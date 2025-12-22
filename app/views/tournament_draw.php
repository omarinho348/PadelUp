<?php
session_start();
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/Tournament.php';
require_once __DIR__ . '/../models/User.php';

$conn = Database::getInstance()->getConnection();

$tournamentId = (int)($_GET['id'] ?? 0);
if ($tournamentId <= 0) {
    header('Location: tournaments.php');
    exit();
}

// Get tournament details
$sql = "SELECT t.*, v.name as venue_name, v.city as venue_city, v.logo_path 
        FROM tournaments t 
        JOIN venues v ON t.venue_id = v.venue_id 
        WHERE t.tournament_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament) {
    header('Location: tournaments.php');
    exit();
}

// Process logo path
$logoPath = $tournament['logo_path'] ?? '';
if ($logoPath && !str_starts_with($logoPath, 'http')) {
    // Remove 'public/' prefix if present since web root might be /public or /PadelUp
    $logoPath = str_replace('public/', '', $logoPath);
    $logoPath = '/PadelUp/public/' . ltrim($logoPath, '/');
}

// Get registered teams (doubles)
$sql = "SELECT tt.id, tt.player1_user_id, tt.player2_user_id, u1.name AS p1_name, u2.name AS p2_name
    FROM tournament_teams tt
    JOIN users u1 ON tt.player1_user_id = u1.user_id
    JOIN users u2 ON tt.player2_user_id = u2.user_id
    WHERE tt.tournament_id = ?
    ORDER BY tt.registered_at";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$regCount = count($teams);
$isFull = $regCount >= (int)$tournament['max_size'];

// Check if draw is available (12 hours before start OR tournament is full)
// BYEs fill empty slots, so we don't need a minimum number of teams
$tournamentDateTime = strtotime($tournament['tournament_date'] . ' ' . $tournament['start_time']);
$twelveHoursBefore = $tournamentDateTime - (12 * 60 * 60);
$within12Hours = (time() >= $twelveHoursBefore);

if (!$within12Hours && !$isFull) {
    header('Location: tournaments.php');
    exit();
}

// Check if draw already exists
$drawSql = "SELECT d.seed_position, d.team_id, d.is_bye, 
            tt.player1_user_id, tt.player2_user_id,
            u1.name AS p1_name, u2.name AS p2_name
            FROM tournament_draw d
            LEFT JOIN tournament_teams tt ON d.team_id = tt.id
            LEFT JOIN users u1 ON tt.player1_user_id = u1.user_id
            LEFT JOIN users u2 ON tt.player2_user_id = u2.user_id
            WHERE d.tournament_id = ?
            ORDER BY d.seed_position";
$stmt = $conn->prepare($drawSql);
$stmt->bind_param("i", $tournamentId);
$stmt->execute();
$existingDraw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!empty($existingDraw)) {
    // Use existing draw - build seed-indexed array
    $draw = [];
    foreach ($existingDraw as $entry) {
        $seed = (int)$entry['seed_position'];
        if ($entry['is_bye']) {
            $draw[$seed] = [
                'player1' => 'BYE',
                'player2' => null,
                'is_bye' => true,
                'seed' => $seed
            ];
        } else {
            $draw[$seed] = [
                'player1' => $entry['p1_name'],
                'player2' => $entry['p2_name'],
                'is_bye' => false,
                'seed' => $seed
            ];
        }
    }
    ksort($draw); // Sort by seed position
} else {
    // Generate new draw and save it
    $draw = generateAndSaveTeamDraw($teams, (int)$tournament['max_size'], $tournamentId, $conn);
}

// Load all match results
$resultsSql = "SELECT round_number, match_number, team1_seed, team2_seed, winner_seed 
               FROM tournament_match_results 
               WHERE tournament_id = ?";
$stmt = $conn->prepare($resultsSql);
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

// Check if user is venue admin for this tournament
$isVenueAdmin = false;
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $isVenueAdmin = ($tournament['created_by'] == $userId);
}

