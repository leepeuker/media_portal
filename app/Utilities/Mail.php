<?php

namespace Utilities;

use \Core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail
 *
 * PHP version 7.0
 */
class Mail
{

    /**
     * Send a email
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     * @return boolean True if email could be sent, false otherwise
     */
    public static function send($to, $subject, $text, $html)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                   // Enable verbose debug output
            $mail->isSMTP();                                        // Set mailer to use SMTP
            $mail->Host = Config::get('SMTP_HOST');                      // Specify main and backup SMTP servers
            $mail->SMTPAuth = Config::get('SMTP_Auth');                 // Enable SMTP authentication
            $mail->Username = Config::get('SMTP_USER');                  // SMTP username
            $mail->Password = Config::get('SMTP_PASSWORD');              // SMTP password
            $mail->SMTPSecure = Config::get('SMTP_SECURE');              // Enable TLS encryption, `ssl` also accepted
            $mail->Port = Config::get('SMTP_PORT');                      // TCP port to connect to
        
            //Recipients
            $mail->setFrom(Config::get('EMAIL_WEBMASTER'));
            $mail->addAddress($to);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;
        
            $mail->send();

            return true;

        } catch (Exception $e) {
            
            return false;
        }
    }
}
