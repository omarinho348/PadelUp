<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Coach - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/coach-finder.css">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="coach-finder-header">
        <div class="container">
            <div class="main-heading">
                <h1>Find Your PadelUp Coach</h1>
                <p>Get personalized training from our range of certified coaches.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="coaches-grid">
            <!-- Coach Card 1 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/Coach1.jpg');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Ahmed Mohamed</h3>
                    <p class="coach-location">Cairo, Egypt</p>
                    <span class="coach-specialty">Attacking Play</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
            <!-- Coach Card 2 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/coach2.webp');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Belal Attia</h3>
                    <p class="coach-location">Cairo, Egypt</p>
                    <span class="coach-specialty">Defensive Strategy</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
            <!-- Coach Card 3 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/coach3.jpg');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Mo Ali</h3>
                    <p class="coach-location">Giza, Egypt</p>
                    <span class="coach-specialty">Technique & Form</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
            <!-- Coach Card 4 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/coach4.webp');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Abdullah Saleh</h3>
                    <p class="coach-location">New Cairo, Egypt</p>
                    <span class="coach-specialty">Junior Coaching</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
            <!-- Coach Card 5 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/coach5.jpg');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Karima Elmarghany</h3>
                    <p class="coach-location">Cairo, Egypt</p>
                    <span class="coach-specialty">Advanced Tactics</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
            <!-- Coach Card 6 -->
            <div class="coach-card">
                <div class="coach-image" style="background-image: url('../../public/Photos/coach6.webp');"></div>
                <div class="coach-info">
                    <h3 class="coach-name">Adel Sameh</h3>
                    <p class="coach-location">Sheikh Zayed, Egypt</p>
                    <span class="coach-specialty">Fitness & Conditioning</span>
                    <button class="btn btn-primary">View Profile</button>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>