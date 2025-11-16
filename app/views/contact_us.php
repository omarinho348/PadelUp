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
        <form class="contact-form" action="#" method="POST">
            <div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" required placeholder="Your Name"></div>
            <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" required placeholder="you@example.com"></div>
            <div class="form-group"><label for="subject">Subject</label><input type="text" id="subject" name="subject" required placeholder="How can we help?"></div>
            <div class="form-group"><label for="message">Message</label><textarea id="message" name="message" rows="6" required placeholder="Your message..."></textarea></div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>