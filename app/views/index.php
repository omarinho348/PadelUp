<?php /* navbar moved inside body */ ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PadelConnect — Find players · Book courts · Buy gear</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container">      

        <!-- Top hero banner using the provided image -->
        <section class="top-hero home-hero" aria-hidden="false">
            <div class="inner">
                <h1>PadelUp</h1>
                    <p>Play • Connect • Level Up</p>
                <div class="cta">
                    <a href="venues.php"><button class="btn">Start Playing</button></a>
                    <a href="marketplace.php"><button class="btn">Shop Now</button></a>
                </div>
            </div>
            <div class="feature-pills">
                <div class="feature-row">
                    <div class="feature-pill">🎯 Smart Matching</div>
                    <div class="feature-pill">🏟️ Court Bookings</div>
                    <div class="feature-pill">🛍️ Padel Marketplace</div>
                </div>
                <div class="feature-row">
                    <div class="feature-pill">👥 Secure Community</div>
                    <div class="feature-pill">📊 AI Skill Ratings and classification</div>
                </div>
            </div>
        </section>

        <!-- Main heading -->
        <div class="main-heading">
            <h1>Everything you need to <span class="accent">LEVEL UP</span></h1>
            <p>Connect with players, book your next match, buy Padel Gear <br> <span class="accent">ALL IN ONE PLACE.</span></p>
        </div>

        <section class="content-section">
            <!-- First Row: Matchmaking and Court Booking -->
            <a href="matchmaking.php" class="feature-card-link">
                <div class="feature-card matchmaking-card">
                    <div class="card-content">
                        <p class="feature-description">Find the perfect match based on your skill level, playing style, and schedule. Our AI-powered system ensures balanced and enjoyable games.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">🎯</div>
                            <h3 class="feature-title">Smart Matchmaking</h3>
                        </div>
                    </div>
                </div>
            </a>

  <a href="tournaments.php" class="feature-card-link">
                <div class="feature-card tournaments-card">
                    <div class="card-content">
                        <p class="feature-description">Join exciting weekly tournaments, compete with players at your level, and climb the rankings. From beginners to pros, there's a tournament for everyone.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">🏆</div>
                            <h3 class="feature-title">Weekly Tournaments</h3>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Second Row: Four cards -->
            <a href="padeliq.php" class="feature-card-link">
                <div class="feature-card padeliq-card">
                    <div class="card-content">
                        <p class="feature-description">Advanced AI-powered skill rating system that analyzes your gameplay, tracks progress, and provides personalized improvement insights.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">🧠</div>
                            <h3 class="feature-title">PadelIQ</h3>
                        </div>
                    </div>
                </div>
            </a>

            <a href="venues.php" class="feature-card-link">
                <div class="feature-card court-booking-card">
                    <div class="card-content">
                        <p class="feature-description">Browse and book courts instantly. Real-time availability, weather updates, and integrated payment system for seamless booking.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">🏟️</div>
                            <h3 class="feature-title">Court Booking</h3>
                        </div>
                    </div>
                </div>
            </a>

            <a href="coach-finder.php" class="feature-card-link">
                <div class="feature-card coaches-card">
                    <div class="card-content">
                        <p class="feature-description">Find certified padel coaches near you. Book private or group lessons, improve your technique, and take your game to the next level.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">👨‍🏫</div>
                            <h3 class="feature-title">Coach Finder</h3>
                        </div>
                    </div>
                </div>
            </a>

            <a href="marketplace.php" class="feature-card-link">
                <div class="feature-card marketplace-card">
                    <div class="card-content">
                        <p class="feature-description">Buy and sell premium padel gear, equipment, and accessories. Connect with trusted sellers and find the best deals in our dedicated marketplace.</p>
                        <div class="card-title-row">
                            <div class="feature-icon">🛍️</div>
                            <h3 class="feature-title">Marketplace</h3>
                        </div>
                    </div>
                </div>
            </a>

        </section>

        <!-- Video section -->
        <section class="video-strip">
            <video autoplay muted loop playsinline>
                <source src="../../public/Videos/video.mp4" type="video/mp4">
            </video>
            <div class="video-overlay">
                <h2>JOIN US NOW</h2>
                <?php
                $redirect_link = (isset($_SESSION['ID']) && isset($_SESSION['FullName'])) ? 'profile.php' : 'signup.php';
                ?>
                <a href="<?php echo $redirect_link; ?>" class="btn btn-primary" style="border-radius: 999px; padding: 16px 48px; font-size: 1.1rem;">
                    Get Started
                </a>
            </div>
        </section>

    </div>
<?php include __DIR__ . '/partials/footer.php'; ?>