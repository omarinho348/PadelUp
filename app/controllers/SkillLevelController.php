<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/SkillLevel.php';
require_once __DIR__ . '/../models/PlayerProfile.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SkillLevelController
{
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
            $existingProfile = PlayerProfile::findByUserId($GLOBALS['conn'], (int)$_SESSION['user_id']);
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
            PlayerProfile::updateSkillLevel($GLOBALS['conn'], $userId, $score);
        }

        return $score;
    }
}