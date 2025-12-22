<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/PlayerProfile.php';
require_once __DIR__ . '/../models/Tournament.php';
$conn = Database::getInstance()->getConnection();

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$currentUserId = $_SESSION['user_id'] ?? null;

$profile = null;
$skillLevel = null;
$ratingLabel = '—';
$matches = [];
$stats = [
    'played' => 0,
    'wins' => 0,
    'losses' => 0,
    'win_rate' => 0,
    'loss_rate' => 0,
    'tournaments_won' => 0,
];
$wonTournaments = [];
$recentTournaments = [];

if ($currentUserId) {
    // Profile and rating
    $profile = PlayerProfile::findByUserId($conn, (int)$currentUserId);
    if ($profile) {
        $skillLevel = isset($profile['skill_level']) ? (float)$profile['skill_level'] : null;
        if ($skillLevel !== null) {
            if ($skillLevel >= 6.0) $ratingLabel = 'Elite';
            elseif ($skillLevel >= 4.5) $ratingLabel = 'Advanced';
            elseif ($skillLevel >= 2.5) $ratingLabel = 'Intermediate';
            else $ratingLabel = 'Beginner';
        }
    }

    // Recent match history and stats (from match_results)
    $sql = "
        SELECT 
            mr.*, m.match_date, m.match_time,
            u1.name AS team1_p1, u2.name AS team1_p2,
            u3.name AS team2_p1, u4.name AS team2_p2
        FROM match_results mr
        JOIN matches m ON m.match_id = mr.match_id
        LEFT JOIN users u1 ON u1.user_id = mr.team1_player1_id
        LEFT JOIN users u2 ON u2.user_id = mr.team1_player2_id
        LEFT JOIN users u3 ON u3.user_id = mr.team2_player1_id
        LEFT JOIN users u4 ON u4.user_id = mr.team2_player2_id
        WHERE (? IN (mr.team1_player1_id, mr.team1_player2_id, mr.team2_player1_id, mr.team2_player2_id))
        ORDER BY m.match_date DESC, m.match_time DESC
        LIMIT 10
    ";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $currentUserId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            foreach ($rows as $r) {
                $isTeam1 = ($currentUserId == $r['team1_player1_id'] || $currentUserId == $r['team1_player2_id']);
                $userTeam = $isTeam1 ? '1' : '2';
                $won = ($r['winner_team'] === $userTeam);
                $opponentNames = $isTeam1
                    ? trim(($r['team2_p1'] ?? '') . (isset($r['team2_p2']) && $r['team2_p2'] ? ' & ' . $r['team2_p2'] : ''))
                    : trim(($r['team1_p1'] ?? '') . (isset($r['team1_p2']) && $r['team1_p2'] ? ' & ' . $r['team1_p2'] : ''));

                $sets = [];
                if ($r['team1_set1_score'] !== null && $r['team2_set1_score'] !== null) {
                    $sets[] = $r['team1_set1_score'] . '-' . $r['team2_set1_score'];
                }
                if ($r['team1_set2_score'] !== null && $r['team2_set2_score'] !== null) {
                    $sets[] = $r['team1_set2_score'] . '-' . $r['team2_set2_score'];
                }
                if ($r['team1_set3_score'] !== null && $r['team2_set3_score'] !== null) {
                    $sets[] = $r['team1_set3_score'] . '-' . $r['team2_set3_score'];
                }

                $matches[] = [
                    'date' => $r['match_date'],
                    'opponent' => $opponentNames ?: '—',
                    'score' => implode(', ', $sets),
                    'won' => $won,
                ];
                $stats['played']++;
                if ($won) { $stats['wins']++; } else { $stats['losses']++; }
            }
            if ($stats['played'] > 0) {
                $stats['win_rate'] = round(($stats['wins'] / $stats['played']) * 100);
                $stats['loss_rate'] = 100 - $stats['win_rate'];
            }
        }
        $stmt->close();
    }

    // Tournaments won
    $wonTournaments = Tournament::getWonTournaments($conn, (int)$currentUserId);
    $stats['tournaments_won'] = is_array($wonTournaments) ? count($wonTournaments) : 0;

    // Recent tournaments participated
    $recentSql = "
        SELECT t.tournament_id, t.tournament_name, t.tournament_date, t.status, t.max_size
        FROM tournament_teams tt
        JOIN tournaments t ON t.tournament_id = tt.tournament_id
        WHERE tt.player1_user_id = ? OR tt.player2_user_id = ?
        ORDER BY t.tournament_date DESC
        LIMIT 5
    ";
    if ($st = $conn->prepare($recentSql)) {
        $st->bind_param('ii', $currentUserId, $currentUserId);
        if ($st->execute()) {
            $res = $st->get_result();
            $recentTournaments = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
        $st->close();
    }
}
// Determine user's best result in a tournament (excluding cancellations)
function getUserTournamentResult(mysqli $conn, int $tournamentId, int $userId, int $maxSize): array {
    $resultLabel = 'Registered';
    $isChampion = false;
    $maxRounds = (int)log($maxSize, 2);
    // Find user's team seed
    $sqlSeed = "
        SELECT td.seed_position
        FROM tournament_draw td
        JOIN tournament_teams tt ON td.team_id = tt.id
        WHERE td.tournament_id = ? AND (tt.player1_user_id = ? OR tt.player2_user_id = ?)
        LIMIT 1";
    $stmt = $conn->prepare($sqlSeed);
    if ($stmt) {
        $stmt->bind_param('iii', $tournamentId, $userId, $userId);
        $stmt->execute();
        $seedRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $seedRow = null;
    }
    if (!$seedRow || !isset($seedRow['seed_position'])) {
        return [$resultLabel, $isChampion];
    }
    $seed = (int)$seedRow['seed_position'];
    // Get all matches involving this seed
    $sqlMatches = "
        SELECT round_number, winner_seed
        FROM tournament_match_results
        WHERE tournament_id = ? AND (team1_seed = ? OR team2_seed = ?)
        ORDER BY round_number ASC";
    $stmt2 = $conn->prepare($sqlMatches);
    if ($stmt2) {
        $stmt2->bind_param('iii', $tournamentId, $seed, $seed);
        $stmt2->execute();
        $rows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    } else {
        $rows = [];
    }
    if (empty($rows)) {
        return [$resultLabel, $isChampion];
    }
    $furthestRound = 0;
    $wonFinal = false;
    foreach ($rows as $r) {
        $round = (int)$r['round_number'];
        if ($round > $furthestRound) { $furthestRound = $round; }
        if ($round === $maxRounds && (int)$r['winner_seed'] === $seed) {
            $wonFinal = true;
        }
    }
    if ($wonFinal) {
        $resultLabel = '1st Place';
        $isChampion = true;
    } else {
        // Map furthest round reached to label
        if ($furthestRound === $maxRounds) {
            $resultLabel = '2nd Place';
        } elseif ($furthestRound === $maxRounds - 1) {
            $resultLabel = 'Semi-Finals';
        } elseif ($furthestRound === $maxRounds - 2) {
            $resultLabel = 'Quarter-Finals';
        } else {
            $resultLabel = 'Early Rounds';
        }
    }
    return [$resultLabel, $isChampion];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PadelIQ - Skill Level Insights</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/padeliq.css">
    </head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container">
        <div class="main-heading padeliq-header">
            <div class="header-content">
                <h1>PadelIQ <span class="accent">Skill Ratings</span></h1>
                <p>Analyze your game. Track your progress. Master your skills.</p>
            </div>
        </div>

        <?php if (!$currentUserId): ?>
            <div class="padeliq-card" style="margin-bottom:20px;">
                <p>Please <a href="/PadelUp/app/views/signin.php" class="link">sign in</a> to view your PadelIQ data.</p>
            </div>
        <?php endif; ?>

        <div class="padeliq-grid">
            <!-- Left Column: Rating & Stats (stacked) -->
            <aside class="padeliq-sidebar">
                <div class="padeliq-card rating-card">
                    <h3>Current Skill Level</h3>
                    <div class="rating-level"><?php echo htmlspecialchars($ratingLabel); ?></div>
                    <div class="rating-score">
                        <?php echo ($skillLevel !== null) ? number_format($skillLevel, 2) : 'N/A'; ?>
                        <span class="rating-score-of">/7.00</span>
                    </div>
                    <p class="rating-description">Based on your PadelUp skill level updates from matches and tournaments.</p>
                </div>
                <div class="padeliq-card">
                    <h3>Performance Stats</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="label">Win Rate</div>
                            <div class="value win"><?php echo (int)$stats['win_rate']; ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Loss Rate</div>
                            <div class="value loss"><?php echo (int)$stats['loss_rate']; ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Tournaments Won</div>
                            <div class="value"><?php echo (int)$stats['tournaments_won']; ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Matches Played</div>
                            <div class="value"><?php echo (int)$stats['played']; ?></div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right Column: Match History & Feedback -->
            <main class="padeliq-main">
                <div class="padeliq-card">
                    <h3>Recent Match History</h3>
                    <table class="match-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Opponent</th>
                                <th>Score</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($currentUserId && !empty($matches)): ?>
                                <?php foreach ($matches as $m): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['date'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($m['opponent']); ?></td>
                                        <td><?php echo htmlspecialchars($m['score'] ?: '—'); ?></td>
                                        <td class="<?php echo $m['won'] ? 'match-outcome-win' : 'match-outcome-loss'; ?>">
                                            <?php echo $m['won'] ? 'Win' : 'Loss'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="muted">No recent matches found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                

                <div class="padeliq-card">
                    <h3>Recent Tournament Results</h3>
                    <?php if ($currentUserId): ?>
                        <?php 
                            $cards = [];
                            // Include recent tournaments excluding cancellations, derive result
                            foreach ($recentTournaments as $rt) {
                                if ($rt['status'] === 'cancelled') { continue; }
                                $alreadyListedWin = false;
                                // Compute result achieved
                                [$label, $isChampion] = getUserTournamentResult($conn, (int)$rt['tournament_id'], (int)$currentUserId, (int)$rt['max_size']);
                                $cards[] = [
                                    'name' => $rt['tournament_name'],
                                    'status' => $label,
                                    'champion' => $isChampion,
                                    'date' => $rt['tournament_date'] ?? null,
                                ];
                            }
                        ?>
                        <?php if (empty($cards)): ?>
                            <div class="muted">No tournament activity found.</div>
                        <?php else: ?>
                            <div class="tournament-cards">
                                <?php foreach ($cards as $c): ?>
                                    <div class="tournament-card">
                                        <div class="tournament-card-header">
                                            <span class="tournament-name"><?php echo htmlspecialchars($c['name']); ?></span>
                                            <?php if (!empty($c['date'])): ?>
                                                <span class="tournament-date"><?php echo htmlspecialchars(date('M j, Y', strtotime($c['date']))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tournament-card-body">
                                            <span class="status-pill <?php echo !empty($c['champion']) ? 'champion' : ''; ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="muted">Sign in to see your tournament results.</div>
                    <?php endif; ?>
                </div>
            </main>
        </div>

    </div>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
            