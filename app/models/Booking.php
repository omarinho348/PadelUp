<?php
class Booking
{
    public static function listByVenue(mysqli $conn, int $venueId): array
    {
        $sql = "SELECT b.*, c.court_name, u.name AS user_name
                FROM bookings b
                JOIN courts c ON b.court_id = c.court_id
                JOIN users u ON b.user_id = u.user_id
                WHERE c.venue_id = ?
                ORDER BY b.booking_date DESC, b.start_time DESC";
        $stmt = $conn->prepare($sql);
        if(!$stmt){ return []; }
        $stmt->bind_param('i', $venueId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?? [];
    }
}
?>