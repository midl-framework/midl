<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\security;

class Security
{

    /**
     */
    public static function passwordHash($password)
    {
        return password_hash(base64_encode(hash("sha256", $password, true)), PASSWORD_DEFAULT);
    }
    
    /**
     */
    public static function passwordVerify($password, $passwordHash)
    {
        return password_verify(base64_encode(hash("sha256", $password, true)), $passwordHash);
    }

    /**
     */
    public static function encrypt($data, $key = null)
    {
        if (!is_scalar($data))
            return false;
        
        $key = $key === null && defined("APP_KEY") ? APP_KEY : $key;
    }

    /**
     */
    public static function decrypt($data, $key = null)
    {
        if (!is_string($data))
            return $data;
        
        $key = $key === null && defined("APP_KEY") ? APP_KEY : $key;
    }

    /**
     * Generates random key
     *
     * @param int $length [optional] Defaults to 128
     * @return string
     */
    public static function generateRandomKey($length = 128)
    {
        if (function_exists("mcrypt_create_iv"))
            $rand = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        
        elseif (function_exists("openssl_random_pseudo_bytes"))
            $rand = openssl_random_pseudo_bytes($length);
        
        elseif (is_readable('/dev/urandom'))
            $rand = file_get_contents('/dev/urandom', false, null, 0, $length);
        
        else {
            $i = 0;
            $rand = "";
            while ($i++ < $length)
                $rand .= chr(mt_rand(0, 255));
        }
        
        return substr(bin2hex($rand), 0, $length);
    }
}