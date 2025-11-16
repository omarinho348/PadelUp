<?php 
require_once __DIR__ . '/../controllers/UserController.php';
UserController::requireSuperAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Manage users, Venue Admins and coaches.</p>
        </div>

        <div class="admin-grid">
            <!-- Placeholder Card 1: User Management -->
            <a href="admin_users.php" class="admin-card">
                <div class="card-icon">👥</div>
                <h3>User Management</h3>
                <p>View, edit, and manage all player accounts.</p>
            </a>

            <!-- Placeholder Card 2: Venue & Court Management -->
            <a href="VenueAdmins.php" class="admin-card">
                <div class="card-icon">🏟️</div>
                <h3>Venue Admins</h3>
                <p>Manage venue admins.</p>
            </a>

            <!-- Placeholder Card 3: Bookings Overview -->
            <a href="PadelCoaches.php" class="admin-card">
                <div class="card-icon">🎾</div>
                <h3>PadelUp coaches</h3>
                <p>Manage all PadelUp coaches.</p>
            </a>
        </div>
    </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>