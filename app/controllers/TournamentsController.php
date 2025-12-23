<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/Tournament.php';

class TournamentsController
{
    /**
     * Prepare data for the tournaments listing page.
     * Returns an associative array containing the current user id and
     * a list of tournaments with computed properties for rendering.
     */
    public static function getTournamentsPageData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $conn = Database::getInstance()->getConnection();
        $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'max_level' => isset($_GET['max_level']) && $_GET['max_level'] !== '' ? (int)$_GET['max_level'] : null,
            'date' => $_GET['date'] ?? ''
        ];

        $tournaments = Tournament::listAll($conn, $filters);
        $enhanced = [];

        foreach ($tournaments as $t) {
            $tId = (int)$t['tournament_id'];
            $regCount = Tournament::getRegistrationCount($conn, $tId);
            $isFull = $regCount >= (int)$t['max_size'];

            $tournamentDateTime = strtotime($t['tournament_date'] . ' ' . $t['start_time']);
            $twelveHoursBefore = $tournamentDateTime - (12 * 60 * 60);
            $within12Hours = (time() >= $twelveHoursBefore);

            $showDraw = $within12Hours || $isFull;
            $registrationClosed = $within12Hours || $isFull;

            $hasRegistered = false;
            if ($currentUserId) {
                $hasRegistered = Tournament::hasRegistered($conn, $tId, $currentUserId);
            }

            $t['reg_count'] = $regCount;
            $t['is_full'] = $isFull;
            $t['within_12_hours'] = $within12Hours;
            $t['show_draw'] = $showDraw;
            $t['registration_closed'] = $registrationClosed;
            $t['has_registered'] = $hasRegistered;

            $enhanced[] = $t;
        }

        return [
            'currentUserId' => $currentUserId,
            'tournaments' => $enhanced,
        ];
    }

    /**
     * Prepare data for the tournament draw page. Handles existing draw retrieval
     * or generation/saving when needed. Returns an array of page variables.
     * If the draw should not be shown yet, returns ['redirect' => 'tournaments.php'].
     */
    public static function getTournamentDrawPageData(int $tournamentId): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $conn = Database::getInstance()->getConnection();

        // Get tournament details
        $sql = "SELECT t.*, v.name as venue_name, v.city as venue_city, v.logo_path 
                FROM tournaments t 
                JOIN venues v ON t.venue_id = v.venue_id 
                WHERE t.tournament_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $tournament = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$tournament) {
            return ['redirect' => 'tournaments.php'];
        }

        // Process logo path
        $logoPath = $tournament['logo_path'] ?? '';
        if ($logoPath && !str_starts_with($logoPath, 'http')) {
            $logoPath = str_replace('public/', '', $logoPath);
            $logoPath = '/PadelUp/public/' . ltrim($logoPath, '/');
        }

        // Get registered teams (doubles)
        $teamsSql = "SELECT tt.id, tt.player1_user_id, tt.player2_user_id, u1.name AS p1_name, u2.name AS p2_name
                     FROM tournament_teams tt
                     JOIN users u1 ON tt.player1_user_id = u1.user_id
                     JOIN users u2 ON tt.player2_user_id = u2.user_id
                     WHERE tt.tournament_id = ?
                     ORDER BY tt.registered_at";
        $stmt = $conn->prepare($teamsSql);
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $regCount = count($teams);
        $isFull = $regCount >= (int)$tournament['max_size'];

        $tournamentDateTime = strtotime($tournament['tournament_date'] . ' ' . $tournament['start_time']);
        $twelveHoursBefore = $tournamentDateTime - (12 * 60 * 60);
        $within12Hours = (time() >= $twelveHoursBefore);

        if (!$within12Hours && !$isFull) {
            return ['redirect' => 'tournaments.php'];
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
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $existingDraw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (!empty($existingDraw)) {
            $draw = [];
            foreach ($existingDraw as $entry) {
                $seed = (int)$entry['seed_position'];
                if ($entry['is_bye']) {
                    $draw[$seed] = [
                        'player1' => 'BYE',
                        'player2' => null,
                        'is_bye' => true,
                        'seed' => $seed,
                    ];
                } else {
                    $draw[$seed] = [
                        'player1' => $entry['p1_name'],
                        'player2' => $entry['p2_name'],
                        'is_bye' => false,
                        'seed' => $seed,
                    ];
                }
            }
            ksort($draw);
        } else {
            $draw = self::generateAndSaveTeamDraw($teams, (int)$tournament['max_size'], $tournamentId, $conn);
        }

        // Load all match results
        $resultsSql = "SELECT round_number, match_number, team1_seed, team2_seed, winner_seed 
                       FROM tournament_match_results 
                       WHERE tournament_id = ?";
        $stmt = $conn->prepare($resultsSql);
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $resultsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $matchResults = [];
        foreach ($resultsData as $result) {
            $key = $result['round_number'] . '_' . $result['match_number'];
            $matchResults[$key] = $result;
        }

        $isVenueAdmin = false;
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $isVenueAdmin = ($tournament['created_by'] == $userId);
        }

        $totalRounds = (int)log((int)$tournament['max_size'], 2);

        return [
            'tournament' => $tournament,
            'logoPath' => $logoPath,
            'teams' => $teams,
            'draw' => $draw,
            'matchResults' => $matchResults,
            'isVenueAdmin' => $isVenueAdmin,
            'totalRounds' => $totalRounds,
        ];
    }

    /**
     * Generate a random draw of teams and save to the database.
     * Returns the draw in the format expected by the view.
     */
    private static function generateAndSaveTeamDraw(array $teams, int $maxTeams, int $tournamentId, \mysqli $conn): array
    {
        $totalSlots = $maxTeams;
        $drawData = [];

        foreach ($teams as $team) {
            $drawData[] = [
                'team_id' => $team['id'],
                'player1' => $team['p1_name'],
                'player2' => $team['p2_name'],
                'is_bye' => false,
            ];
        }

        while (count($drawData) < $totalSlots) {
            $drawData[] = [
                'team_id' => null,
                'player1' => 'BYE',
                'player2' => null,
                'is_bye' => true,
            ];
        }

        shuffle($drawData);

        $stmt = $conn->prepare('INSERT INTO tournament_draw (tournament_id, seed_position, team_id, is_bye) VALUES (?, ?, ?, ?)');
        foreach ($drawData as $position => $entry) {
            $seedPosition = $position + 1;
            $teamId = $entry['team_id'];
            $isBye = $entry['is_bye'] ? 1 : 0;
            $stmt->bind_param('iiii', $tournamentId, $seedPosition, $teamId, $isBye);
            $stmt->execute();
        }
        $stmt->close();

        $result = [];
        $position = 1;
        foreach ($drawData as $entry) {
            $result[$position] = [
                'player1' => $entry['player1'],
                'player2' => $entry['player2'],
                'is_bye' => $entry['is_bye'],
                'seed' => $position,
            ];
            $position++;
        }
        return $result;
    }
}

?>