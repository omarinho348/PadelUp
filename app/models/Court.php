<?php
class Court
{
    public static function listByVenue(mysqli $conn, int $venueId): array
    {
        $stmt = $conn->prepare('SELECT * FROM courts WHERE venue_id=? ORDER BY court_name');
        if(!$stmt){ return []; }
        $stmt->bind_param('i', $venueId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?? [];
    }

    public static function create(mysqli $conn, array $data): bool|string
    {
        $stmt = $conn->prepare('INSERT INTO courts (venue_id, court_name, court_type, is_active) VALUES (?,?,?,?)');
        if(!$stmt){ return 'Prepare failed'; }
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $stmt->bind_param('issi', $data['venue_id'], $data['court_name'], $data['court_type'], $isActive);
        $ok = $stmt->execute();
        if(!$ok){ $err=$stmt->error; $stmt->close(); return $err ?: false; }
        $stmt->close();
        return true;
    }

    public static function update(mysqli $conn, int $courtId, array $fields): bool|string
    {
        $sets=[]; $params=[]; $types='';
        if(isset($fields['court_name'])){ $sets[]='court_name=?'; $params[]=$fields['court_name']; $types.='s'; }
        if(isset($fields['court_type'])){ $sets[]='court_type=?'; $params[]=$fields['court_type']; $types.='s'; }
        if(isset($fields['is_active'])){ $sets[]='is_active=?'; $params[]=(int)$fields['is_active']; $types.='i'; }
        if(empty($sets)) return true;
        $sql='UPDATE courts SET '.implode(',', $sets).' WHERE court_id=?';
        $stmt = $conn->prepare($sql);
        if(!$stmt){ return 'Prepare failed'; }
        $types.='i'; $params[]=$courtId;
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        if(!$ok){ $err=$stmt->error; $stmt->close(); return $err ?: false; }
        $stmt->close();
        return true;
    }

    public static function delete(mysqli $conn, int $courtId): bool|string
    {
        $stmt = $conn->prepare('DELETE FROM courts WHERE court_id=?');
        if(!$stmt){ return 'Prepare failed'; }
        $stmt->bind_param('i', $courtId);
        $ok = $stmt->execute();
        if(!$ok){ $err=$stmt->error; $stmt->close(); return $err ?: false; }
        $stmt->close();
        return true;
    }

    public static function findById(mysqli $conn, int $courtId): ?array
    {
        $stmt = $conn->prepare('SELECT * FROM courts WHERE court_id=?');
        if(!$stmt){ return null; }
        $stmt->bind_param('i', $courtId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }
}
?>