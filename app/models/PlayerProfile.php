<?php
class PlayerProfile
{
    public static function findByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT player_id, skill_level, gender, birth_date, padel_iq_rating, preferred_side FROM player_profiles WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function update(mysqli $conn, int $userId, array $data): bool|string
    {
        $sql = "UPDATE player_profiles SET gender = ?, birth_date = ?, preferred_side = ? WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            return "Prepare failed: " . $conn->error;
        }
        $stmt->bind_param("sssi", 
            $data['gender'], 
            $data['birth_date'], 
            $data['preferred_side'], 
            $userId);
        $ok = $stmt->execute();
        if(!$ok){
            $err = $stmt->error;
            $stmt->close();
            return $err ?: false;
        }
        $stmt->close();
        return true;
    }

    /**
     * Updates only the skill_level for a specific player.
     */
    public static function updateSkillLevel(mysqli $conn, int $userId, float $skillLevel): bool
    {
        $sql = "UPDATE player_profiles SET skill_level = ? WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("di", $skillLevel, $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
?>