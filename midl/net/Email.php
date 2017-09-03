<?php
namespace midl\core;

use midl\app\App;

/**
 *
 * @author Abdulhalim Kara
 */
class Email
{
    // Constants
    
    // Public variables
    
    // Private variables
    
    /**
     * Send email
     *
     * @param string $email Email address to send message
     * @param string $subject Email subject
     * @param string $message Email content
     * @param string $sender [optional] Sender's name
     * @param string $senderEmail [optional] Sender's email address
     * @return bool True on success, false on failure
     */
    public static function send($email, $subject, $message, $sender = APP_NAME, $senderEmail = SUPPORT_EMAIL)
    {
        $subject = $sender === APP_NAME ? $subject : "[" . APP_NAME . "] $subject";
        $sender = '=?UTF-8?B?' . base64_encode($sender) . '?=';
        
        $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
             '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>' .
             "<body>$message</body></html>";
        
        $headers = "From: $sender <$senderEmail>\r\nReply-To: $sender <$senderEmail>\r\nX-Mailer: PHP/" .
             phpversion() . "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        
        return mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    }

    /**
     * Send confirmation email to user
     *
     * @param string $email Email address
     * @param string $username Username
     * @param string $userFullName [Optional] User full name, defaults to username
     * @param string $code [Optional] Confirmation code
     * @return bool True on success, false on failure
     */
    public static function sendConfirmationEmail($email, $username, $userFullName = null, $code = null)
    {
        $userFullName = $userFullName ? $userFullName : $username;
        
        if (!$code) {
            $code = substr(md5($username . mt_rand(1, 99999999)), 16, 16);
            App::$db->update(DB_T_USER, "confirmation_code='$code'", "username='$username'", "LIMIT 1");
        }
        
        $message = t_spf(
            'Dear %1$s,<br /><br />Welcome to %2$s!<br /><br />Click the following link to confirm your account:<br /><br /><a href="%3$s">%3$s</a><br /><br />Alternatively, you can confirm your account on %2$s by entering the confirmation code: %4$s<br />Go to %2$s -> Sign In -> Confirm Your Account.<br /><br />Thank you,<br />%2$s Team', 
            $userFullName, APP_NAME, SITE_URL . "signin/confirm/$code/", $code);
        
        return self::send($email, t__("Confirm Your Account"), $message);
    }
}