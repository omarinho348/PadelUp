<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Court.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tournament.php';
require_once __DIR__ . '/SkillLevelController.php';

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
    elseif ($action === 'create_tournament') {
        // Required fields: tournament_name, tournament_date, start_time, max_level, total_prize_money
        $t_name = trim($_POST['tournament_name'] ?? '');
        $t_date = trim($_POST['tournament_date'] ?? '');
        $t_start = trim($_POST['start_time'] ?? '');
        $t_max_level = trim($_POST['max_level'] ?? '');
        $t_prize = trim($_POST['total_prize_money'] ?? '0');
        $t_entrance_fee = trim($_POST['entrance_fee'] ?? '0');
        $t_max_size = (int)($_POST['max_size'] ?? 4);

        // validate allowed sizes
        $allowedSizes = [4,8,16];
        if (!in_array($t_max_size, $allowedSizes, true)) {
            $message = 'Invalid tournament size selected.';
        }

        // validate max_level (skill level range 1-7)
        $maxLevelInt = (int)$t_max_level;
        if ($maxLevelInt < 1 || $maxLevelInt > 7) {
            $message = 'Max level must be between 1 and 7.';
        }

        if ($t_name === '' || $t_date === '' || $t_start === '' || $t_max_level === '') {
            $message = 'Name, date, start time and max level are required.';
        }

        if (empty($message)) {
            // sanitize and prepare data (no court_id stored)
            $data = [
                'venue_id' => (int)$selectedVenue['venue_id'],
                'tournament_name' => htmlspecialchars($t_name),
                'created_by' => $adminId,
                'tournament_date' => $t_date,
                'start_time' => $t_start,
                'max_level' => (int)$t_max_level,
                'entrance_fee' => (float)$t_entrance_fee,
                'total_prize_money' => (float)$t_prize,
                'max_size' => (int)$t_max_size,
            ];

            $res = Tournament::create($GLOBALS['conn'], $data);
            if (is_int($res)) {
                header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&tournament_created=1');
                exit();
            } else {
                $message = is_string($res) ? $res : 'Failed to create tournament.';
            }
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
    elseif ($action === 'update_booking_status') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';
        if ($booking_id <= 0) {
            $message = 'Invalid booking selected.';
        } else {
            $res = Booking::updateStatusByAdmin($GLOBALS['conn'], $booking_id, $adminId, $new_status);
            if ($res === true) {
                header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&booking_updated=1');
                exit();
            } else {
                $message = is_string($res) ? $res : 'Failed to update booking.';
            }
        }
    }
    elseif ($action === 'update_tournament_status') {
        $tournamentId = (int)($_POST['tournament_id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? '');
        
        if ($tournamentId <= 0 || !in_array($newStatus, ['completed', 'cancelled'])) {
            $message = 'Invalid tournament or status.';
        } else {
            $sql = "UPDATE tournaments SET status = ? WHERE tournament_id = ? AND venue_id = ?";
            $stmt = $GLOBALS['conn']->prepare($sql);
            $stmt->bind_param('sii', $newStatus, $tournamentId, $selectedVenue['venue_id']);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                header('Location: VenueAdminDashboardController.php?venue_id='.$selectedVenue['venue_id'].'&tournament_updated=1');
                exit();
            } else {
                $message = 'Failed to update tournament status.';
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['saved'])) { $success = 'Venue settings saved.'; }
if (isset($_GET['court_created'])) { $success = 'Court created.'; }
if (isset($_GET['court_updated'])) { $success = 'Court updated.'; }
if (isset($_GET['court_toggled'])) { $success = 'Court status updated.'; }
if (isset($_GET['court_deleted'])) { $success = 'Court deleted.'; }
if (isset($_GET['booking_updated'])) { $success = 'Booking updated.'; }
if (isset($_GET['tournament_created'])) { $success = 'Tournament created.'; }
if (isset($_GET['tournament_updated'])) { $success = 'Tournament status updated.'; }

$courts = $selectedVenue ? Court::listByVenue($GLOBALS['conn'], $selectedVenue['venue_id']) : [];
$bookings = $selectedVenue ? Booking::listByVenue($GLOBALS['conn'], $selectedVenue['venue_id']) : [];
$tournaments = $selectedVenue ? Tournament::listByVenue($GLOBALS['conn'], $selectedVenue['venue_id']) : [];

include __DIR__ . '/../views/venue_admin.php';
