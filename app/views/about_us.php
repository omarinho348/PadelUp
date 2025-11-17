<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/about_us.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="about-hero">
    <div class="container">
        <h1>We're PadelUp.</h1>
        <p class="subtitle">A team of passionate players dedicated to growing the sport we love.</p>
    </div>
</header>

<main class="about-content container">
    <section class="about-section">
        <h2>Our Mission</h2>
        <p>Our journey started on the court. We saw a need for a single, unified platform where players could connect, compete, and improve. We got tired of juggling different apps for finding matches, booking courts, and tracking our progress. So, we decided to build it ourselves.</p>
        <p>PadelUp is our answer. It’s more than just an app; it's a community hub designed by players, for players. Our mission is to make padel more accessible, competitive, and fun for everyone, from the first-time player to the seasoned pro.</p>
    </section>

    <section class="about-section values-section">
        <h2>What We Believe In</h2>
        <ul class="values-list">
            <li>
                <strong>Community First:</strong> We believe the heart of padel is its community. Our goal is to build a platform that fosters connections, sportsmanship, and friendship.
            </li>
            <li>
                <strong>Seamless Technology:</strong> Your time should be spent on the court, not on your phone. We're obsessed with creating a smooth, intuitive experience that gets you playing faster.
            </li>
            <li>
                <strong>Constant Improvement:</strong> Just like in padel, we're always looking to improve. We are committed to listening to our community and continuously evolving the PadelUp platform.
            </li>
        </ul>
    </section>

    <section class="about-section cta-section">
        <h2>Ready to Join Us?</h2>
        <p>Whether you're looking for your next match, a new racket, or a court to play on, we've got you covered. Become a part of the PadelUp community today.</p>
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $redirect_link = (isset($_SESSION['user_id']) && isset($_SESSION['name'])) ? 'profile.php' : 'signup.php';
        ?>
        <a href="<?php echo $redirect_link; ?>" class="btn btn-primary">Get Started</a>
    </section>

</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>