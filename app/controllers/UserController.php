<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PlayerProfile.php';
require_once __DIR__ . '/../models/Venue.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserController
{
    public static function register(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return "";
        }
        $required = ['fullname','email','gender','dob','hand','skill','password','confirm-password'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return "Please fill in all required fields.";
            }
        }
        if ($_POST['password'] !== $_POST['confirm-password']) {
            return "Passwords do not match.";
        }
        // Prepare user + profile data
        $userData = [
            'name' => htmlspecialchars($_POST['fullname']),
            'email' => htmlspecialchars($_POST['email']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => 'player',
            'phone' => null
        ];
        $profileData = [
            'skill_level' => htmlspecialchars($_POST['skill']),
            'gender' => htmlspecialchars($_POST['gender']),
            'birth_date' => htmlspecialchars($_POST['dob']),
            'padel_iq_rating' => 0,
            'preferred_hand' => htmlspecialchars($_POST['hand'])
        ];
        $result = User::createPlayerUser($GLOBALS['conn'], $userData, $profileData);
        if ($result === true) {
            header('Location: signin.php?signup=success');
            exit();
        }
        return is_string($result) ? $result : 'Registration failed.';
    }

    public static function login(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return "";
        }
        if (empty($_POST['email']) || empty($_POST['password'])) {
            return 'Email and password are required.';
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        $row = User::findByEmail($GLOBALS['conn'], $email);
        if (!$row) {
            return 'Invalid email or password.';
        }
        if (!password_verify($password, $row['password_hash'])) {
            return 'Invalid email or password.';
        }
        // Set new session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        // Redirect based on role
        if ($row['role'] === 'super_admin') {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }

    public static function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return User::findById($GLOBALS['conn'], (int)$_SESSION['user_id']);
    }

    public static function getPlayerProfile(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return PlayerProfile::findByUserId($GLOBALS['conn'], (int)$_SESSION['user_id']);
    }

    public static function editProfile(): array
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: signin.php');
            exit();
        }
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // For now allow updating only player profile basics if role player
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'player') {
                $fields = [
                    'skill_level' => htmlspecialchars($_POST['skill_level'] ?? ''),
                    'gender' => htmlspecialchars($_POST['gender'] ?? ''),
                    'birth_date' => htmlspecialchars($_POST['birth_date'] ?? ''),
                    'padel_iq_rating' => (int)($_POST['padel_iq_rating'] ?? 0),
                    'preferred_hand' => htmlspecialchars($_POST['preferred_hand'] ?? '')
                ];
                // Basic validation: ensure skill_level present
                if (empty($fields['skill_level'])) {
                    $message = '<div class="error-message">Skill level required.</div>';
                } else {
                    $result = PlayerProfile::update($GLOBALS['conn'], (int)$_SESSION['user_id'], $fields);
                    if ($result === true) {
                        $message = '<div class="success-message">Profile updated successfully!</div>';
                    } else {
                        $message = '<div class="error-message">Error updating profile: ' . htmlspecialchars((string)$result) . '</div>';
                    }
                }
            }
        }
        $user = self::getCurrentUser();
        $profile = self::getPlayerProfile();
        return [$message, $user, $profile];
    }

    public static function deleteAccount(): string
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: signin.php');
            exit();
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
                // Cascade delete via FK will remove profile rows
                $sql = "DELETE FROM users WHERE user_id = ?";
                $stmt = $GLOBALS['conn']->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        $stmt->close();
                        $_SESSION = [];
                        if (isset($_COOKIE[session_name()])) {
                            setcookie(session_name(), '', time() - 42000, '/');
                        }
                        session_destroy();
                        header('Location: index.php?account=deleted');
                        exit();
                    } else {
                        $error = 'Failed to delete account.';
                        $stmt->close();
                    }
                } else {
                    $error = 'Delete prepare failed.';
                }
            } else {
                $error = 'You must confirm deletion by checking the confirmation box.';
            }
        }
        return $error;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
        header('Location: index.php');
        exit();
    }

    public static function requireSuperAdmin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: signin.php');
            exit();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
            header('Location: index.php');
            exit();
        }
    }

    // Fetch venue admins list (super admin only)
    public static function getVenueAdmins(): array
    {
        self::requireSuperAdmin();
        return User::listVenueAdmins($GLOBALS['conn']);
    }

    // Handle venue admin creation
    public static function createVenueAdmin(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return '';
        }
        $required = ['name','email','password','confirm_password','venue_name','venue_address','venue_city','opening_time','closing_time','hourly_rate'];
        foreach($required as $f){
            if(empty($_POST[$f])){
                return 'All required fields must be filled.';
            }
        }
        if($_POST['password'] !== $_POST['confirm_password']){
            return 'Passwords do not match.';
        }
        // Validate hourly_rate integer >=0
        $rawRate = $_POST['hourly_rate'];
        if(!ctype_digit($rawRate)){
            return 'Hourly rate must be a whole number.';
        }
        $hourlyRate = (int)$rawRate;
        $email = htmlspecialchars($_POST['email']);
        // Ensure email not already used
        if(User::findByEmail($GLOBALS['conn'], $email)){
            return 'Email already in use.';
        }
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => $email,
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'phone' => htmlspecialchars($_POST['phone'] ?? '')
        ];
        $conn = $GLOBALS['conn'];
        $conn->begin_transaction();
        try {
            $res = User::createVenueAdminWithId($conn, $data);
            if(!is_int($res)){
                throw new Exception(is_string($res) ? $res : 'Failed to create venue admin.');
            }
            $venueAdminId = $res;
            $venueData = [
                'venue_admin_id' => $venueAdminId,
                'name' => htmlspecialchars($_POST['venue_name']),
                'address' => htmlspecialchars($_POST['venue_address']),
                'city' => htmlspecialchars($_POST['venue_city']),
                'opening_time' => htmlspecialchars($_POST['opening_time']),
                'closing_time' => htmlspecialchars($_POST['closing_time']),
                'hourly_rate' => $hourlyRate
            ];
            $vRes = Venue::create($conn, $venueData);
            if($vRes !== true){
                throw new Exception(is_string($vRes) ? $vRes : 'Failed to create venue.');
            }
            $conn->commit();
            return 'VENUE_ADMIN_CREATED';
        } catch (Throwable $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    public static function deleteVenueAdmin(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_admin_id'])) {
            return '';
        }
        $id = (int)$_POST['delete_admin_id'];
        $res = User::deleteVenueAdmin($GLOBALS['conn'], $id);
        if($res === true){
            return 'VENUE_ADMIN_DELETED';
        }
        return is_string($res) ? $res : 'Failed to delete venue admin.';
    }
}
?>