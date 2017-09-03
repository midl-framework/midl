<?php
namespace midl\core\Form;

use midl\core\Net\Network;

class Form
{
    // Constants
    /**
     * Captcha verify url
     *
     * @var string
     */
    const CAPTCHA_URL = "https://www.google.com/recaptcha/api/siteverify";
    
    // Public variables
    
    // Private variables
    
    /**
     * Validate username
     *
     * @param string $username Username
     * @return bool|string True if username is valid, error string if it is invalid
     */
    public static function validateUsername($username)
    {
        if (!$username)
            return t__("Please enter a username");
        
        if (($len = strlen($username)) < 3)
            return t__("Username must be at least 3 characters");
        
        if ($len > 50)
            return t__("Username must be at most 50 characters");
        
        if (preg_match("/[^a-zA-Z0-9\-\.]+/", $username))
            return t__("Usernames can only contain letters, numbers, dashes and dots.");
        
        if (!preg_match("/[a-zA-Z]+/", $username))
            return t__("Sorry, usernames must have letters too!");
        
        $reservedUsernames = include (APP_DIR . "Model/data/reserved-usernames.php");
        
        if (in_array(strtolower($username), $reservedUsernames, true))
            return t_spf(
                '<strong>%1$s</strong> is a reserved name, if you are the owner of this name please contact us via support page.', 
                $username);
        
        return true;
    }

    /**
     * Validate email address.
     *
     * @param string $email Email to validate
     * @return bool True if email address is valid, false if it is invalid
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password.
     *
     * @param string $password Password to validate
     * @param string $confirmPassword [optional] Confirmation password
     * @return bool|string True if password is valid, error string if it is invalid
     */
    public static function validatePassword($password, $confirmPassword = null)
    {
        if (strlen($password) < 6)
            return t__("Your password must be at least 6 characters");
        
        elseif (!preg_match("/^([a-zA-Z0-9_@%&}{,;:<>\/\+\*\.\$\[\]\(\)\?\|\-])+$/", $password))
            return t__("Your password contains invalid characters.");
        
        elseif ($confirmPassword !== null && $confirmPassword != $password)
            return t__("Passwords doesn't match.");
        
        return true;
    }

    /**
     * Check captcha.
     *
     * @param string $response Recaptcha response
     * @return bool True if typed capcha is correct, otherwise false
     */
    public static function checkCaptcha($response)
    {
        if (!$response)
            return false;
        
        $result = Network::makeRequest(self::CAPTCHA_URL, 
            array('secret' => CAPTCHA_KEY, 'response' => $response));
        $result = @json_decode($result, true);
        
        return $result["success"] === true;
    }

    /**
     * Generate a random password
     * Min 6 characters long, contains at least one digit and one letter.
     *
     * @return string A random password
     */
    public static function generateRandomPassword()
    {
        $digits = str_shuffle("0123456789");
        $letters = str_shuffle("abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWYZ");
        $count = mt_rand(1, 5);
        
        return str_shuffle(substr($digits, 0, $count) . substr($letters, 0, mt_rand(6 - $count, 10 - $count)));
    }
}