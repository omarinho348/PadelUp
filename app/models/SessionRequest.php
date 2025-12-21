<?php
class SessionRequest
{
    public static function create(mysqli $conn, array $data)
    {
        try {
            $sql = "INSERT INTO session_requests (coach_id, requester_id, name, email, phone, message) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                return "DB Prepare Error: " . $conn->error;
            }
            $coachId = $data['coach_id'];
            $requesterId = $data['requester_id'] ?? null;
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'] ?? null;
            $message = $data['message'] ?? null;

            // bind_param requires variables
            $stmt->bind_param("iissss", $coachId, $requesterId, $name, $email, $phone, $message);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            $err = $stmt->error;
            $stmt->close();
            return $err ?: 'Insert failed';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function findByCoachId(mysqli $conn, int $coachId): array
    {
        $sql = "SELECT request_id, coach_id, requester_id, name, email, phone, message, status, created_at FROM session_requests WHERE coach_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $coachId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows ?: [];
    }

    public static function findById(mysqli $conn, int $id): ?array
    {
        $sql = "SELECT request_id, coach_id, requester_id, name, email, phone, message, status, created_at FROM session_requests WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function updateStatus(mysqli $conn, int $requestId, string $status): bool|string
    {
        $allowed = ['pending','accepted','declined'];
        if (!in_array($status, $allowed)) {
            return 'Invalid status';
        }
        $sql = "UPDATE session_requests SET status = ? WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return "DB Prepare Error: " . $conn->error;
        }
        $stmt->bind_param("si", $status, $requestId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        $err = $stmt->error;
        $stmt->close();
        return $err ?: 'Update failed';
    }
}
?>