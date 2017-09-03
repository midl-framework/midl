<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midlWebsite\app;

use midl\security\Security;

class User
{

    /**
     * User id, guest id is -1
     *
     * @var int
     */
    public $id = -1;

    /**
     *
     * @var string
     */
    public $username;

    /**
     *
     * @var string
     */
    public $password;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var bool
     */
    public $isSignedIn = false;

    /**
     *
     * @var App
     */
    protected $app;

    /**
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @return bool
     */
    public function signin($user, $password, $remember = false)
    {
        $password = Security::passwordHash($password);
        
        $result = $this->app->db->select("SELECT * FROM user WHERE email=? AND password=?", 
            [$user, $password]);
        
        if (count($result) === 1) {}
        
        return false;
    }

    /**
     *
     * @return bool
     */
    public function signout()
    {}

    /**
     * Gets json encoded user data, this data can be used by JavaScript
     *
     * @return string
     */
    public function toJson()
    {
        $data = [
            "id" => $this->id, 
            "username" => $this->username, 
            "name" => $this->name, 
            "isSignedIn" => $this->isSignedIn];
        
        return json_encode($data);
    }

    /**
     *
     * @return bool
     */
    protected function setData(array $data)
    {
        if (empty($data["id"]) || empty($data["username"]))
            return;
        
        $this->id = $data["id"];
        $this->username = $data["username"];
        $this->name = @$data["name"];
        $this->email = @$data["email"];
        $this->isSignedIn = true;
    }

    /**
     *
     * @return void
     */
    protected function loadSession()
    {
        if ($this->app->session->has("user") && is_array($this->app->session["user"])) {
            $this->setData($this->app->session["user"]);
        } else {
            $cookie = $this->app->request->getCookies()->get("user");
            
            if ($cookie) {
                list ($id, $token, $mac) = explode(':', $cookie);
                
                if (!hash_equals(hash_hmac('sha256', "$id:$token", APP_KEY), $mac))
                    return false;
                
                $cond = "id=? AND added > NOW() - INTERVAL 2 WEEK";
                $result = $this->app->db->selectRow("SELECT * FROM user_session WHERE $cond", [$id]);
                
                if (isset($result["id"]) && hash_equals($result["token"], $token)) {
                    $result = $this->app->db->select("SELECT * FROM user WHERE id=?", [$result["userid"]]);
                    
                    if (count($result) === 1)
                        $this->setData($result);
                }
            }
        }
    }

    /**
     *
     * @return void
     */
    protected function rememberMe()
    {
        $token = Security::generateRandomKey(128);
        
        $ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
        $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
        
        $stmnt = $this->app->db->prepare(
            "INSERT INTO user_session (userid, token, ip, user_agent) VALUES (?, ?, ?, ?)");
        
        $stmnt->execute([$this->id, $token, $ip, $userAgent]);
        $id = $this->app->db->lastInsertId();
        
        $cookie = "$id:$token";
        $mac = hash_hmac('sha256', $cookie, APP_KEY);
        $cookie .= ":$mac";
        
        $stmnt = $this->app->db->prepare("UPDATE user_session SET mac=? WHERE id=?");
        $stmnt->execute([$mac, $id]);
        
        $this->app->request->getCookies()->set("user", $cookie, strtotime("+2 weeks"));
    }
}
