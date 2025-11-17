<?php 
require_once __DIR__ . '/../controllers/UserController.php';
UserController::requireSuperAdmin();

// Handle form submissions for the VenueAdmins page from this central point
$creationMessage = UserController::createVenueAdmin();
$coachCreationMessage = UserController::createCoach(); // For PadelCoaches page
$contactMessage = UserController::contactPlayer(); // For PadelCoaches page
$contactVenueAdminMessage = UserController::contactVenueAdmin(); // For VenueAdmins page
$deleteMessage = UserController::deleteVenueAdmin();
$coachDeleteMessage = UserController::deleteCoach(); // For PadelCoaches page
$venueAdmins = UserController::getVenueAdmins();
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
    <?php include __DIR__ . '/partials/admin_navbar.php'; ?>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Manage users, Venue Admins and coaches.</p>
        </div>

        <div class="admin-grid">
            <!-- Placeholder Card 1: User Management -->
            <a href="admin_users.php" class="admin-card">
                <div class="card-icon">👥</div>
                <h3>Players Management</h3>
                <p>View, edit, and manage all player accounts.</p>
            </a>

            <!-- Placeholder Card 2: Venue & Court Management -->
            <a href="VenueAdmins.php" class="admin-card" 
               onclick="event.preventDefault(); 
                        const form = document.createElement('form'); 
                        form.method = 'POST'; form.action = 'VenueAdmins.php';
                        form.innerHTML = `<input type='hidden' name='creationMessage' value='<?php echo $creationMessage; ?>'><input type='hidden' name='deleteMessage' value='<?php echo $deleteMessage; ?>'><input type='hidden' name='contactMessage' value='<?php echo $contactVenueAdminMessage; ?>'>`;
                        document.body.appendChild(form); form.submit();">
                <div class="card-icon">🏟️</div>
                <h3>Venue Admins</h3>
                <p>Manage venue admins.</p>
            </a>

            <!-- Placeholder Card 3: Bookings Overview -->
            <a href="PadelCoaches.php" class="admin-card"
                onclick="event.preventDefault(); 
                        const form = document.createElement('form'); 
                        form.method = 'POST'; form.action = 'PadelCoaches.php';
                        form.innerHTML = `<input type='hidden' name='creationMessage' value='<?php echo $coachCreationMessage; ?>'><input type='hidden' name='deleteMessage' value='<?php echo $coachDeleteMessage; ?>'><input type='hidden' name='contactMessage' value='<?php echo $contactMessage; ?>'>`;
                        document.body.appendChild(form); form.submit();">
                <div class="card-icon">🎾</div>
                <h3>PadelUp coaches</h3>
                <p>Manage all PadelUp coaches.</p>
            </a>
        </div>
    </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>