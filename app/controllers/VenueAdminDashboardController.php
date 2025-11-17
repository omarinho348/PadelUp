<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Court.php';
require_once __DIR__ . '/../models/Booking.php';

UserController::requireVenueAdmin();

$adminId = (int)$_SESSION['user_id'];
$venues = Venue::listByAdmin($GLOBALS['conn'], $adminId);
if (isset($_GET['venue_id'])) {
    $selectedVenueId = (int)$_GET['venue_id'];
    $selectedVenue = Venue::findById($GLOBALS['conn'], $selectedVenueId);
} else {
    $selectedVenue = Venue::findFirstByAdmin($GLOBALS['conn'], $adminId);
    $selectedVenueId = $selectedVenue['venue_id'] ?? null;
}

if ($selectedVenue && (int)$selectedVenue['venue_admin_id'] !== $adminId) {
    // prevent access to others' venues
    header('Location: VenueAdminDashboardController.php');
    exit();
}

$message = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedVenue) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_venue') {
        $hourly_rate = $_POST['hourly_rate'] ?? '';
        $opening_time = $_POST['opening_time'] ?? '';
        $closing_time = $_POST['closing_time'] ?? '';
        $logo_path = $selectedVenue['logo_path'] ?? null;
        // Handle logo upload
        if (isset($_FILES['venue_logo']) && $_FILES['venue_logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $type = mime_content_type($_FILES['venue_logo']['tmp_name']);
            if (!isset($allowed[$type])) {
                $message = 'Logo must be JPG or PNG.';
            } else {
                $ext = $allowed[$type];
                $unique = uniqid('venue_', true);
                $filename = $unique . '.' . $ext;
                $target = __DIR__ . '/../../public/Photos/VenueLogos/' . $filename;
                if (move_uploaded_file($_FILES['venue_logo']['tmp_name'], $target)) {
                    $logo_path = 'public/Photos/VenueLogos/' . $filename;
                } else {
                    $message = 'Failed to upload logo.';
                }
            }
        }
        if ($hourly_rate === '' || !ctype_digit($hourly_rate)) {
            $message = 'Hourly rate must be a whole number.';
        } else if (empty($message)) {
            $fields = [
                'hourly_rate' => (int)$hourly_rate,
                'opening_time' => $opening_time,
                'closing_time' => $closing_time,
            ];
            if ($logo_path) { $fields['logo_path'] = $logo_path; }
            $res = Venue::update($GLOBALS['conn'], $selectedVenue['venue_id'], $fields);
            if ($res === true) { header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&saved=1'); exit(); }
            else { $message = is_string($res) ? $res : 'Failed to update venue.'; }
        }
    }
    elseif ($action === 'create_court') {
        $court_name = trim($_POST['court_name'] ?? '');
        $court_type = $_POST['court_type'] ?? 'outdoor';
        if ($court_name === '') { $message = 'Court name is required.'; }
        else {
            $res = Court::create($GLOBALS['conn'], [
                'venue_id' => $selectedVenue['venue_id'],
                'court_name' => htmlspecialchars($court_name),
                'court_type' => htmlspecialchars($court_type),
                'is_active' => 1,
            ]);
            if ($res === true) { header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&court_created=1'); exit(); }
            else { $message = is_string($res) ? $res : 'Failed to create court.'; }
        }
    }
    elseif ($action === 'update_court') {
        $court_id = (int)($_POST['court_id'] ?? 0);
        $court_name = trim($_POST['court_name'] ?? '');
        $court_type = $_POST['court_type'] ?? 'outdoor';
        if ($court_id <= 0 || $court_name === '') { $message = 'Invalid court update data.'; }
        else {
            $res = Court::update($GLOBALS['conn'], $court_id, [
                'court_name' => htmlspecialchars($court_name),
                'court_type' => htmlspecialchars($court_type)
            ]);
            if ($res === true) { header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&court_updated=1'); exit(); }
            else { $message = is_string($res) ? $res : 'Failed to update court.'; }
        }
    }
    elseif ($action === 'toggle_court') {
        $court_id = (int)($_POST['court_id'] ?? 0);
        $desired = $_POST['desired'] ?? 'close';
        if ($court_id > 0) {
            $res = Court::update($GLOBALS['conn'], $court_id, [ 'is_active' => ($desired === 'open' ? 1 : 0) ]);
            if ($res === true) { header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&court_toggled=1'); exit(); }
            else { $message = is_string($res) ? $res : 'Failed to update court status.'; }
        }
    }
    elseif ($action === 'delete_court') {
        $court_id = (int)($_POST['court_id'] ?? 0);
        if ($court_id > 0) {
            $res = Court::delete($GLOBALS['conn'], $court_id);
            if ($res === true) { header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&court_deleted=1'); exit(); }
            else { $message = is_string($res) ? $res : 'Failed to delete court.'; }
        }
    }
}

if (isset($_GET['saved'])) { $success = 'Venue settings saved.'; }
if (isset($_GET['court_created'])) { $success = 'Court created.'; }
if (isset($_GET['court_updated'])) { $success = 'Court updated.'; }
if (isset($_GET['court_toggled'])) { $success = 'Court status updated.'; }
if (isset($_GET['court_deleted'])) { $success = 'Court deleted.'; }

$courts = $selectedVenue ? Court::listByVenue($GLOBALS['conn'], $selectedVenue['venue_id']) : [];
$bookings = $selectedVenue ? Booking::listByVenue($GLOBALS['conn'], $selectedVenue['venue_id']) : [];

include __DIR__ . '/../views/venue_admin.php';
