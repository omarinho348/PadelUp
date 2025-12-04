<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../app/core/dbh.inc.php';
require_once __DIR__ . '/../../app/models/Tournament.php';
require_once __DIR__ . '/../../app/controllers/SkillLevelController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$tournamentId = (int)($input['tournament_id'] ?? 0);
$roundNumber = (int)($input['round_number'] ?? 0);
$matchNumber = (int)($input['match_number'] ?? 0);
$team1Seed = (int)($input['team1_seed'] ?? 0);
$team2Seed = (int)($input['team2_seed'] ?? 0);
$winnerSeed = (int)($input['winner_seed'] ?? 0);

// Validate input
if (!$tournamentId || !$roundNumber || !$matchNumber || !$team1Seed || !$team2Seed || !$winnerSeed) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// Verify winner is one of the two teams
if ($winnerSeed != $team1Seed && $winnerSeed != $team2Seed) {
    echo json_encode(['success' => false, 'error' => 'Winner must be one of the match participants']);
    exit();
}

// Check if user is venue admin for this tournament
$sql = "SELECT created_by FROM tournaments WHERE tournament_id = ?";
$stmt = $GLOBALS['conn']->prepare($sql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result || $result['created_by'] != $userId) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

// Insert or update match result
$sql = "INSERT INTO tournament_match_results 
        (tournament_id, round_number, match_number, team1_seed, team2_seed, winner_seed, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        winner_seed = VALUES(winner_seed),
        recorded_by = VALUES(recorded_by),
        recorded_at = CURRENT_TIMESTAMP";

$stmt = $GLOBALS['conn']->prepare($sql);
$stmt->bind_param("iiiiiii", $tournamentId, $roundNumber, $matchNumber, $team1Seed, $team2Seed, $winnerSeed, $userId);

if ($stmt->execute()) {
    $stmt->close();
    
    // Get tournament max_size to calculate max rounds
    $tournamentSql = "SELECT max_size FROM tournaments WHERE tournament_id = ?";
    $stmt = $GLOBALS['conn']->prepare($tournamentSql);
    $stmt->bind_param("i", $tournamentId);
    $stmt->execute();
    $tournamentData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($tournamentData) {
        $maxRounds = (int)log($tournamentData['max_size'], 2);
        
        // Update player skill levels based on match result
        $loserSeed = ($winnerSeed === $team1Seed) ? $team2Seed : $team1Seed;
        SkillLevelController::updateFromTournamentMatch(
            $GLOBALS['conn'],
            $tournamentId,
            $winnerSeed,
            $loserSeed,
            $roundNumber,
            $maxRounds
        );
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    $stmt->close();
}