// Generate tournament draw and save to database
function generateAndSaveTeamDraw($teams, $maxTeams, $tournamentId, $conn) {
    $totalSlots = $maxTeams;
    $drawData = [];

    // Add real teams
    foreach ($teams as $team) {
        $drawData[] = [
            'team_id' => $team['id'],
            'player1' => $team['p1_name'],
            'player2' => $team['p2_name'],
            'is_bye' => false
        ];
    }

    // Add BYEs
    while (count($drawData) < $totalSlots) {
        $drawData[] = [
            'team_id' => null,
            'player1' => 'BYE',
            'player2' => null,
            'is_bye' => true
        ];
    }

    // Randomize the draw
    shuffle($drawData);

    // Save to database
    $stmt = $conn->prepare("INSERT INTO tournament_draw (tournament_id, seed_position, team_id, is_bye) VALUES (?, ?, ?, ?)");
    foreach ($drawData as $position => $entry) {
        $seedPosition = $position + 1;
        $teamId = $entry['team_id'];
        $isBye = $entry['is_bye'] ? 1 : 0;
        $stmt->bind_param("iiii", $tournamentId, $seedPosition, $teamId, $isBye);
        $stmt->execute();
    }
    $stmt->close();

    // Return the draw for display
    $result = [];
    $position = 1;
    foreach ($drawData as $entry) {
        $result[$position] = [
            'player1' => $entry['player1'],
            'player2' => $entry['player2'],
            'is_bye' => $entry['is_bye'],
            'seed' => $position
        ];
        $position++;
    }
    return $result;
}
$totalRounds = log($tournament['max_size'], 2); // Number of elimination rounds (teams)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Draw - <?php echo htmlspecialchars($tournament['tournament_name']); ?></title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/tournament_draw.css">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    
    <div class="hero-section">
        <div class="hero-content">
            <?php if (!empty($logoPath)): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Venue Logo" class="venue-logo" onerror="this.style.display='none'">
            <?php else: ?>
                <!-- Fallback if no logo -->
                <div class="venue-logo" style="display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary); font-size: 1.5rem;">
                    <?php echo substr($tournament['venue_name'], 0, 1); ?>
                </div>
            <?php endif; ?>
            
            <h1 class="tournament-title"><?php echo htmlspecialchars($tournament['tournament_name']); ?></h1>
            
            <div class="tournament-meta">
                <div class="meta-item">
                    <span class="meta-icon">📅</span>
                    <?php echo date('M j, Y', strtotime($tournament['tournament_date'])); ?>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">⏰</span>
                    <?php echo date('g:i A', strtotime($tournament['start_time'])); ?>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">📍</span>
                    <?php echo htmlspecialchars($tournament['venue_name']); ?>
                </div>
                <div class="meta-item">
                    <span class="meta-icon">👥</span>
                    <?php echo count($teams) . '/' . $tournament['max_size']; ?> Teams
                </div>
            </div>
        </div>
    </div>

    <div class="bracket-wrapper">
        <div class="bracket">
            <?php 
            $currentRoundPlayers = $draw;
            $roundNumber = 1;
            $maxRounds = (int)log($tournament['max_size'], 2);
            
            while (count($currentRoundPlayers) > 1): 
                $roundName = '';
                if ($roundNumber == $maxRounds) $roundName = 'Final';
                elseif ($roundNumber == $maxRounds - 1) $roundName = 'Semi-Final';
                elseif ($roundNumber == $maxRounds - 2) $roundName = 'Quarter-Final';
                else $roundName = 'Round ' . $roundNumber;
            ?>
                <div class="round" data-round="<?php echo $roundNumber; ?>">
                    <div class="round-title"><?php echo $roundName; ?></div>
                    <div class="round-matches">
                    <?php 
                    $matchNumber = 1;
                    $playersArray = array_values($currentRoundPlayers);
                    for ($i = 0; $i < count($playersArray); $i += 2):
                        $player1 = $playersArray[$i];
                        $player2 = isset($playersArray[$i + 1]) ? $playersArray[$i + 1] : null;
                        
                        // Get match result if exists
                        $matchKey = $roundNumber . '_' . $matchNumber;
                        $matchResult = $matchResults[$matchKey] ?? null;
                        $winner = null;
                        if ($matchResult) {
                            $winner = $matchResult['winner_seed'];
                        }
                        
                        $p1Seed = $player1['seed'] ?? null;
                        $p2Seed = $player2['seed'] ?? null;
                    ?>
                        <div class="match" data-round="<?php echo $roundNumber; ?>" data-match="<?php echo $matchNumber; ?>">
                            <div class="player <?php 
                                echo $player1['is_bye'] ? 'bye' : ''; 
                                if ($winner && $p1Seed == $winner) echo ' winner';
                                if ($winner && $p1Seed != $winner && !$player1['is_bye']) echo ' loser';
                            ?>">
                                <div class="player-info">
                                    <span class="team-names">
                                        <?php if ($player1['is_bye']): ?>
                                            BYE
                                        <?php elseif (!empty($player1['player1']) && $player1['player1'] !== 'TBD'): ?>
                                            <span class="team-member"><?php echo htmlspecialchars($player1['player1']); ?></span>
                                            <span class="team-member"><?php echo htmlspecialchars($player1['player2']); ?></span>
                                        <?php elseif ($player1['player1'] === 'TBD'): ?>
                                            TBD
                                        <?php else: ?>
                                            <span class="team-member"><?php echo htmlspecialchars($player1['player1'] ?? ''); ?></span>
                                            <span class="team-member"><?php echo htmlspecialchars($player1['player2'] ?? ''); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($winner && $p1Seed == $winner): ?>
                                    <span class="winner-badge">Winner</span>
                                <?php elseif ($isVenueAdmin && !$winner && $p1Seed && $p2Seed && !$player1['is_bye'] && !($player2['is_bye'] ?? false)): ?>
                                    <button class="set-winner-btn" onclick="setWinner(<?php echo $tournamentId; ?>, <?php echo $roundNumber; ?>, <?php echo $matchNumber; ?>, <?php echo $p1Seed; ?>, <?php echo $p2Seed; ?>, <?php echo $p1Seed; ?>)">Win</button>
                                <?php endif; ?>
                            </div>
                            <?php if ($player2): ?>
                                <div class="player <?php 
                                    echo $player2['is_bye'] ? 'bye' : ''; 
                                    if ($winner && $p2Seed == $winner) echo ' winner';
                                    if ($winner && $p2Seed != $winner && !$player2['is_bye']) echo ' loser';
                                ?>">
                                    <div class="player-info">
                                        <span class="team-names">
                                            <?php if ($player2['is_bye']): ?>
                                                BYE
                                            <?php elseif (!empty($player2['player1']) && $player2['player1'] !== 'TBD'): ?>
                                                <span class="team-member"><?php echo htmlspecialchars($player2['player1']); ?></span>
                                                <span class="team-member"><?php echo htmlspecialchars($player2['player2']); ?></span>
                                            <?php elseif ($player2['player1'] === 'TBD'): ?>
                                                TBD
                                            <?php else: ?>
                                                <span class="team-member"><?php echo htmlspecialchars($player2['player1'] ?? ''); ?></span>
                                                <span class="team-member"><?php echo htmlspecialchars($player2['player2'] ?? ''); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if ($winner && $p2Seed == $winner): ?>
                                        <span class="winner-badge">Winner</span>
                                    <?php elseif ($isVenueAdmin && !$winner && $p1Seed && $p2Seed && !$player2['is_bye'] && !$player1['is_bye']): ?>
                                        <button class="set-winner-btn" onclick="setWinner(<?php echo $tournamentId; ?>, <?php echo $roundNumber; ?>, <?php echo $matchNumber; ?>, <?php echo $p1Seed; ?>, <?php echo $p2Seed; ?>, <?php echo $p2Seed; ?>)">Win</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        $matchNumber++;
                    endfor; ?>
                    </div>
                </div>
            <?php 
                // Prepare next round with winners
                $nextRound = [];
                $matchNum = 1;
                for ($i = 0; $i < count($playersArray); $i += 2) {
                    $p1 = $playersArray[$i];
                    $p2 = isset($playersArray[$i + 1]) ? $playersArray[$i + 1] : null;
                    
                    $matchKey = $roundNumber . '_' . $matchNum;
                    $result = $matchResults[$matchKey] ?? null;
                    
                    if ($result) {
                        // Winner determined - find winner from draw
                        $winnerSeed = $result['winner_seed'];
                        $nextRound[] = $draw[$winnerSeed];
                    } elseif (!$p2 || $p2['is_bye']) {
                        // Player 1 advances by BYE
                        $nextRound[] = $p1;
                    } elseif ($p1['is_bye']) {
                        // Player 2 advances by BYE
                        $nextRound[] = $p2;
                    } else {
                        // Match not yet played - TBD
                        $nextRound[] = [
                            'player1' => 'TBD',
                            'player2' => null,
                            'is_bye' => false,
                            'seed' => null
                        ];
                    }
                    $matchNum++;
                }
                $currentRoundPlayers = $nextRound;
                $roundNumber++;
            endwhile; ?>
        </div>
    </div>

    <div class="back-btn-container">
        <a href="tournaments.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Tournaments
        </a>
    </div>

    <script>
    function setWinner(tournamentId, roundNumber, matchNumber, team1Seed, team2Seed, winnerSeed) {
        if (!confirm('Confirm this team as the winner?')) {
            return;
        }

        fetch('/PadelUp/public/api/set_match_winner.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tournament_id: tournamentId,
                round_number: roundNumber,
                match_number: matchNumber,
                team1_seed: team1Seed,
                team2_seed: team2Seed,
                winner_seed: winnerSeed
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Could not set winner'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>