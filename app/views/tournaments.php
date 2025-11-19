<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Tournaments - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/tournaments.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="tournaments-header">
        <div class="container">
            <div class="main-heading">
                <h1>Weekly Tournaments</h1>
                <p>Find and join padel tournaments happening near you.</p>
            </div>

            <div class="filters-container">
                <input type="text" placeholder="Search by name or location...">
                <select><option value="">All Categories</option><option value="mens">Men's</option><option value="womens">Women's</option><option value="mixed">Mixed</option></select>
                <input type="date">
                <button class="btn btn-primary">Find Tournaments</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="tournaments-grid">
            <!-- Tournament Card 1 -->
            <div class="tournament-card">
                <div class="card-header">
                    <span class="tournament-level">Intermediate</span>
                    <span class="tournament-date">October 20, 2025</span>
                </div>
                <div class="card-body">
                    <h3 class="tournament-title">Nile Championship</h3>
                    <p class="tournament-location">New Cairo, Egypt</p>
                    <div class="tournament-details">
                        <div class="detail-item">
                            <span class="detail-label">Prize</span>
                            <span class="detail-value">EGP 15000</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Entry Fee</span>
                            <span class="detail-value">EGP 600</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">Register Now</button>
                </div>
            </div>

            <!-- Tournament Card 2 -->
            <div class="tournament-card">
                <div class="card-header">
                    <span class="tournament-level">Advanced</span>
                    <span class="tournament-date">October 22, 2025</span>
                </div>
                <div class="card-body">
                    <h3 class="tournament-title">MIU University Cup</h3>
                    <p class="tournament-location">Zamalek, Egypt</p>
                    <div class="tournament-details">
                        <div class="detail-item">
                            <span class="detail-label">Prize</span>
                            <span class="detail-value">EGP 30000</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Entry Fee</span>
                            <span class="detail-value">EGP 1200</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">Register Now</button>
                </div>
            </div>

            <!-- Tournament Card 3 -->
            <div class="tournament-card">
                <div class="card-header">
                    <span class="tournament-level">Beginner</span>
                    <span class="tournament-date">October 25, 2025</span>
                </div>
                <div class="card-body">
                    <h3 class="tournament-title">Pyramids Cup</h3>
                    <p class="tournament-location">Sheikh Zayed, Egypt</p>
                    <div class="tournament-details">
                        <div class="detail-item">
                            <span class="detail-label">Prize</span>
                            <span class="detail-value">Gear</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Entry Fee</span>
                            <span class="detail-value">EGP 300</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">Register Now</button>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>