<?php
session_start();
require_once __DIR__ . '/../controllers/TournamentsController.php';

$tournamentId = (int)($_GET['id'] ?? 0);
if ($tournamentId <= 0) {
    header('Location: tournaments.php');
    exit();
}

$page = TournamentsController::getTournamentDrawPageData($tournamentId);
if (isset($page['redirect'])) {
    header('Location: ' . $page['redirect']);
    exit();
}

$tournament = $page['tournament'];
$logoPath = $page['logoPath'];
$teams = $page['teams'];
$draw = $page['draw'];
$matchResults = $page['matchResults'];
$isVenueAdmin = (bool)$page['isVenueAdmin'];
$totalRounds = (int)$page['totalRounds'];
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
            $maxRounds = $totalRounds;
            
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