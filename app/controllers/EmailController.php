<?php
require_once __DIR__ . '/../core/Mailer.php';

class EmailController
{



    public static function sendBookingConfirmation(
        $email,
        $name,
        $bookingId,
        $date,
        $start,
        $end,
        $price
     ) {
        $subject = "Booking Confirmation - PadelUp";

        $body = "
            <h2>Booking Confirmed</h2>
            <p>Hello <strong>$name</strong>,</p>
            <p>Your booking has been confirmed.</p>
            <ul>
                <li>Booking ID: $bookingId</li>
                <li>Date: $date</li>
                <li>Time: $start - $end</li>
                <li>Total Price: $price EGP</li>
            </ul>
            <p>Thank you for using PadelUp.</p>
        ";

        return Mailer::send($email, $subject, $body);
    }

   //Welcome Email (User Registration)
public static function sendWelcomeEmail($email, $name)
{
    $subject = "Welcome to PadelUp 🎾";

    $body = "
        <h2>Welcome to PadelUp!</h2>
        <p>Hello <strong>$name</strong>,</p>
        <p>We are excited to have you join the PadelUp community.</p>
        <p>You can now:</p>
        <ul>
            <li>Book padel courts</li>
            <li>Join tournaments</li>
            <li>Find coaches</li>
            <li>Buy equipment from the marketplace</li>
        </ul>
        <p>See you on the court! 🎾</p>
        <p><strong>PadelUp Team</strong></p>
    ";

    return Mailer::send($email, $subject, $body);
}

    //Coach Session Accepted Email
    public static function sendCoachSessionAccepted(
    $email,
    $playerName,
    $coachName,
    $date,
    $time
) {
    $subject = "Your Coaching Session is Confirmed";

    $body = "
        <h2>Session Accepted</h2>
        <p>Hello <strong>$playerName</strong>,</p>
        <p>Your coaching session has been accepted.</p>
        <ul>
            <li>Coach: $coachName</li>
            <li>Date: $date</li>
            <li>Time: $time</li>
        </ul>
        <p>Good luck with your training 💪</p>
    ";

    return Mailer::send($email, $subject, $body);
}
  //Tournament Confirmation Email
  public static function sendTournamentConfirmation(
    $email,
    $playerName,
    $tournamentName,
    $date
) {
    $subject = "Tournament Registration Confirmed";

    $body = "
        <h2>Tournament Confirmed</h2>
        <p>Hello <strong>$playerName</strong>,</p>
        <p>You are successfully registered in:</p>
        <p><strong>$tournamentName</strong></p>
        <p>Date: $date</p>
        <p>Good luck 🍀</p>
    ";

    return Mailer::send($email, $subject, $body);
}
//Marketplace Purchase Email
public static function sendPurchaseConfirmation(
    $email,
    $name,
    $orderId,
    $total
) {
    $subject = "Purchase Confirmation - PadelUp Shop";

    $body = "
        <h2>Thank You for Your Purchase</h2>
        <p>Hello <strong>$name</strong>,</p>
        <p>Your order has been successfully placed.</p>
        <ul>
            <li>Order ID: $orderId</li>
            <li>Total Paid: $total EGP</li>
        </ul>
        <p>We hope you enjoy your items 🛒</p>
    ";

    return Mailer::send($email, $subject, $body);
}
//Admin → User Message (for UserController)
public static function sendAdminMessage($email, $subject, $message)
{
    $body = "
        <h2>Message from PadelUp Admin</h2>
        <p>$message</p>
    ";

    return Mailer::send($email, $subject, $body);
}

}