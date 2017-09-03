<?php
namespace midl\app\Model;

use midl\app\App;
use midl\core\Exception\Exception;
use midl\core\UserRoles;

/**
 * UserModel manages user related operations between
 * controller and database.
 *
 * @author Abdulhalim Kara
 */
class UserModel
{
    // Constants
    
    // Public variables
    /**
     * User id
     *
     * @var int
     */
    public $id;

    /**
     * Username
     *
     * @var string
     */
    public $username;

    /**
     * User type
     *
     * @var int
     */
    public $type;

    /**
     * User email
     *
     * @var string
     */
    public $email;

    /**
     * User password
     *
     * @var string
     */
    public $password;

    /**
     * Associative array of user data (account, profile etc...)
     *
     * @var array
     */
    public $profileData;

    /**
     * User roles
     *
     * @var bool
     */
    public $isAdmin = false;
    
    // Private variables
    
    /**
     * Constructor
     *
     * @param string $username [optional] Displayed username, defaults to logged in user's username
     * @throws Exception If no user found for given username
     */
    function __construct($username = null)
    {
        if ($username === null) {
            $user = App::$user;
            
            if (!$user->isSignedIn)
                throw new Exception("There is no logged in user.");
            
            $this->username = $user->username;
            $this->id = $user->id;
            $this->type = $user->type;
            $this->email = $user->email;
            $this->password = $user->password;
            
            UserRoles::setRoles($this);
        } elseif (!(($this->username = App::$db->escapeString($username)) && $this->checkUsername()))
            throw new Exception("Could not find user: '$username'");
    }

    /**
     * Check displayed user name
     *
     * @return bool True if a user exists with this id, false on failure
     * @throws \Exception When internal error occurred
     */
    private function checkUsername()
    {
        $result = App::$db->select(DB_T_USER, "userid, type, email, password", "username='{$this->username}'", 
            "", 0, true);
        
        if (count($result) === 1) {
            $this->email = $result[0]["email"];
            $this->password = $result[0]["password"];
            $this->id = (int)$result[0]["userid"];
            $this->type = (int)$result[0]["type"];
            
            UserRoles::setRoles($this);
            
            return true;
        }
        
        return false;
    }

    /**
     * Get profile data
     *
     * @return array Associative array of user profile data
     * @throws \Exception If internal error occurs
     */
    public function getProfile()
    {
        if ($this->profileData)
            return $this->profileData;
        
        $table = DB_T_USER_PROFILE . " AS p JOIN " . DB_T_USER . " AS u ON u.userid=$this->id";
        $cols = "p.*,u.firstname,u.lastname,u.avatar,CONCAT_WS(' ',u.firstname,u.lastname) AS name";
        
        $profile = App::$db->select($table, $cols, "p.userid=$this->id", "", null, true);
        
        if (count($profile) !== 1)
            throw new Exception("Could not retrieve profile data for userid: '$this->id'");
        
        $profile = $profile[0];
        $profile["userid"] = $this->id;
        $profile["username"] = $this->username;
        $profile["type"] = $this->type;
        $profile["email"] = $this->email;
        $profile["password"] = $this->password;
        
        return $this->profileData = $profile;
    }

    /**
     * Toggle block user
     *
     * @param int $id [optional] User id, defaults to $this->id
     * @param bool $block [optional] Block status, defaults to false
     * @return bool True on success, false on failure
     */
    public function toggleBlock($id = null, $block = false)
    {
        $id = (int)($id ?: $this->id);
        
        if ($id > 0) {
            $block = $block ? "Y" : "N";
            
            return false !== App::$db->update(DB_T_USER, "blocked='$block'", "userid=$id", "LIMIT 1");
        }
        
        return false;
    }

    /**
     * Search users
     *
     * @param string $query search query
     * @param string $type [optional] User type, defaults to ""
     * @param int $limit [optional] Defaults to 20
     * @return array List of users
     */
    public function search($query, $type = "", $limit = 20)
    {
        $query = App::$db->escapeSearchString($query);
        
        if (!$query)
            return false;
        
        $cond = App::$user->isAdmin ? "" : "blocked='N' AND ";
        $cond .= "(type=" .
             (UserRoles::$roles[$type] > 0 ? UserRoles::$roles[$type] : UserRoles::$roles["member"]) . ")";
        $cond .= " AND (firstname LIKE \"%$query%\" OR lastname LIKE \"%$query%\")";
        
        $limit = (int)$limit;
        $ext = "LIMIT " . ($limit > 0 && $limit < 50 ? $limit : 20);
        
        return App::$db->select(DB_T_USER, "userid AS id, username, firstname, lastname, avatar", $cond, $ext);
    }

