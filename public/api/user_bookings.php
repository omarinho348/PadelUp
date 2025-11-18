<?php
// GET: /public/api/user_bookings.php  (lists current user's bookings)
// POST: /public/api/user_bookings.php  { booking_id } (cancels booking)
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/core/dbh.inc.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$userId = $_SESSION['user_id'] ?? null;
if(!$userId){ http_response_code(401); echo json_encode(['error'=>'Not authenticated']); exit; }
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if($method === 'GET'){
    $stmt = $conn->prepare('SELECT b.booking_id, b.booking_date, b.start_time, b.end_time, b.status, b.total_price, c.court_name, v.name AS venue_name FROM bookings b JOIN courts c ON b.court_id = c.court_id JOIN venues v ON c.venue_id = v.venue_id WHERE b.user_id = ? ORDER BY b.booking_date DESC, b.start_time DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    echo json_encode($rows);
    exit;
}
if($method === 'POST'){
    $input = json_decode(file_get_contents('php://input'), true);
    $bookingId = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
    if(!$bookingId){ http_response_code(400); echo json_encode(['error'=>'Missing booking_id']); exit; }
    // Only allow cancelling own booking and only if not already cancelled or paid
    $stmt = $conn->prepare('UPDATE bookings SET status = "cancelled" WHERE booking_id = ? AND user_id = ? AND status NOT IN ("cancelled","paid")');
    $stmt->bind_param('ii', $bookingId, $userId);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    if($ok){ echo json_encode(['success'=>true]); } else { http_response_code(400); echo json_encode(['error'=>'Could not cancel booking']); }
    exit;
}
http_response_code(405); echo json_encode(['error'=>'Method not allowed']);
