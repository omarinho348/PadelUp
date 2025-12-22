<?php
require_once __DIR__ . '/../../app/core/dbh.inc.php';
require_once __DIR__ . '/../../app/models/Tournament.php';
require_once __DIR__ . '/../../app/models/PlayerProfile.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Mail.php';
require_once __DIR__ . '/../../app/models/Observer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = Database::getInstance()->getConnection();

// Create observable instance for tournament events
class TournamentObservable extends Observable {}
$tournamentObserver = new TournamentObservable();

// Set JSON response header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please sign in to register']);
    exit();
}

$tournamentId = (int)($_POST['tournament_id'] ?? 0);
$partnerEmail = trim($_POST['partner_email'] ?? '');
if ($tournamentId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid tournament']);
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Get tournament details to check max_level and capacity
$sql = "SELECT max_level, max_size FROM tournaments WHERE tournament_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$result = $stmt->get_result();
$tournament = $result->fetch_assoc();
$stmt->close();

if (!$tournament) {
    echo json_encode(['success' => false, 'error' => 'Tournament not found']);
    exit();
}

// Validate partner email
if ($partnerEmail === '') {
    echo json_encode(['success' => false, 'error' => 'Please enter your partner\'s email']);
    exit();
}

$partner = User::findByEmail($conn, $partnerEmail);
if (!$partner) {
    echo json_encode(['success' => false, 'error' => 'Partner not found. Please ask your partner to sign up first.']);
    exit();
}

// Fetch player profiles for both users
$profile1 = PlayerProfile::findByUserId($conn, $userId);
$profile2 = PlayerProfile::findByUserId($conn, (int)$partner['user_id']);
if (!$profile1 || !$profile2) {
    echo json_encode(['success' => false, 'error' => 'Both players must have a completed profile with a skill level.']);
    exit();
}

$maxLevel = (int)$tournament['max_level'];
$level1 = (float)$profile1['skill_level'];
$level2 = (float)$profile2['skill_level'];
if ($level1 > $maxLevel || $level2 > $maxLevel) {
    echo json_encode([
        'success' => false,
        'error' => 'One or both players exceed the maximum tournament level (' . $maxLevel . ')'
    ]);
    exit();
}

// Proceed with team registration
$res = Tournament::registerTeam($conn, $tournamentId, $userId, (int)$partner['user_id']);
if ($res === true) {
    // Get tournament name
    $getTournament = $conn->prepare('SELECT name FROM tournaments WHERE tournament_id = ?');
    $getTournament->bind_param('i', $tournamentId);
    $getTournament->execute();
    $getTournament->bind_result($tournamentName);
    $getTournament->fetch();
    $getTournament->close();
    
    // Get current user email
    $getUser = $conn->prepare('SELECT email, name FROM users WHERE user_id = ?');
    $getUser->bind_param('i', $userId);
    $getUser->execute();
    $getUser->bind_result($userEmail, $userName);
    $getUser->fetch();
    $getUser->close();
    
    // Get partner name
    $partnerName = $partner['name'];
    $partnerEmailResult = $partner['email'];
    
    // Send confirmation email to the current user
    if($userEmail){
        $mailBody = "Your tournament registration is confirmed!\n\nTournament Details:\nTournament: $tournamentName\nTeam Member: $partnerName\n\nTournament draws will be available 12 hours before the start time.\n\nThank you for registering with PadelUp!";
        Mail::send($userEmail, 'Tournament Registration Confirmation', $mailBody);
    }
    
    // Send confirmation email to the teammate
    if($partnerEmailResult){
        $mailBody = "Your teammate $userName has registered you both in a tournament!\n\nTournament Details:\nTournament: $tournamentName\nTeam Member: $userName\n\nTournament draws will be available 12 hours before the start time.\n\nThank you for registering with PadelUp!";
        Mail::send($partnerEmailResult, 'Tournament Registration Confirmation', $mailBody);
    }
    
    // Notify observers of tournament registration
    $registrationData = [
        'tournament_id' => $tournamentId,
        'tournament_name' => $tournamentName,
        'user_id' => $userId,
        'user_name' => $userName,
        'user_email' => $userEmail,
        'partner_id' => (int)$partner['user_id'],
        'partner_name' => $partnerName,
        'partner_email' => $partnerEmailResult
    ];
    $tournamentObserver->notify('tournament_registered', $registrationData);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! Tournament draws will be available 12 hours before the start time.'
    ]);
} else {
    $err = is_string($res) ? $res : 'Registration failed';
    echo json_encode(['success' => false, 'error' => $err]);
}
