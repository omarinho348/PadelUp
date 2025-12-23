<?php
class Tournament
{
    /**
     * Create a new tournament record.
     *
     * @param mysqli $conn
     * @param array $data keys: venue_id, created_by, tournament_date, start_time, max_level, total_prize_money
     * @return int|string new tournament_id or error string
     */
    public static function create(mysqli $conn, array $data): int|string
    {
        // tournament_name included; include max_size and entrance_fee
        $sql = "INSERT INTO tournaments (venue_id, tournament_name, created_by, tournament_date, start_time, max_level, max_size, entrance_fee, total_prize_money) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return 'Prepare failed: ' . $conn->error;
        }

        // types: i=venue, s=name, i=created_by, s=date, s=time, i=max_level, i=max_size, d=entrance_fee, d=prize
        $stmt->bind_param('isissiidd', $data['venue_id'], $data['tournament_name'], $data['created_by'], $data['tournament_date'], $data['start_time'], $data['max_level'], $data['max_size'], $data['entrance_fee'], $data['total_prize_money']);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            return $err ?: 'Execute failed';
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return (int)$newId;
    }

    public static function listByVenue(mysqli $conn, int $venueId): array
    {
        $stmt = $conn->prepare('SELECT * FROM tournaments WHERE venue_id=? ORDER BY tournament_date DESC, start_time DESC');
        if (!$stmt) { return []; }
        $stmt->bind_param('i', $venueId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?: [];
    }

    public static function listAll(mysqli $conn, array $filters = []): array
    {
        $sql = "SELECT t.*, v.name AS venue_name, v.city AS venue_city
                FROM tournaments t
                JOIN venues v ON t.venue_id = v.venue_id
                WHERE t.status NOT IN ('cancelled', 'completed')";
        
        $params = [];
        $types = '';
        
        // Filter by search term (tournament name only)
        if (!empty($filters['search'])) {
            $sql .= " AND t.tournament_name LIKE ?";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $types .= 's';
        }
        
        // Filter by max skill level (exact match)
        if (isset($filters['max_level']) && $filters['max_level'] > 0) {
            $sql .= " AND t.max_level = ?";
            $params[] = $filters['max_level'];
            $types .= 'i';
        }
        
        // Filter by date
        if (!empty($filters['date'])) {
            $sql .= " AND t.tournament_date = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        
        $sql .= " ORDER BY t.tournament_date ASC, t.start_time ASC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) { return []; }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?: [];
    }

    /**
     * Check if a user already registered for a tournament.
     */
    public static function hasRegistered(mysqli $conn, int $tournamentId, int $userId): bool
    {
        // Check team table: user appears as either player1 or player2 in any team for this tournament
        $sql = 'SELECT id FROM tournament_teams WHERE tournament_id=? AND (player1_user_id=? OR player2_user_id=?) LIMIT 1';
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('iii', $tournamentId, $userId, $userId);
        $stmt->execute();
        $stmt->store_result();
        $has = $stmt->num_rows > 0;
        $stmt->close();
        return $has;
    }

    public static function getRegistrationCount(mysqli $conn, int $tournamentId): int
    {
        // Count how many teams registered for this tournament
        $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM tournament_teams WHERE tournament_id = ?');
        if (!$stmt) return 0;
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ? (int)$row['cnt'] : 0;
    }

    public static function hasDraw(mysqli $conn, int $tournamentId): bool
    {
        $stmt = $conn->prepare('SELECT id FROM tournament_draw WHERE tournament_id = ? LIMIT 1');
        if (!$stmt) return false;
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $stmt->store_result();
        $has = $stmt->num_rows > 0;
        $stmt->close();
        return $has;
    }

    /**
     * Register a user for a tournament.
     * Returns true on success, or string error on failure.
     */
    public static function getWonTournaments(mysqli $conn, int $userId): array
    {
        $sql = "
            SELECT t.*, v.name as venue_name
            FROM tournaments t
            JOIN venues v ON t.venue_id = v.venue_id
            JOIN tournament_match_results tmr ON t.tournament_id = tmr.tournament_id
            JOIN tournament_draw td ON tmr.winner_seed = td.seed_position AND tmr.tournament_id = td.tournament_id
            JOIN tournament_teams tt ON td.team_id = tt.id
            WHERE (tt.player1_user_id = ? OR tt.player2_user_id = ?)
            AND tmr.round_number = LOG2(t.max_size)
            ORDER BY t.tournament_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ii', $userId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Register a team (current user + partner) for a tournament.
     * @param mysqli $conn
     * @param int $tournamentId
     * @param int $userId       Current logged-in user id
     * @param int $partnerId    Partner user id
     * @return bool|string      True on success or error string
     */
    public static function registerTeam(mysqli $conn, int $tournamentId, int $userId, int $partnerId)
    {
        if ($userId === $partnerId) return 'Partner must be a different user';

        // Prevent duplicate registration by either player
        if (self::hasRegistered($conn, $tournamentId, $userId)) {
            return 'You are already registered in this tournament';
        }
        if (self::hasRegistered($conn, $tournamentId, $partnerId)) {
            return 'Your partner is already registered in this tournament';
        }

        // Capacity (teams)
        $stmt = $conn->prepare('SELECT max_size FROM tournaments WHERE tournament_id = ? LIMIT 1');
        if (!$stmt) return 'Prepare failed';
        $stmt->bind_param('i', $tournamentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $t = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if (!$t) return 'Tournament not found';
        $maxTeams = (int)($t['max_size'] ?? 0);
        $teamCount = self::getRegistrationCount($conn, $tournamentId);
        if ($maxTeams > 0 && $teamCount >= $maxTeams) {
            return 'Tournament is full';
        }

        // Normalize pair (lowest id first) to avoid pair-order duplicates
        $p1 = min($userId, $partnerId);
        $p2 = max($userId, $partnerId);

        $stmt = $conn->prepare('INSERT INTO tournament_teams (tournament_id, player1_user_id, player2_user_id) VALUES (?, ?, ?)');
        if (!$stmt) return 'Prepare failed';
        $stmt->bind_param('iii', $tournamentId, $p1, $p2);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            return $err ?: 'Execute failed';
        }
        $stmt->close();
        return true;
    }
}
?>
