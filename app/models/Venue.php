<?php
class Venue
{
    public static function create(mysqli $conn, array $data): bool|string
    {
        $sql = "INSERT INTO venues (venue_admin_id,name,address,city,opening_time,closing_time,hourly_rate) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            return 'Prepare failed';
        }
        $stmt->bind_param(
            'isssssi',
            $data['venue_admin_id'],
            $data['name'],
            $data['address'],
            $data['city'],
            $data['opening_time'],
            $data['closing_time'],
            $data['hourly_rate']
        );
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