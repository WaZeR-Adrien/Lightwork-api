<?php
namespace Kernel\Tools\File;

use Kernel\Config;
use Kernel\Tools\Collection\MailException;
use Kernel\Tools\Utils;

class Mail
{
    /**
     * @var array
     */
    private $from;

    /**
     * @var array
     */
    private $to;

    /**
     * @var array
     */
    private $reply;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $altbody;

    /**
     * Mail constructor.
     * @param array $from
     * @param array $to
     * @param array $reply
     * @param string $subject
     * @param string $body
     * @param string $altbody
     */
    public function __construct($from = [], $to = [], $reply = [], $subject, $body, $altbody = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->reply = $reply;
        $this->subject = $subject;
        $this->body = $body;
        $this->altbody = $altbody;
    }

    /**
     * Send the mail
     * @throws MailException
     */
    public function send()
    {
        $mail = new PHPMailer(true);
        try {
            // Server settings for PHPMailer
            $mail->isSMTP();
            $mail->Host = Utils::getConfigElement('mail')['host'];
            $mail->SMTPAuth = true;
            $mail->Username = Utils::getConfigElement('mail')['username']; // SMTP username
            $mail->Password = Utils::getConfigElement('mail')['pw']; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;

            // Recipients
            $mail->setFrom($this->from['email'], $this->from['name']);

            if (count($this->to) !== count($this->to, COUNT_RECURSIVE)) {
                foreach ($this->to as $item) {
                    if (!empty($item['name'])) {
                        $mail->addAddress($item['email'], $item['name']);
                    } else {
                        $mail->addAddress($item['email']);
                    }
                }
            } else {
                if (!empty($this->to['name'])) {
                    $mail->addAddress($this->to['email'], $this->to['name']);
                } else {
                    $mail->addAddress($this->to['email']);
                }
            }


            $mail->addReplyTo($this->reply['email'], $this->reply['name']);

            // Content
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body    = $this->body;
            if (null != $this->altbody) {
                $mail->AltBody = $this->altbody;
            }

            $mail->send();
        } catch (Exception $e) {
            throw new MailException($e->getMessage());
        }
    }
}
