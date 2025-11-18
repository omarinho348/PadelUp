<?php
class Venue
{
    public static function create(mysqli $conn, array $data): bool|string
    {
        $sql = "INSERT INTO venues (venue_admin_id,name,address,city,opening_time,closing_time,hourly_rate,logo_path) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt){
            return 'Prepare failed';
        }
        $logo_path = $data['logo_path'] ?? null;
        $stmt->bind_param(
            'isssssis',
            $data['venue_admin_id'],
            $data['name'],
            $data['address'],
            $data['city'],
            $data['opening_time'],
            $data['closing_time'],
            $data['hourly_rate'],
            $logo_path
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

    public static function listByAdmin(mysqli $conn, int $venueAdminId): array
    {
        $sql = "SELECT * FROM venues WHERE venue_admin_id = ? ORDER BY name";
        $stmt = $conn->prepare($sql);
        if(!$stmt){ return []; }
        $stmt->bind_param('i', $venueAdminId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?? [];
    }

    public static function listAll(mysqli $conn): array
    {
        if(!$conn){ return []; }
        $stmt = $conn->prepare('SELECT * FROM venues ORDER BY name');
        if(!$stmt){ return []; }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows ?: [];
    }

    public static function update(mysqli $conn, int $venueId, array $fields): bool|string
    {
        $sets = [];
        $params = [];
        $types = '';
        if(isset($fields['name'])){ $sets[]='name=?'; $params[]=$fields['name']; $types.='s'; }
        if(isset($fields['address'])){ $sets[]='address=?'; $params[]=$fields['address']; $types.='s'; }
        if(isset($fields['city'])){ $sets[]='city=?'; $params[]=$fields['city']; $types.='s'; }
        if(isset($fields['opening_time'])){ $sets[]='opening_time=?'; $params[]=$fields['opening_time']; $types.='s'; }
        if(isset($fields['closing_time'])){ $sets[]='closing_time=?'; $params[]=$fields['closing_time']; $types.='s'; }
        if(isset($fields['hourly_rate'])){ $sets[]='hourly_rate=?'; $params[]=(int)$fields['hourly_rate']; $types.='i'; }
        if(isset($fields['logo_path'])){ $sets[]='logo_path=?'; $params[]=$fields['logo_path']; $types.='s'; }
        if(empty($sets)) return true;
        $sql = 'UPDATE venues SET '.implode(',', $sets).' WHERE venue_id=?';
        $stmt = $conn->prepare($sql);
        if(!$stmt){ return 'Prepare failed'; }
        $types .= 'i';
        $params[] = $venueId;
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        if(!$ok){ $err=$stmt->error; $stmt->close(); return $err ?: false; }
        $stmt->close();
        return true;
    }
    public static function findById(mysqli $conn, int $venueId): ?array
    {
        $stmt = $conn->prepare('SELECT * FROM venues WHERE venue_id=?');
        if(!$stmt){ return null; }
        $stmt->bind_param('i', $venueId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    public static function findFirstByAdmin(mysqli $conn, int $venueAdminId): ?array
    {
        $stmt = $conn->prepare('SELECT * FROM venues WHERE venue_admin_id=? ORDER BY venue_id ASC LIMIT 1');
        if(!$stmt){ return null; }
        $stmt->bind_param('i', $venueAdminId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }
}
?>