<?php
require_once __DIR__ . '/../models/Mail.php';
require_once __DIR__ . '/../models/Observer.php';

// Create observable instance for contact events
class ContactObservable extends Observable {}
$contactObserver = new ContactObservable();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $messageContent = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    if (empty($name) || empty($email) || empty($subject) || empty($messageContent)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'error';
    } else {
        // Send email to oa7784055@gmail.com
        $mailBody = "New Contact Form Submission\n\n";
        $mailBody .= "From: $name\n";
        $mailBody .= "Email: $email\n";
        $mailBody .= "Subject: $subject\n\n";
        $mailBody .= "Message:\n$messageContent";
        
        $result = Mail::send('oa7784055@gmail.com', "Contact Form: $subject", $mailBody);
        
        if ($result) {
            // Send confirmation email to the user
            $userMailBody = "Dear $name,\n\n";
            $userMailBody .= "Thank you for contacting PadelUp! We have received your message and will respond to you as soon as possible.\n\n";
            $userMailBody .= "Your Message Details:\n";
            $userMailBody .= "Subject: $subject\n\n";
            $userMailBody .= "Message:\n$messageContent\n\n";
            $userMailBody .= "If you have any urgent questions, please feel free to contact us at support@padelup.com or call +20 122 315 8001.\n\n";
            $userMailBody .= "Best regards,\nThe PadelUp Team";
            
            Mail::send($email, 'We Received Your Message - PadelUp', $userMailBody);
            
            // Notify observers
            $contactData = [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $messageContent
            ];
            $contactObserver->notify('contact_form_submitted', $contactData);
            
            $message = 'Thank you for contacting us! We\'ll get back to you soon. A confirmation email has been sent to your address.';
            $messageType = 'success';
        } else {
            $message = 'Failed to send message. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/contact_us.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="contact-hero">
    <div class="container">
        <h1>Get In Touch</h1>
        <p class="subtitle">Have a question or feedback? We'd love to hear from you.</p>
    </div>
</header>

<main class="contact-page-content container">
    <div class="contact-details">
        <h2>Contact Information</h2>
        <p>You can reach us through the following channels. We'll do our best to get back to you as soon as possible.</p>
        <ul class="contact-info-list">
            <li class="info-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <div>
                    <strong>Email</strong>
                    <a href="mailto:support@padelup.com">support@padelup.com</a>
                </div>
            </li>
            <li class="info-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <div>
                    <strong>Phone</strong>
                    <span>+20 122 315 8001</span>
                </div>
            </li>
            <li class="info-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <div>
                    <strong>Address</strong>
                    <span>PadelUp HQ 5th settlement, Cairo, Egypt</span>
                </div>
            </li>
        </ul>
    </div>

    <div class="contact-form-container">
        <h2>Send Us a Message</h2>
        <?php if ($message): ?>
            <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>" style="padding: 12px; margin-bottom: 16px; border-radius: 6px; background-color: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form class="contact-form" action="contact_us.php" method="POST">
            <div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" required placeholder="Your Name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"></div>
            <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" required placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>
            <div class="form-group"><label for="subject">Subject</label><input type="text" id="subject" name="subject" required placeholder="How can we help?" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"></div>
            <div class="form-group"><label for="message">Message</label><textarea id="message" name="message" rows="6" required placeholder="Your message..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea></div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>