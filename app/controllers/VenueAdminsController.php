<?php
require_once __DIR__ . '/../controllers/UserController.php';

// Gate access
UserController::requireSuperAdmin();

$creationMessage = '';
$deleteMessage = '';

// Handle POST actions using PRG pattern for success cases
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_admin_id'])) {
        $deleteMessage = UserController::deleteVenueAdmin();
        if ($deleteMessage === 'VENUE_ADMIN_DELETED') {
            header('Location: VenueAdminsController.php?deleted=1');
            exit();
        }
    } elseif (isset($_POST['name'])) {
        $creationMessage = UserController::createVenueAdmin();
        if ($creationMessage === 'VENUE_ADMIN_CREATED') {
            header('Location: VenueAdminsController.php?created=1');
            exit();
        }
    }
}

if (isset($_GET['deleted'])) {
    $deleteMessage = 'VENUE_ADMIN_DELETED';
}
if (isset($_GET['created'])) {
    $creationMessage = 'VENUE_ADMIN_CREATED';
}

$venueAdmins = UserController::getVenueAdmins();

// Render view
include __DIR__ . '/../views/VenueAdmins.php';