    /**
     * Get followers for this user or users list followed by this user
     *
     * @param string $type [optional] Valid types are ["follower", "followed"], defaults to "follower"
     * @param int $limit [optional] Defaults to 24, if null, limit will not be applied
     * @return array List of followers
     */
    public function getFollows($type = "follower", $limit = 20)
    {
        $limit = max(1, min((int)$limit, 100));
        $offset = (max(1, (int)$_REQUEST["page"]) - 1) * $limit;
        
        if (App::$user->self && $type != "follower") {
            $table = DB_T_USER_FOLLOW . " AS f JOIN " . DB_T_USER . " AS u ON f.$type=u.userid JOIN " .
                 DB_T_USER_PROFILE . " AS p ON p.userid=u.userid";
            $cols = "u.username, CONCAT_WS(' ', u.firstname, u.lastname) as name, u.avatar, p.title, 1 AS isFollowing";
            $cond = "f.follower=$this->id";
        } else {
            $table = DB_T_USER_FOLLOW . " AS f JOIN " . DB_T_USER . " AS u ON f.$type=u.userid JOIN " .
                 DB_T_USER_PROFILE . " AS p ON p.userid=u.userid";
            $cols = "u.username, CONCAT_WS(' ', u.firstname, u.lastname) as name, u.avatar, p.title";
            
            if (App::$user->isSignedIn) {
                $table .= " LEFT JOIN " . DB_T_USER_FOLLOW .
                     " AS flw ON flw.followed=u.userid AND flw.follower=" . App::$user->id;
                $cols .= ", IF(flw.follower IS NOT NULL, 1, 0) AS isFollowing";
            } else
                $cols .= ", 0 AS isFollowing";
            
            if ($type == "follower")
                $cond = "f.followed=$this->id";
            
            elseif ($type == "followed")
                $cond = "f.follower=$this->id";
            
            else
                return [];
        }
        
        return App::$db->select($table, $cols, $cond, "ORDER BY f.when DESC LIMIT $offset, $limit") ?: [];
    }

    /**
     * Follow user with given id
     *
     * @param string $uName Username
     * @return bool True on success, false on failure
     */
    public function follow($uName)
    {
        if ($username = App::$db->escapeString($uName)) {
            if ($username !== $this->username) {
                $id = App::$db->getVal(DB_T_USER, "userid", "username='$username'");
                
                if ($id > 0) {
                    Notifications::add($id, $id, $this->id, "new_follower");
                    
                    return App::$db->insert("IGNORE " . DB_T_USER_FOLLOW, "follower=$this->id, followed=$id", 
                        null, "", true);
                }
            } else
                Logger::log("User ($uName) is attempting to follow himself.", Logger::MT_USER);
        }
        
        return false;
    }

    /**
     * Unfollow user with given id
     *
     * @param string $username Username
     * @return bool True on success, false on failure
     */
    public function unfollow($username)
    {
        if ($username = App::$db->escapeString($username)) {
            $cond = "follower=$this->id AND followed=(SELECT userid FROM " . DB_T_USER .
                 " WHERE username='$username')";
            App::$db->delete(DB_T_USER_FOLLOW, $cond, "LIMIT 1", true);
            
            return true;
        }
        
        return false;
    }

    /**
     * Check whether user is following given user or not
     * To use currently logged in user's id set related parameter to null.
     *
     * @param int $follower Follower id
     * @param int $followed Followed id
     * @return bool True if follower follows followed, otherwise false
     */
    public static function isFollowing($follower = null, $followed = null)
    {
        if (!$follower)
            $follower = App::$user->id;
        elseif (!$followed)
            $followed = App::$user->id;
        
        if (!$follower || !$followed)
            return false;
        
        $result = App::$db->select(DB_T_USER_FOLLOW, "follower", "follower=$follower AND followed=$followed");
        
        return count($result) === 1;
    }
}