<?php

require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/MatchModel.php';
require_once __DIR__ . '/../models/PlayerProfile.php'; // Required for skill level check
require_once __DIR__ . '/../models/MatchPlayer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MatchController
{
    /**
     * Fetches all open matches, applying any filters from the request.
     *
     * @return array An array of matches.
     */
    public static function showMatches(): array
    {
        $conn = $GLOBALS['conn'];
        $filters = [];

        // Example of how you might process filters from a form
        if (!empty($_GET['venue_id'])) {
            $filters['venue_id'] = (int)$_GET['venue_id'];
        }
        if (!empty($_GET['date'])) {
            $filters['date'] = $_GET['date'];
        }
        if (!empty($_GET['min_skill'])) {
            $filters['min_skill'] = (int)$_GET['min_skill'];
        }
        if (!empty($_GET['max_skill'])) {
            $filters['max_skill'] = (int)$_GET['max_skill'];
        }

        return MatchModel::fetchAll($conn, $filters);
    }

    /**
     * Handles the creation of a new match from a POST request.
     *
     * @return string A message indicating success or failure.
     */
    public static function createMatch(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'create_match') {
            return "";
        }

        if (!isset($_SESSION['user_id'])) {
            return "Error: You must be logged in to create a match.";
        }

        // --- Input Validation ---
        $required_fields = ['venue_id', 'match_date', 'match_time', 'min_skill_level', 'max_skill_level'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return "Error: Please fill all required fields.";
            }
        }

        if ((int)$_POST['min_skill_level'] > (int)$_POST['max_skill_level']) {
            return "Error: Minimum skill level cannot be greater than maximum skill level.";
        }

        $data = [
            'creator_id' => (int)$_SESSION['user_id'],
            'venue_id' => (int)$_POST['venue_id'],
            'match_date' => $_POST['match_date'],
            'match_time' => $_POST['match_time'],
            'min_skill_level' => (int)$_POST['min_skill_level'],
            'max_skill_level' => (int)$_POST['max_skill_level'],
            'description' => htmlspecialchars($_POST['description'] ?? '')
        ];

        $conn = $GLOBALS['conn'];
        $result = MatchModel::create($conn, $data);

        if (is_int($result)) {
            // Success
            header('Location: matchmaking.php?status=created');
            exit();
        } else {
            // Failure
            return "Error: " . $result;
        }
    }

    /**
     * Handles a player's request to join a match.
     *
     * @return string A message indicating success or failure.
     */
    public static function joinMatch(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'join_match') {
            return "";
        }

        if (!isset($_SESSION['user_id'])) {
            return "Error: You must be logged in to join a match.";
        }

        $matchId = (int)($_POST['match_id'] ?? 0);
        if ($matchId <= 0) {
            return "Error: Invalid match ID.";
        }

        $playerId = (int)$_SESSION['user_id'];
        $conn = $GLOBALS['conn'];
    
        // Optional: Check player eligibility (e.g., skill level)
        $match = MatchModel::findById($conn, $matchId);
        if (!$match) {
            return "Error: Match not found.";
        }

        // Fetch the player's profile to get their skill level for validation.
        $playerProfile = PlayerProfile::findByUserId($conn, $playerId);
        if ($playerProfile && isset($playerProfile['skill_level']) && $playerProfile['skill_level'] < $match['min_skill_level']) { // Check if player's skill is too low
            return "Error: Your skill level ({$playerProfile['skill_level']}) is below the minimum required ({$match['min_skill_level']}) for this match.";
        }

        $result = MatchPlayer::joinMatch($conn, $matchId, $playerId);


        if ($result === true) {
            // Success
            header('Location: matchmaking.php?status=joined&match_id=' . $matchId);
            exit();
        } else {
            // Failure
            return "Error: " . $result;
        }
    }

    /**
     * Handles a player's request to leave a match.
     *
     * @return string A message indicating success or failure.
     */
    public static function leaveMatch(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'leave_match') {
            return "";
        }

        if (!isset($_SESSION['user_id'])) {
            return "Error: You must be logged in to leave a match.";
        }

        $matchId = (int)($_POST['match_id'] ?? 0);
        if ($matchId <= 0) {
            return "Error: Invalid match ID.";
        }

        $playerId = (int)$_SESSION['user_id'];
        $conn = $GLOBALS['conn'];

        $result = MatchPlayer::leaveMatch($conn, $matchId, $playerId);

        if ($result === true) {
            // Success
            header('Location: matchmaking.php?status=left&match_id=' . $matchId);
            exit();
        } else {
            // Failure
            return "Error: " . $result;
        }
    }
}

?>
