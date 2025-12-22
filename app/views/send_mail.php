<form method="post">
    <input type="email" name="to" placeholder="Recipient Email" required>
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="body" placeholder="Message" required></textarea>
    <button type="submit" name="send">Send Email</button>
</form>
<?php
if (isset($_POST['send'])) {
    require_once __DIR__ . '/../controllers/MailController.php';
    $sent = MailController::sendMail($_POST['to'], $_POST['subject'], $_POST['body']);
    echo $sent ? 'Email sent!' : 'Failed to send email.';
}
?>
