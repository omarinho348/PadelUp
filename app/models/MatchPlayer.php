<?php

require_once __DIR__ . '/MatchModel.php';

class MatchPlayer
{
    /**
     * Allows a player to join a match.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match to join.
     * @param int $playerId The ID of the player joining.
     * @return bool|string True on success, or an error message string on failure.
     */
    public static function joinMatch(mysqli $conn, int $matchId, int $playerId): bool|string
    {
        $conn->begin_transaction();

        try {
            // 1. Lock the match row and check if it's still open
            $sqlCheck = "SELECT status, current_players, max_players FROM matches WHERE match_id = ? FOR UPDATE";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $matchId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $match = $result->fetch_assoc();
            $stmtCheck->close();

            if (!$match) {
                throw new Exception("Match not found.");
            }

            if ($match['status'] !== 'open' || $match['current_players'] >= $match['max_players']) {
                throw new Exception("This match is already full or closed.");
            }

            // 2. Check if the player has already joined (to prevent duplicate entries)
            if (self::hasJoined($conn, $matchId, $playerId)) {
                throw new Exception("You have already joined this match.");
            }

            // 3. Insert the player into the match
            $sqlInsert = "INSERT INTO match_players (match_id, player_id) VALUES (?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("ii", $matchId, $playerId);

            if (!$stmtInsert->execute()) {
                throw new Exception($stmtInsert->error ?: "Could not join the match.");
            }
            $stmtInsert->close();

            // 4. Update the player count and status on the main match record
            MatchModel::updatePlayerCountAndStatus($conn, $matchId);

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    /**
     * Allows a player to leave a match.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match to leave.
     * @param int $playerId The ID of the player leaving.
     * @return bool|string True on success, or an error message string on failure.
     */
    public static function leaveMatch(mysqli $conn, int $matchId, int $playerId): bool|string
    {
        $conn->begin_transaction();

        try {
            // 1. Get match details to check creator status
            $match = MatchModel::findById($conn, $matchId);
            if (!$match) {
                throw new Exception("Match not found.");
            }

            if ($match['creator_id'] == $playerId) {
                throw new Exception("The match creator cannot leave the match. Please delete the match instead.");
            }

            // 2. Delete the player from match_players
            $sqlDelete = "DELETE FROM match_players WHERE match_id = ? AND player_id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("ii", $matchId, $playerId);

            if (!$stmtDelete->execute() || $stmtDelete->affected_rows === 0) {
                throw new Exception("You are not in this match or could not be removed.");
            }
            $stmtDelete->close();

            // 3. Update the player count and status on the main match record
            MatchModel::updatePlayerCountAndStatus($conn, $matchId);

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    /**
     * Checks if a player is already part of a specific match.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match.
     * @param int $playerId The ID of the player.
     * @return bool True if the player has joined, false otherwise.
     */
    public static function hasJoined(mysqli $conn, int $matchId, int $playerId): bool
    {
        $sql = "SELECT id FROM match_players WHERE match_id = ? AND player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $matchId, $playerId);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetches all players for a given match, including their profile details.
     *
     * @param mysqli $conn The database connection object.
     * @param int $matchId The ID of the match.
     * @return array An array of player data.
     */
    public static function getPlayersForMatch(mysqli $conn, int $matchId): array
    {
        $sql = "SELECT 
                    u.user_id,
                    u.name,
                    pp.skill_level
                FROM match_players mp
                JOIN users u ON mp.player_id = u.user_id
                LEFT JOIN player_profiles pp ON u.user_id = pp.player_id
                WHERE mp.match_id = ?
                ORDER BY mp.joined_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $matchId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}