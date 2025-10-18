<?php 
include 'Includes/navbar.php'; 
include_once "includes/dbh.inc.php";

// --- SECURITY CHECK ---
// If the user is not logged in or is not an admin, redirect to the homepage.
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Manage users, venues, bookings, and more.</p>
        </div>

        <div class="admin-grid">
            <!-- Placeholder Card 1: User Management -->
            <a href="admin_users.php" class="admin-card">
                <div class="card-icon">👥</div>
                <h3>User Management</h3>
                <p>View, edit, and manage all user accounts.</p>
            </a>

            <!-- Placeholder Card 2: Venue & Court Management -->
            <a href="admin_venues.php" class="admin-card">
                <div class="card-icon">🏟️</div>
                <h3>Venue Management</h3>
                <p>Add new venues and manage court availability.</p>
            </a>

            <!-- Placeholder Card 3: Bookings Overview -->
            <a href="admin_bookings.php" class="admin-card">
                <div class="card-icon">📅</div>
                <h3>Bookings Overview</h3>
                <p>Monitor all court bookings and reservations.</p>
            </a>
        </div>
    </div>

<?php include 'Includes/footer.php'; ?>
</body>
</html>