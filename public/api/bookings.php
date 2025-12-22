<?php
// public/api/bookings.php
// GET:  /public/api/bookings.php?venue_id=ID&date=YYYY-MM-DD  -> returns { courts: [{court_id,court_name},...], bookings: {"<court_id>":["HH:MM",...] } }
// POST: JSON { venue_id, court_id, date: 'YYYY-MM-DD', start_time: 'HH:MM', end_time: 'HH:MM' } -> creates booking
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/controllers/EmailController.php';
require_once __DIR__ . '/../../app/core/dbh.inc.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function respond($code, $data){
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Helpers
function getCourtsForVenue(mysqli $conn, int $venueId): array {
    $stmt = $conn->prepare('SELECT court_id, court_name FROM courts WHERE venue_id = ? AND is_active = 1 ORDER BY court_name ASC');
    if(!$stmt){ return []; }
    $stmt->bind_param('i', $venueId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows ?: [];
}


function timeRangeToSlots(string $start, string $end): array {
    // Both in HH:MM:SS or HH:MM, returns array of HH:MM at 30-min intervals [start, end)
    $slots = [];
    [$sh, $sm] = array_map('intval', explode(':', $start));
    [$eh, $em] = array_map('intval', explode(':', $end));
    $startMin = $sh*60 + $sm;
    $endMin = $eh*60 + $em;
    for($t = $startMin; $t < $endMin; $t += 30){
        $h = floor($t/60); $m = $t%60;
        $slots[] = sprintf('%02d:%02d', $h, $m);
    }
    return $slots;
}

if ($method === 'GET') {
    $venueId = isset($_GET['venue_id']) ? (int)$_GET['venue_id'] : 0;
    $date = $_GET['date'] ?? '';
    if(!$venueId || !$date){ respond(400, ['error' => 'venue_id and date are required']); }

    $courts = getCourtsForVenue($conn, $venueId);
    // Fetch venue hours to ensure client renders correct slot range
    $hours = ['opening_time' => null, 'closing_time' => null];
    $vh = $conn->prepare('SELECT opening_time, closing_time FROM venues WHERE venue_id = ? LIMIT 1');
    if($vh){
        $vh->bind_param('i', $venueId);
        $vh->execute();
        $vh->bind_result($o, $c);
        if($vh->fetch()){
            $hours['opening_time'] = $o;
            $hours['closing_time'] = $c;
        }
        $vh->close();
    }
    $bookings = [];
    foreach($courts as $c){ $bookings[(string)$c['court_id']] = []; }
    foreach($courts as $c){
        $cid = (int)$c['court_id'];
        $stmt = $conn->prepare("SELECT start_time, end_time FROM bookings WHERE court_id = ? AND booking_date = ? AND status <> 'cancelled'");
        if(!$stmt){ continue; }
        $stmt->bind_param('is', $cid, $date);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res){
            while($row = $res->fetch_assoc()){
                $slots = timeRangeToSlots($row['start_time'], $row['end_time']);
                $key = (string)$cid;
                $bookings[$key] = array_values(array_unique(array_merge($bookings[$key], $slots)));
            }
        }
        $stmt->close();
    }

    respond(200, [ 'courts' => $courts, 'bookings' => $bookings, 'hours' => $hours ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input){ respond(400, ['error' => 'Invalid JSON']); }
    $venueId = (int)($input['venue_id'] ?? 0);
    $courtId = isset($input['court_id']) ? (int)$input['court_id'] : 0;
    $date = $input['date'] ?? '';
    $start = $input['start_time'] ?? '';
    $end = $input['end_time'] ?? '';

    if(!$venueId || !$courtId || !$date || !$start || !$end){
        respond(400, ['error' => 'Missing fields']);
    }

    // Require logged-in user
    $userId = $_SESSION['user_id'] ?? null;
    if(!$userId){ respond(401, ['error' => 'Not authenticated']); }

    // Validate court belongs to venue
    $stmt = $conn->prepare('SELECT 1 FROM courts WHERE court_id = ? AND venue_id = ? AND is_active = 1');
    if(!$stmt){ respond(500, ['error' => 'Prepare failed']); }
    $stmt->bind_param('ii', $courtId, $venueId);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows === 0){ $stmt->close(); respond(400, ['error' => 'Invalid court']); }
    $stmt->close();

    // Validate overlap
    $stmt = $conn->prepare("SELECT 1 FROM bookings WHERE court_id = ? AND booking_date = ? AND status <> 'cancelled' AND NOT (end_time <= ? OR start_time >= ?) LIMIT 1");
    if(!$stmt){ respond(500, ['error' => 'Prepare failed']); }
    $stmt->bind_param('isss', $courtId, $date, $start, $end);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $stmt->close();
        respond(409, ['error' => 'Time slot already booked']);
    }
    $stmt->close();

    // Compute price from venue hourly_rate
    $stmt = $conn->prepare('SELECT hourly_rate FROM venues WHERE venue_id = ?');
    if(!$stmt){ respond(500, ['error' => 'Prepare failed']); }
    $stmt->bind_param('i', $venueId);
    $stmt->execute();
    $stmt->bind_result($hourlyRate);
    $stmt->fetch();
    $stmt->close();
    if($hourlyRate === null){ $hourlyRate = 0; }

    [$sh,$sm] = array_map('intval', explode(':', $start));
    [$eh,$em] = array_map('intval', explode(':', $end));
    $minutes = ($eh*60+$em) - ($sh*60+$sm);
    if($minutes <= 0) { respond(400, ['error' => 'Invalid time range']); }
    $hours = $minutes / 60.0;
    $totalPrice = round($hours * (float)$hourlyRate, 2);

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (court_id, user_id, booking_date, start_time, end_time, total_price, status) VALUES (?,?,?,?,?,?, 'confirmed')");
    if(!$stmt){ respond(500, ['error' => 'Prepare failed']); }
    $stmt->bind_param('iisssd', $courtId, $userId, $date, $start, $end, $totalPrice);
    $ok = $stmt->execute();
    if(!$ok){
        $err = $stmt->error;
        $stmt->close();
        respond(500, ['error' => $err ?: 'Insert failed']);
    }
    $bookingId = $stmt->insert_id;
    $stmt->close();
    
    // ================= EMAIL CONFIRMATION =================

// get user email and court name
$stmt = $conn->prepare("
    SELECT u.email, c.court_name
    FROM users u
    JOIN courts c ON c.court_id = ?
    WHERE u.user_id = ?
    LIMIT 1
");
$stmt->bind_param('ii', $courtId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// send email (do not block booking if email fails)
if ($userData && isset($userData['email'])) {
    $emailController = new EmailController();

    $emailController->sendBookingConfirmation(
        $userData['email'],
        [
            'date'  => $date,
            'time'  => $start . ' - ' . $end,
            'court' => $userData['court_name']
        ]
    );
}

// ======================================================


    respond(201, ['success' => true, 'booking_id' => $bookingId, 'total_price' => $totalPrice]);
}

respond(405, ['error' => 'Method not allowed']);
