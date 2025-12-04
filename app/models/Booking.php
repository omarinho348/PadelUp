<?php
class Booking
{
    public static function listByVenue(mysqli $conn, int $venueId): array
    {
        $sql = "SELECT b.*, c.court_name, u.name AS user_name, u.email AS user_email, u.phone AS user_phone
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

    public static function updateStatusByAdmin(mysqli $conn, int $bookingId, int $adminId, string $status)
    {
        error_log('[Booking] updateStatusByAdmin called: bookingId=' . $bookingId . ' adminId=' . $adminId . ' status=' . $status);
        // Allow only specific statuses
        $allowed = ['cancelled','paid','confirmed','pending'];
        if (!in_array($status, $allowed, true)) {
            error_log('[Booking] Invalid status value: ' . $status);
            return 'Invalid status value';
        }

        // Verify this booking belongs to a venue managed by this admin
        $sql = "SELECT b.booking_id
                FROM bookings b
                JOIN courts c ON b.court_id = c.court_id
                JOIN venues v ON c.venue_id = v.venue_id
                WHERE b.booking_id = ? AND v.venue_admin_id = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            $err = $conn->error ?: 'unknown';
            error_log('[Booking] prepare failed (verify admin owns venue) bookingId=' . $bookingId . ' adminId=' . $adminId . ' conn_error=' . $err);
            return 'Prepare failed';
        }
        $stmt->bind_param('ii', $bookingId, $adminId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            error_log('[Booking] Unauthorized or booking not found: bookingId=' . $bookingId . ' adminId=' . $adminId);
            return 'Unauthorized or booking not found';
        }
        $stmt->close();

        // Fetch current status to enforce immutability once paid or cancelled
        $cur = $conn->prepare('SELECT status FROM bookings WHERE booking_id = ? LIMIT 1');
        if(!$cur){
            $err = $conn->error ?: 'unknown';
            error_log('[Booking] prepare failed (select current status) bookingId=' . $bookingId . ' conn_error=' . $err);
            return 'Prepare failed';
        }
        $cur->bind_param('i', $bookingId);
        $cur->execute();
        $cur->bind_result($currentStatus);
        $cur->fetch();
        $cur->close();

        if (in_array($currentStatus, ['paid','cancelled'], true)) {
            return 'Cannot change status of a paid or cancelled booking';
        }

        // Update status
        $u = $conn->prepare('UPDATE bookings SET status = ? WHERE booking_id = ?');
        if(!$u){
            $err = $conn->error ?: 'unknown';
            error_log('[Booking] prepare failed (update status) bookingId=' . $bookingId . ' conn_error=' . $err);
            return 'Prepare failed';
        }
        $u->bind_param('si', $status, $bookingId);
        $ok = $u->execute();
        $err = $u->error;
        if(!$ok){
            error_log('[Booking] update execute failed bookingId=' . $bookingId . ' stmt_error=' . $err);
            $u->close();
            return $err ?: 'Failed to update status';
        }
        $u->close();
        error_log('[Booking] status updated successfully bookingId=' . $bookingId . ' newStatus=' . $status);
        return true;
    }
}
?>