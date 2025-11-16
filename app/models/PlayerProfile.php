<?php
class PlayerProfile
{
    public static function findByUserId(mysqli $conn, int $userId): ?array
    {
        $sql = "SELECT player_id,skill_level,gender,birth_date,padel_iq_rating,preferred_hand FROM player_profiles WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function update(mysqli $conn, int $userId, array $fields): bool|string
    {
        $sql = "UPDATE player_profiles SET skill_level = ?, gender = ?, birth_date = ?, padel_iq_rating = ?, preferred_hand = ? WHERE player_id = ?";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            return "Prepare failed";
        }
        $skill = $fields['skill_level'];
        $gender = $fields['gender'];
        $birth = $fields['birth_date'];
        $iq = (int)$fields['padel_iq_rating'];
        $hand = $fields['preferred_hand'];
        $stmt->bind_param("sssisi", $skill, $gender, $birth, $iq, $hand, $userId);
        $ok = $stmt->execute();
        if(!$ok){
            $err = $stmt->error;
            $stmt->close();
            return $err ?: false;
        }
        $stmt->close();
        return true;
    }
}
?>