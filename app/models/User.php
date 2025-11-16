<?php
class User
{
    // Base user fetches only identity & role columns
    public static function findByEmail(mysqli $conn, string $email): ?array
    {
        $sql = "SELECT user_id,name,email,password_hash,role,phone FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function findById(mysqli $conn, int $id): ?array
    {
        $sql = "SELECT user_id,name,email,role,phone,created_at FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // Create a player user + player profile (wrap in transaction)
    public static function createPlayerUser(mysqli $conn, array $userData, array $profileData): bool|string
    {
        $conn->begin_transaction();
        try {
            $sqlUser = "INSERT INTO users (name,email,password_hash,role,phone) VALUES (?,?,?,?,?)";
            $stmtUser = $conn->prepare($sqlUser);
            if (!$stmtUser) {
                throw new Exception("Prepare users failed");
            }
            $stmtUser->bind_param(
                "sssss",
                $userData['name'],
                $userData['email'],
                $userData['password_hash'],
                $userData['role'],
                $userData['phone']
            );
            if (!$stmtUser->execute()) {
                throw new Exception($stmtUser->error ?: 'Insert user failed');
            }
            $newId = $stmtUser->insert_id;
            $stmtUser->close();

            $sqlProfile = "INSERT INTO player_profiles (player_id,skill_level,gender,birth_date,padel_iq_rating,preferred_hand) VALUES (?,?,?,?,?,?)";
            $stmtProf = $conn->prepare($sqlProfile);
            if (!$stmtProf) {
                throw new Exception("Prepare player_profiles failed");
            }
            $stmtProf->bind_param(
                "isssis",
                $newId,
                $profileData['skill_level'],
                $profileData['gender'],
                $profileData['birth_date'],
                $profileData['padel_iq_rating'],
                $profileData['preferred_hand']
            );
            if (!$stmtProf->execute()) {
                throw new Exception($stmtProf->error ?: 'Insert profile failed');
            }
            $stmtProf->close();
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    // List all venue admins (minimal columns)
    public static function listVenueAdmins(mysqli $conn): array
    {
        $sql = "SELECT user_id,name,email,phone,created_at FROM users WHERE role='venue_admin' ORDER BY created_at DESC";
        $res = $conn->query($sql);
        if(!$res){
            return [];
        }
        $rows = [];
        while($r = $res->fetch_assoc()){
            $rows[] = $r;
        }
        return $rows;
    }

    // Create a venue admin user (no related profile)
    public static function createVenueAdmin(mysqli $conn, array $data): bool|string
    {
        try {
            $sql = "INSERT INTO users (name,email,password_hash,role,phone) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if(!$stmt){
                return "Prepare failed";
            }
            $role = 'venue_admin';
            $stmt->bind_param(
                "sssss",
                $data['name'],
                $data['email'],
                $data['password_hash'],
                $role,
                $data['phone']
            );
            if(!$stmt->execute()){
                $err = $stmt->error;
                $stmt->close();
                return $err ?: false;
            }
            $stmt->close();
            return true;
        } catch(Exception $e){
            return $e->getMessage();
        }
    }

    // Create a venue admin and return inserted user_id
    public static function createVenueAdminWithId(mysqli $conn, array $data): int|string
    {
        try {
            $sql = "INSERT INTO users (name,email,password_hash,role,phone) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if(!$stmt){
                return "Prepare failed";
            }
            $role = 'venue_admin';
            $stmt->bind_param(
                "sssss",
                $data['name'],
                $data['email'],
                $data['password_hash'],
                $role,
                $data['phone']
            );
            if(!$stmt->execute()){
                $err = $stmt->error;
                $stmt->close();
                return $err ?: false;
            }
            $newId = $stmt->insert_id;
            $stmt->close();
            return (int)$newId;
        } catch(Exception $e){
            return $e->getMessage();
        }
    }

    public static function deleteVenueAdmin(mysqli $conn, int $id): bool|string
    {
        // Ensure target is a venue_admin
        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        if(!$stmt){ return "Prepare failed"; }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if(!$row){ return "User not found"; }
        if($row['role'] !== 'venue_admin'){ return "Not a venue admin"; }
        $del = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if(!$del){ return "Delete prepare failed"; }
        $del->bind_param("i", $id);
        if(!$del->execute()){
            $err = $del->error;
            $del->close();
            return $err ?: false;
        }
        $del->close();
        return true;
    }
}
?>