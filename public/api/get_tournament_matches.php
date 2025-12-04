<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../app/core/dbh.inc.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$tournamentId = (int)($_GET['tournament_id'] ?? 0);

if (!$tournamentId) {
    echo json_encode(['success' => false, 'error' => 'Invalid tournament ID']);
    exit();
}

// Verify user is the tournament creator (venue admin)
$sql = "SELECT created_by, max_size FROM tournaments WHERE tournament_id = ?";
$stmt = $GLOBALS['conn']->prepare($sql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament || $tournament['created_by'] != $userId) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

// Get the draw
$drawSql = "SELECT d.seed_position, d.team_id, d.is_bye, 
            tt.player1_user_id, tt.player2_user_id,
            u1.name AS p1_name, u2.name AS p2_name
            FROM tournament_draw d
            LEFT JOIN tournament_teams tt ON d.team_id = tt.id
            LEFT JOIN users u1 ON tt.player1_user_id = u1.user_id
            LEFT JOIN users u2 ON tt.player2_user_id = u2.user_id
            WHERE d.tournament_id = ?
            ORDER BY d.seed_position";
$stmt = $GLOBALS['conn']->prepare($drawSql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$drawData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($drawData)) {
    echo json_encode(['success' => false, 'error' => 'Tournament draw not generated yet']);
    exit();
}

// Build draw array indexed by seed
$draw = [];
foreach ($drawData as $entry) {
    $seed = (int)$entry['seed_position'];
    $draw[$seed] = [
        'seed' => $seed,
        'is_bye' => (bool)$entry['is_bye'],
        'player1' => $entry['p1_name'] ?? null,
        'player2' => $entry['p2_name'] ?? null
    ];
}

// Load all match results
$resultsSql = "SELECT round_number, match_number, team1_seed, team2_seed, winner_seed 
               FROM tournament_match_results 
               WHERE tournament_id = ?";
$stmt = $GLOBALS['conn']->prepare($resultsSql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$resultsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Index results by round and match
$matchResults = [];
foreach ($resultsData as $result) {
    $key = $result['round_number'] . '_' . $result['match_number'];
    $matchResults[$key] = $result;
}

// Generate all matches across all rounds
$matches = [];
$currentRoundSeeds = array_keys($draw);
$roundNumber = 1;
$maxRounds = (int)log($tournament['max_size'], 2);

while (count($currentRoundSeeds) > 1) {
    $roundName = '';
    if ($roundNumber == $maxRounds) $roundName = 'Final';
    elseif ($roundNumber == $maxRounds - 1) $roundName = 'Semi-Final';
    elseif ($roundNumber == $maxRounds - 2) $roundName = 'Quarter-Final';
    else $roundName = 'Round ' . $roundNumber;
    
    $matchNumber = 1;
    $nextRoundSeeds = [];
    
    for ($i = 0; $i < count($currentRoundSeeds); $i += 2) {
        $seed1 = $currentRoundSeeds[$i];
        $seed2 = isset($currentRoundSeeds[$i + 1]) ? $currentRoundSeeds[$i + 1] : null;
        
        $team1 = $draw[$seed1];
        $team2 = $seed2 ? $draw[$seed2] : null;
        
        $matchKey = $roundNumber . '_' . $matchNumber;
        $result = $matchResults[$matchKey] ?? null;
        
        // Determine winner for this match (from results or auto-advance BYE)
        $winnerSeed = null;
        if ($result) {
            $winnerSeed = $result['winner_seed'];
        } elseif (!$team2 || $team2['is_bye']) {
            $winnerSeed = $seed1;
        } elseif ($team1['is_bye']) {
            $winnerSeed = $seed2;
        }
        
        // Only include matches that have both teams determined (not TBD)
        $team2IsTBD = !$team2 || (!$team2['is_bye'] && !$team2['player1']);
        
        $matches[] = [
            'round_number' => $roundNumber,
            'round_name' => $roundName,
            'match_number' => $matchNumber,
            'team1_seed' => $seed1,
            'team1_player1' => $team1['player1'],
            'team1_player2' => $team1['player2'],
            'team1_is_bye' => $team1['is_bye'],
            'team2_seed' => $seed2,
            'team2_player1' => $team2 ? $team2['player1'] : null,
            'team2_player2' => $team2 ? $team2['player2'] : null,
            'team2_is_bye' => $team2 ? $team2['is_bye'] : false,
            'team2_is_tbd' => $team2IsTBD,
            'winner_seed' => $winnerSeed
        ];
        
        // Advance winner to next round
        if ($winnerSeed) {
            $nextRoundSeeds[] = $winnerSeed;
        } else {
            // Match not yet decided - TBD for next round
            $nextRoundSeeds[] = null;
        }
        
        $matchNumber++;
    }
    
    $currentRoundSeeds = array_filter($nextRoundSeeds); // Remove nulls
    $roundNumber++;
}

echo json_encode([
    'success' => true,
    'matches' => $matches
]);
