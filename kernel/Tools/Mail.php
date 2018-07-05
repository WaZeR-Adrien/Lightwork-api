<?php
namespace Kernel\Tools;

use Kernel\Config;

class Mail
{
    public static function send($from = [], $to = [], $reply = [], $subject, $body, $altbody = null)
    {
        $mail = new PHPMailer(true);
        try {
            // Server settings for PHPMailer
            $mail->isSMTP();
            $mail->Host = Config::getMail()['host'];
            $mail->SMTPAuth = true;
            $mail->Username = Config::getMail()['username']; // SMTP username
            $mail->Password = Config::getMail()['pw']; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;

            // Recipients
            $mail->setFrom($from['email'], $from['name']);
            foreach ($to as $item) {
                if (!empty($item['name'])) {
                    $mail->addAddress($item['email'], $item['name']);
                } else {
                    $mail->addAddress($item['email']);
                }
            }
            $mail->addReplyTo($reply['email'], $reply['name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if (null != $altbody) {
                $mail->AltBody = $altbody;
            }

            $mail->send();
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}