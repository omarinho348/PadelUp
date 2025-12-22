<?php



require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/Observer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail extends Observable
{
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function send($to, $subject, $body)
    {
        $instance = self::getInstance();
        $config = require __DIR__ . '/../config/mail.php';
        $mail = new PHPMailer(true);
        $result = false;
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port = $config['port'];

            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $result = $mail->send();
            $instance->notify('mail_sent', ['to'=>$to, 'subject'=>$subject, 'body'=>$body, 'success'=>true]);
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $instance->notify('mail_failed', ['to'=>$to, 'subject'=>$subject, 'body'=>$body, 'success'=>false, 'error'=>$e->getMessage()]);
            return false;
        }
    }
}
