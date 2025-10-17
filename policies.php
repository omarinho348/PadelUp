<?php include 'Includes/navbar.php'; ?>

<title>Our Policies - PadelUp</title>
<link rel="stylesheet" href="styling/policies.css">

<header class="policy-hero">
    <div class="container">
        <h1>Our Policies</h1>
        <p class="subtitle">Your guide to our terms, privacy, and community guidelines.</p>
    </div>
</header>

<main class="policy-content-wrapper container">
    <aside class="policy-nav">
        <nav>
            <ul>
                <li><a href="#terms" class="active">Terms of Service</a></li>
                <li><a href="#privacy">Privacy Policy</a></li>
                <li><a href="#cookies">Cookie Policy</a></li>
                <li><a href="#community">Community Guidelines</a></li>
            </ul>
        </nav>
    </aside>

    <div class="policy-text">
        <section id="terms">
            <h2>Terms of Service</h2>
            <p class="last-updated">Last updated: October 17, 2025</p>
            
            <h3>1. Introduction</h3>
            <p>Welcome to PadelUp! These Terms of Service ("Terms") govern your use of our website, mobile applications, and services.
                 By accessing or using our Services, you agree to be bound by these Terms.</p>

            <h3>2. User Accounts</h3>
            <p>To access certain features, you must create an account. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. 
                You agree to notify us immediately of any unauthorized use of your account.</p>

            <h3>3. User Conduct</h3>
            <p>You agree not to use the Services to post or transmit any material that is defamatory, obscene, fraudulent, or that violates the rights of others.
                 We reserve the right to terminate accounts that violate these rules.</p>
        </section>

        <section id="privacy">
            <h2>Privacy Policy</h2>
            <p class="last-updated">Last updated: October 17, 2025</p>

            <h3>1. Information We Collect</h3>
            <p>We collect information you provide directly to us, such as when you create an account (name, email, skill level). We also collect information automatically as you use our Services, such as your IP address, device type, and booking history.</p>

            <h3>2. How We Use Your Information</h3>
            <p>We use your information to provide and improve our Services, facilitate matchmaking, process bookings, communicate with you, and ensure the security of our platform. We do not sell your personal data to third parties.</p>
        </section>

        <section id="cookies">
            <h2>Cookie Policy</h2>
            <p class="last-updated">Last updated: October 17, 2025</p>

            <h3>1. What Are Cookies?</h3>
            <p>Cookies are small text files stored on your device that help us operate and customize our Services. We use both session cookies (which expire when you close your browser) and persistent cookies (which stay on your device for a set period).</p>

            <h3>2. Your Choices</h3>
            <p>Most web browsers are set to accept cookies by default. If you prefer, you can usually choose to set your browser to remove or reject browser cookies. Please note that if you choose to remove or reject cookies, this could affect the availability and functionality of our Services.</p>
        </section>

        <section id="community">
            <h2>Community Guidelines</h2>
            <p class="last-updated">Last updated: October 17, 2025</p>

            <h3>1. Be Respectful</h3>
            <p>PadelUp is a community for everyone. Treat fellow players, coaches, and venue staff with respect. Harassment, hate speech, and bullying will not be tolerated.</p>

            <h3>2. Play Fair</h3>
            <p>Honor your bookings and commitments. Communicate clearly with your match partners and report scores accurately. Sportsmanship is at the core of our community.</p>

            <h3>3. Keep it Safe</h3>
            <p>Do not share sensitive personal information with other users. Report any suspicious or inappropriate behavior to our support team immediately.</p>
        </section>
    </div>
</main>

<script>
    // Simple scroll-spy for active nav link
    const sections = document.querySelectorAll('.policy-text section');
    const navLinks = document.querySelectorAll('.policy-nav a');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 150) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
</script>

<?php include 'Includes/footer.php'; ?>