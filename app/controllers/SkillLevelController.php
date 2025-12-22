<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/SkillLevel.php';
require_once __DIR__ . '/../models/PlayerProfile.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SkillLevelController
{
    const MIN_SKILL_LEVEL = 1.0;
    const MAX_SKILL_LEVEL = 7.0;

    /**
     * Handles the questionnaire submission, calculates the score,
     * and optionally saves it to the user's profile.
     */
    public static function calculate(): ?float
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        // Basic validation
        if (count($_POST) < 8) {
            // Not all questions were answered
            return null;
        }

        // If user is logged in, check if they already have a score. If so, prevent re-calculation.
        if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'player') {
            $existingProfile = PlayerProfile::findByUserId(Database::getInstance()->getConnection(), (int)$_SESSION['user_id']);
            if ($existingProfile && isset($existingProfile['skill_level']) && $existingProfile['skill_level'] > 0) {
                // User already has a score, do not proceed.
                // Redirect them to the same page on GET to show their existing score.
                header('Location: skill_level.php');
                exit();
            }
        }

        $answers = $_POST;
        $score = SkillLevel::calculate($answers);

        // If user is logged in, update their profile
        if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'player') {
            $userId = (int)$_SESSION['user_id'];
            // Use the new method to update the skill_level
            PlayerProfile::updateSkillLevel(Database::getInstance()->getConnection(), $userId, $score);
        }

        return $score;
    }

    /**
     * Updates player skill levels based on a tournament match result.
     * Called when a tournament match winner is recorded.
     * 
     * @param mysqli $conn Database connection
     * @param int $tournamentId Tournament ID
     * @param int $winnerSeed Seed position of the winning team
     * @param int $loserSeed Seed position of the losing team
     * @param int $roundNumber Round number (1 = first round, final = last round)
     * @param int $maxRounds Total number of rounds in the tournament
     * @return bool Success status
     */
    public static function updateFromTournamentMatch(
        mysqli $conn, 
        int $tournamentId, 
        int $winnerSeed, 
        int $loserSeed,
        int $roundNumber,
        int $maxRounds
    ): bool {
        try {
            // Get winner team players
            $winnerSql = "SELECT tt.player1_user_id, tt.player2_user_id
                         FROM tournament_draw td
                         JOIN tournament_teams tt ON td.team_id = tt.id
                         WHERE td.tournament_id = ? AND td.seed_position = ?";
            $stmt = $conn->prepare($winnerSql);
            $stmt->bind_param("ii", $tournamentId, $winnerSeed);
            $stmt->execute();
            $winnerTeam = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Get loser team players
            $loserSql = "SELECT tt.player1_user_id, tt.player2_user_id
                        FROM tournament_draw td
                        JOIN tournament_teams tt ON td.team_id = tt.id
                        WHERE td.tournament_id = ? AND td.seed_position = ?";
            $stmt = $conn->prepare($loserSql);
            $stmt->bind_param("ii", $tournamentId, $loserSeed);
            $stmt->execute();
            $loserTeam = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$winnerTeam || !$loserTeam) {
                return false; // Teams not found (might be BYE)
            }

            // Calculate skill adjustment based on round importance
            // Later rounds have bigger impact for winners, but smaller penalty for losers
            $baseAdjustment = 0.05; // Base adjustment for early rounds
            
            if ($roundNumber === $maxRounds) {
                // Finals - biggest reward for winning, smallest penalty for losing
                $winAdjustment = 0.20;
                $lossAdjustment = 0.025; // Very small penalty - you made it to the finals!
            } elseif ($roundNumber === $maxRounds - 1) {
                // Semi-finals
                $winAdjustment = 0.10;
                $lossAdjustment = 0.03;
            } elseif ($roundNumber === $maxRounds - 2) {
                // Quarter-finals
                $winAdjustment = 0.08;
                $lossAdjustment = 0.035;
            } else {
                // Early rounds - smaller reward, normal penalty
                $winAdjustment = $baseAdjustment;
                $lossAdjustment = 0.04;
            }

            // Update winners
            $winners = [$winnerTeam['player1_user_id'], $winnerTeam['player2_user_id']];
            foreach ($winners as $playerId) {
                $profile = PlayerProfile::findByUserId($conn, $playerId);
                if ($profile && isset($profile['skill_level'])) {
                    $newSkill = min(self::MAX_SKILL_LEVEL, $profile['skill_level'] + $winAdjustment);
                    PlayerProfile::updateSkillLevel($conn, $playerId, $newSkill);
                }
            }

            // Update losers - penalty decreases in later rounds
            $losers = [$loserTeam['player1_user_id'], $loserTeam['player2_user_id']];
            foreach ($losers as $playerId) {
                $profile = PlayerProfile::findByUserId($conn, $playerId);
                if ($profile && isset($profile['skill_level'])) {
                    $newSkill = max(self::MIN_SKILL_LEVEL, $profile['skill_level'] - $lossAdjustment);
                    PlayerProfile::updateSkillLevel($conn, $playerId, $newSkill);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating tournament skill levels: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Awards bonus skill points to tournament winners.
     * Called when a tournament is marked as completed.
     * 
     * @param mysqli $conn Database connection
     * @param int $tournamentId Tournament ID
     * @return bool Success status
     */
    public static function awardTournamentWinnerBonus(mysqli $conn, int $tournamentId): bool
    {
        try {
            // Get the maximum round number to find the finals
            $maxRoundSql = "SELECT MAX(round_number) as max_round FROM tournament_match_results WHERE tournament_id = ?";
            $stmt = $conn->prepare($maxRoundSql);
            $stmt->bind_param("i", $tournamentId);
            $stmt->execute();
            $maxRoundResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$maxRoundResult || !$maxRoundResult['max_round']) {
                return false; // No results recorded
            }

            $finalRound = (int)$maxRoundResult['max_round'];

            // Get the winner of the final match
            $finalSql = "SELECT winner_seed FROM tournament_match_results 
                        WHERE tournament_id = ? AND round_number = ?";
            $stmt = $conn->prepare($finalSql);
            $stmt->bind_param("ii", $tournamentId, $finalRound);
            $stmt->execute();
            $finalResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$finalResult) {
                return false; // Final not played yet
            }

            $winnerSeed = (int)$finalResult['winner_seed'];

            // Get winner team players
            $winnerSql = "SELECT tt.player1_user_id, tt.player2_user_id
                         FROM tournament_draw td
                         JOIN tournament_teams tt ON td.team_id = tt.id
                         WHERE td.tournament_id = ? AND td.seed_position = ?";
            $stmt = $conn->prepare($winnerSql);
            $stmt->bind_param("ii", $tournamentId, $winnerSeed);
            $stmt->execute();
            $winnerTeam = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$winnerTeam) {
                return false;
            }

            // Award bonus points to tournament champions (0.20 total bonus)
            $championBonus = 0.20;
            $winners = [$winnerTeam['player1_user_id'], $winnerTeam['player2_user_id']];
            
            foreach ($winners as $playerId) {
                $profile = PlayerProfile::findByUserId($conn, $playerId);
                if ($profile && isset($profile['skill_level'])) {
                    $newSkill = min(self::MAX_SKILL_LEVEL, $profile['skill_level'] + $championBonus);
                    PlayerProfile::updateSkillLevel($conn, $playerId, $newSkill);
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error awarding tournament winner bonus: " . $e->getMessage());
            return false;
        }
    }
}