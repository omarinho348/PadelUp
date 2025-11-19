<?php

class MatchModel
{
    /**
     * Fetch all matches, optionally applying filters.
     * Joins with users, venues, and player_profiles to get rich data.
     *
     * @param mysqli $conn The database connection object.
     * @param array $filters An associative array of filters. Supported filters: 'venue_id', 'date', 'min_skill', 'max_skill'.
     * @return array An array of match data.
     */
    public static function fetchAll(mysqli $conn, array $filters = []): array
    {
        $sql = "SELECT 
                    m.*, 
                    u.name as creator_name, 
                    v.name as venue_name,
                    pp.skill_level as creator_skill_level
                FROM matches m
                JOIN users u ON m.creator_id = u.user_id
                JOIN venues v ON m.venue_id = v.venue_id
                LEFT JOIN player_profiles pp ON m.creator_id = pp.player_id
                WHERE m.status IN ('open', 'full')";

        $params = [];
        $types = '';

        if (!empty($filters['venue_id'])) {
            $sql .= " AND m.venue_id = ?";
            $params[] = $filters['venue_id'];
            $types .= 'i';
        }
        if (!empty($filters['date'])) {
            $sql .= " AND m.match_date = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        if (!empty($filters['min_skill'])) {
            $sql .= " AND m.max_skill_level >= ?";
            $params[] = $filters['min_skill'];
            $types .= 'i';
        }
        if (!empty($filters['max_skill'])) {
            $sql .= " AND m.min_skill_level <= ?";
            $params[] = $filters['max_skill'];
            $types .= 'i';
        }

        $sql .= " ORDER BY m.match_date ASC, m.match_time ASC";

        $stmt = $conn->prepare($sql);
        if ($stmt && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt || !$stmt->execute()) {
            // In a real app, you'd log this error.
            return [];
        }

        $result = $stmt->get_result();
        $stmt->close();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Fetch a single match by its ID.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match to fetch.
     * @return array|null The match data or null if not found.
     */
    public static function findById(mysqli $conn, int $matchId): ?array
    {
        $sql = "SELECT m.*, u.name as creator_name, v.name as venue_name
                FROM matches m
                JOIN users u ON m.creator_id = u.user_id
                JOIN venues v ON m.venue_id = v.venue_id
                WHERE m.match_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $matchId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Create a new match and add the creator to the match_players table.
     *
     * @param mysqli $conn The database connection object.
     * @param array $data The data for the new match.
     * @return int|string The new match ID on success, or an error message on failure.
     */
    public static function create(mysqli $conn, array $data): int|string
    {
        $conn->begin_transaction();
        try {
            // 1. Insert into `matches` table
            $sqlMatch = "INSERT INTO matches (creator_id, venue_id, match_date, match_time, min_skill_level, max_skill_level, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtMatch = $conn->prepare($sqlMatch);
            $stmtMatch->bind_param(
                "iisssis",
                $data['creator_id'],
                $data['venue_id'],
                $data['match_date'],
                $data['match_time'],
                $data['min_skill_level'],
                $data['max_skill_level'],
                $data['description']
            );

            if (!$stmtMatch->execute()) {
                throw new Exception($stmtMatch->error ?: 'Failed to create match.');
            }
            $newMatchId = $stmtMatch->insert_id;
            $stmtMatch->close();

            // 2. Add creator to `match_players` table
            $sqlPlayer = "INSERT INTO match_players (match_id, player_id) VALUES (?, ?)";
            $stmtPlayer = $conn->prepare($sqlPlayer);
            $stmtPlayer->bind_param("ii", $newMatchId, $data['creator_id']);

            if (!$stmtPlayer->execute()) {
                throw new Exception($stmtPlayer->error ?: 'Failed to add creator to match.');
            }
            $stmtPlayer->close();

            $conn->commit();
            return (int)$newMatchId;
        } catch (Exception $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    /**
     * Update the current player count and status for a specific match.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match to update.
     * @return bool True on success, false on failure.
     */
    public static function updatePlayerCountAndStatus(mysqli $conn, int $matchId): bool
    {
        // This function recalculates the player count from `match_players`
        // and updates the `matches` table accordingly.
        $sql = "UPDATE matches m, (SELECT COUNT(*) as p_count FROM match_players mp WHERE mp.match_id = ?) as sub
                SET
                    m.current_players = sub.p_count,
                    m.status = IF(sub.p_count >= m.max_players, 'full', 'open')
                WHERE m.match_id = ?";
        
        $stmt = $conn->prepare($sql);
        // Bind the matchId for both the subquery and the WHERE clause
        $stmt->bind_param("ii", $matchId, $matchId);
        return $stmt->execute();
    }
}
