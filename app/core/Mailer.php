<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {

    private PHPMailer $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $this->mail->isSMTP();
            $this->mail->Host       = 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'natalie.hany14@gmail.com';
            $this->mail->Password   = 'mtma elgc ujzq ikfr'; // Gmail App Password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = 587;

            // Sender info
            $this->mail->setFrom('your_email@gmail.com', 'PadelUp');

        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
    }

    public function send(string $to, string $subject, string $body): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            return false;
        }
    }
}
