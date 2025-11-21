<?php

require_once __DIR__ . '/PlayerProfile.php';

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

    /**
     * Records the result of a match and updates its status to 'completed'.
     *
     * @param mysqli $conn The database connection object.
     * @param array $data The result data.
     * @return bool|string True on success, or an error message on failure.
     */
    public static function recordResult(mysqli $conn, array $data): bool|string
    {
        // --- Calculate Winner ---
        $team1_sets_won = 0;
        $team2_sets_won = 0;
        foreach ($data['scores'] as $set) {
            if (!empty($set['team1']) && !empty($set['team2'])) {
                if ((int)$set['team1'] > (int)$set['team2']) {
                    $team1_sets_won++;
                } elseif ((int)$set['team2'] > (int)$set['team1']) {
                    $team2_sets_won++;
                }
            }
        }

        if ($team1_sets_won === $team2_sets_won) {
            return "The score results in a draw, which is not allowed. Please check the scores.";
        }
        $winner_team = $team1_sets_won > $team2_sets_won ? '1' : '2';

        $conn->begin_transaction();

        try {
            // 1. Insert into `match_results` table
            // Dynamically build the query to handle optional Set 3 scores
            $columns = [
                'match_id', 'team1_player1_id', 'team1_player2_id', 'team2_player1_id', 'team2_player2_id',
                'team1_set1_score', 'team2_set1_score', 'team1_set2_score', 'team2_set2_score', 'winner_team'
            ];
            $placeholders = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
            $types = 'iiiiiiiiss';
            $params = [
                $data['match_id'], $data['team1_player1_id'], $data['team1_player2_id'],
                $data['team2_player1_id'], $data['team2_player2_id'],
                (int)$data['scores']['set1']['team1'], (int)$data['scores']['set1']['team2'],
                (int)$data['scores']['set2']['team1'], (int)$data['scores']['set2']['team2'],
                $winner_team
            ];

            // Add Set 3 scores only if they are provided
            if (!empty($data['scores']['set3']['team1']) && !empty($data['scores']['set3']['team2'])) {
                $columns[] = 'team1_set3_score';
                $columns[] = 'team2_set3_score';
                $placeholders .= ', ?, ?';
                $types .= 'ii';
                $params[] = (int)$data['scores']['set3']['team1'];
                $params[] = (int)$data['scores']['set3']['team2'];
            }

            $sqlResult = "INSERT INTO match_results (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
            $stmtResult = $conn->prepare($sqlResult);
            if (!$stmtResult) {
                throw new Exception("Prepare failed for result insert: " . $conn->error);
            }

            // Use call_user_func_array for dynamic binding
            $bindParams = [&$types];
            foreach ($params as &$param) { $bindParams[] = &$param; }
            call_user_func_array([$stmtResult, 'bind_param'], $bindParams);


            if (!$stmtResult->execute()) {
                throw new Exception($stmtResult->error ?: 'Failed to save match result.');
            }
            $stmtResult->close();

            // 2. Update the status of the original match to 'completed'
            $sqlMatchUpdate = "UPDATE matches SET status = 'completed' WHERE match_id = ?";
            $stmtMatchUpdate = $conn->prepare($sqlMatchUpdate);
            $stmtMatchUpdate->bind_param("i", $data['match_id']);
            $stmtMatchUpdate->execute();
            $stmtMatchUpdate->close();

            // 3. Update player skill levels based on match result
            $total_game_difference = 0;
            foreach ($data['scores'] as $set) {
                if (isset($set['team1']) && $set['team1'] !== '' && isset($set['team2']) && $set['team2'] !== '') {
                    $total_game_difference += abs((int)$set['team1'] - (int)$set['team2']);
                }
            }

            // Determine the adjustment value based on how close the match was
            if ($total_game_difference <= 4) { // Very close match
                $skill_adjustment = 0.03;
            } elseif ($total_game_difference >= 8) { // Decisive victory
                $skill_adjustment = 0.10;
            } else { // Moderately close match
                $skill_adjustment = 0.06;
            }

            define('MIN_SKILL_LEVEL', 1.0);
            define('MAX_SKILL_LEVEL', 7.0);

            $winners = ($winner_team === '1') ? [$data['team1_player1_id'], $data['team1_player2_id']] : [$data['team2_player1_id'], $data['team2_player2_id']];
            $losers = ($winner_team === '1') ? [$data['team2_player1_id'], $data['team2_player2_id']] : [$data['team1_player1_id'], $data['team1_player2_id']];

            foreach ($winners as $playerId) {
                $profile = PlayerProfile::findByUserId($conn, $playerId);
                if ($profile && isset($profile['skill_level'])) {
                    $newSkill = min(MAX_SKILL_LEVEL, $profile['skill_level'] + $skill_adjustment);
                    PlayerProfile::updateSkillLevel($conn, $playerId, $newSkill);
                }
            }

            foreach ($losers as $playerId) {
                $profile = PlayerProfile::findByUserId($conn, $playerId);
                if ($profile && isset($profile['skill_level'])) {
                    $newSkill = max(MIN_SKILL_LEVEL, $profile['skill_level'] - $skill_adjustment);
                    PlayerProfile::updateSkillLevel($conn, $playerId, $newSkill);
                }
            }


            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }
}
