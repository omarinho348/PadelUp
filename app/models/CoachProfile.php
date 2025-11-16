<?php
class CoachProfile
{
    public static function findByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT coach_id,bio,hourly_rate,experience_years,location FROM coach_profiles WHERE coach_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
?>