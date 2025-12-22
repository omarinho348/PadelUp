<?php

require_once __DIR__ . '/../models/Mail.php';

class MailController
{
    public static function sendMail($to, $subject, $body)
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return Mail::send($to, $subject, $body);
    }
}
