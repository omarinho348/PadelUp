<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PlayerProfile.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/SessionRequest.php';
require_once __DIR__ . '/../models/Mail.php';
require_once __DIR__ . '/../models/Observer.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserController extends Observable
{
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private static function extractFirstName($fullName): string
    {
        $trimmed = trim((string)$fullName);
        if ($trimmed === '') {
            return 'PadelUp player';
        }
        $parts = preg_split('/\s+/', $trimmed);
        return $parts[0] ?: 'PadelUp player';
    }

    private static function buildEmailTemplate($firstName, $heading, $welcomeText, $highlightTitle, array $highlightLines, $buttonText, $buttonUrl, $specialHeading, $specialBody, $specialNote = 'Valid for 7 days | Terms apply'): string
    {
        $safeHeading = htmlspecialchars((string)$heading, ENT_QUOTES, 'UTF-8');
        $safeWelcome = htmlspecialchars((string)$welcomeText, ENT_QUOTES, 'UTF-8');
        $safeHighlightTitle = htmlspecialchars((string)$highlightTitle, ENT_QUOTES, 'UTF-8');
        $safeButtonText = htmlspecialchars((string)$buttonText, ENT_QUOTES, 'UTF-8');
        $safeButtonUrl = htmlspecialchars((string)$buttonUrl, ENT_QUOTES, 'UTF-8');
        $safeSpecialHeading = htmlspecialchars((string)$specialHeading, ENT_QUOTES, 'UTF-8');
        $safeSpecialBody = htmlspecialchars((string)$specialBody, ENT_QUOTES, 'UTF-8');
        $safeSpecialNote = htmlspecialchars((string)$specialNote, ENT_QUOTES, 'UTF-8');

        $highlightBody = '';
        foreach ($highlightLines as $line) {
            $highlightBody .= htmlspecialchars((string)$line, ENT_QUOTES, 'UTF-8') . '<br>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #16A34A, #22C55E); padding: 40px 30px; text-align: center; }
        .logo { color: white; font-size: 28px; font-weight: bold; margin: 0; }
        .tagline { color: rgba(255,255,255,0.9); font-size: 16px; margin-top: 10px; }
        .content { padding: 40px 30px; }
        .welcome-text { font-size: 18px; line-height: 1.6; color: #334155; }
        .highlight-box { background: #ECFDF5; border-left: 4px solid #16A34A; padding: 20px; margin: 30px 0; border-radius: 0 8px 8px 0; }
        .button { display: inline-block; background: #16A34A; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .steps { margin: 30px 0; }
        .step { display: flex; align-items: flex-start; margin-bottom: 20px; }
        .step-number { background: #16A34A; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0; font-weight: bold; }
        .step-text { flex: 1; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 40px 0; }
        .feature { text-align: center; padding: 20px; background: #F8FAFC; border-radius: 12px; }
        .feature-icon { font-size: 32px; margin-bottom: 15px; }
        .footer { background: #1E293B; color: white; padding: 30px; text-align: center; }
        .social-links { margin: 20px 0; }
        .social-icon { display: inline-block; margin: 0 10px; color: white; text-decoration: none; }
        @media (max-width: 600px) {
            .content { padding: 30px 20px; }
            .features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">🎾 PADELUP</h1>
            <p class="tagline">Book. Play. Repeat.</p>
        </div>

        <div class="content">
            <h2>{$safeHeading}</h2>
            <p class="welcome-text">{$safeWelcome}</p>

            <div class="highlight-box">
                <strong>{$safeHighlightTitle}</strong><br>
                {$highlightBody}
            </div>

            <div style="text-align: center;">
                <a href="{$safeButtonUrl}" class="button">{$safeButtonText}</a>
            </div>

            <div class="steps">
                <h3>Get Started in 3 Easy Steps:</h3>

                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <strong>Complete your profile</strong>
                        <p>Add your skill level and playing preferences</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <strong>Explore courts near you</strong>
                        <p>Find and book available slots instantly</p>
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">
                        <strong>Join or create matches</strong>
                        <p>Play with players at your level</p>
                    </div>
                </div>
            </div>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">🏆</div>
                    <h4>Smart Matchmaking</h4>
                    <p>Find players at your skill level</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">📅</div>
                    <h4>Instant Booking</h4>
                    <p>Reserve courts in 2 clicks</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">⚡</div>
                    <h4>Real-time Updates</h4>
                    <p>Get notified about openings</p>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #FEF3C7, #FDE68A); padding: 25px; border-radius: 12px; text-align: center; margin: 30px 0;">
                <h3>{$safeSpecialHeading}</h3>
                <p>{$safeSpecialBody}</p>
                <small>{$safeSpecialNote}</small>
            </div>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #E2E8F0;">
                <h3>Need Help?</h3>
                <p>
                    Check our <a href="https://padelup.com/help" style="color: #4F46E5;">Help Center</a> or 
                    reply to this email. We're here for you!
                </p>
            </div>
        </div>

        <div class="footer">
            <p><strong>PadelUp</strong><br>
            The modern way to play padel</p>
            
            <div class="social-links">
                <a href="https://instagram.com/padelup" class="social-icon">Instagram</a>
                <a href="https://facebook.com/padelup" class="social-icon">Facebook</a>
                <a href="https://twitter.com/padelup" class="social-icon">Twitter</a>
            </div>
            
            <p style="margin-top: 20px; font-size: 14px; color: #94A3B8;">
                You're receiving this email because you signed up for PadelUp.<br>
                <a href="https://padelup.com/unsubscribe" style="color: #94A3B8;">Unsubscribe</a> | 
                <a href="https://padelup.com/privacy" style="color: #94A3B8;">Privacy Policy</a>
            </p>
            
            <p style="font-size: 12px; color: #64748B; margin-top: 20px;">
                © 2024 PadelUp. All rights reserved.<br>
                123 Court Street, Sports City
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function register(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return "";
        }
        $required = ['fullname','email','gender','dob','side','password','confirm-password'];
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
            'phone' => htmlspecialchars($_POST['phone'] ?? null)
        ];
        $profileData = [
            'skill_level' => 0.00, // Set default skill level on registration
            'gender' => htmlspecialchars($_POST['gender']),
            'birth_date' => htmlspecialchars($_POST['dob']),
            'preferred_side' => htmlspecialchars($_POST['side'])
        ];
        $result = User::createPlayerUser(Database::getInstance()->getConnection(), $userData, $profileData);
        if ($result === true) {
            $firstName = self::extractFirstName($userData['name']);
            $signupDate = date('F j, Y');
            $body = self::buildEmailTemplate(
                $firstName,
                "Welcome to the Court, {$firstName}! 👋",
                "We're thrilled to have you join our padel community! Get ready to book courts, find partners, and elevate your game.",
                '🎉 Your account is ready!',
                [
                    'Email: ' . $userData['email'],
                    'Member since: ' . $signupDate
                ],
                'Go to Your Dashboard',
                'https://padelup.com/dashboard',
                '🎁 First Booking Discount!',
                'Use code WELCOME20 for 20% off your first court booking.'
            );
            Mail::send($userData['email'], 'Welcome to PadelUp!', $body);
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
        $row = User::findByEmail(Database::getInstance()->getConnection(), $email);
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
        $firstName = self::extractFirstName($row['name'] ?? '');
        $signInTime = date('F j, Y g:i A T');
        $body = self::buildEmailTemplate(
            $firstName,
            "Welcome back, {$firstName}! 👋",
            "Sign-in confirmed. If this wasn't you, secure your account in a few seconds.",
            '⚡ Sign-in details',
            [
                'Email: ' . $row['email'],
                'Signed in: ' . $signInTime
            ],
            'Open Dashboard',
            'https://padelup.com/dashboard',
            'Stay secure',
            'If you did not sign in, please reset your password immediately.',
            'Keep your account safe: enable strong passwords and sign out on shared devices.'
        );
        Mail::send($row['email'], 'PadelUp Sign-in Confirmation', $body);
        // Redirect based on role
        if ($row['role'] === 'super_admin') {
            header('Location: admin.php');
        } elseif ($row['role'] === 'venue_admin') {
            header('Location: ../controllers/VenueAdminDashboardController.php');
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
        return User::findById(Database::getInstance()->getConnection(), (int)$_SESSION['user_id']);
    }

    public static function getPlayerProfile(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return PlayerProfile::findByUserId(Database::getInstance()->getConnection(), (int)$_SESSION['user_id']);
    }

    public static function editProfile(): array
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: signin.php');
            exit();
        }
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_SESSION['user_id'];
            $conn = Database::getInstance()->getConnection();
            
            // Data for 'users' table
            $userData = [
                'name' => htmlspecialchars($_POST['fullname'] ?? ''),
                'phone' => htmlspecialchars($_POST['phone'] ?? '')
            ];

            // Data for 'player_profiles' table (if user is a player)
            $profileData = null;
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'player') {
                $profileData = [
                    'gender' => htmlspecialchars($_POST['gender'] ?? ''),
                    'birth_date' => htmlspecialchars($_POST['dob'] ?? ''),
                    'preferred_side' => htmlspecialchars($_POST['side'] ?? 'right')
                ];
            }

            $conn->begin_transaction();
            try {
                User::updateUser($conn, $userId, $userData);
                if ($profileData) {
                    PlayerProfile::update($conn, $userId, $profileData);
                }
                $conn->commit();
                $message = '<div class="success-message">Profile updated successfully!</div>';
                $_SESSION['name'] = $userData['name']; // Update session name immediately
            } catch (Exception $e) {
                $conn->rollback();
                $message = '<div class="error-message">Error updating profile: ' . $e->getMessage() . '</div>';
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
                $stmt = Database::getInstance()->getConnection()->prepare($sql);
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
        $userEmail = $_SESSION['email'] ?? null;
        $userName = $_SESSION['name'] ?? '';
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        if ($userEmail) {
            $firstName = self::extractFirstName($userName);
            $signOutTime = date('F j, Y g:i A T');
            $body = self::buildEmailTemplate(
                $firstName,
                "Signed out successfully, {$firstName}.",
                'You are signed out. Come back anytime to book courts and join matches.',
                '✅ Sign-out details',
                [
                    'Email: ' . $userEmail,
                    'Signed out: ' . $signOutTime
                ],
                'Sign back in',
                'https://padelup.com/signin',
                'Need to jump back in?',
                'Sign in again to keep your spot on the court.',
                'Tip: If this sign-out was unexpected, change your password and review active sessions.'
            );
            Mail::send($userEmail, 'PadelUp Sign-out Confirmation', $body);
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

    public static function requireVenueAdmin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../views/signin.php');
            exit();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'venue_admin') {
            header('Location: ../views/index.php');
            exit();
        }
    }

    // Fetch venue admins list (super admin only)
    public static function getVenueAdmins(): array
    {
        self::requireSuperAdmin();
        $searchTerm = trim($_GET['search'] ?? '');
        return User::listVenueAdmins(Database::getInstance()->getConnection(), $searchTerm);
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
        if(User::findByEmail(Database::getInstance()->getConnection(), $email)){
            return 'Email already in use.';
        }
        $data = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => $email,
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'phone' => htmlspecialchars($_POST['phone'] ?? '')
        ];
        $conn = Database::getInstance()->getConnection();
        $conn->begin_transaction();
        try {
            $res = User::createVenueAdminWithId($conn, $data);
            if(!is_int($res)){
                throw new Exception(is_string($res) ? $res : 'Failed to create venue admin.');
            }
            $venueAdminId = $res;
            $venueData = [
                'venue_id' => $venueAdminId, // Use the new user's ID as the venue's ID
                'venue_admin_id' => $venueAdminId,
                'name' => htmlspecialchars($_POST['venue_name']),
                'address' => htmlspecialchars($_POST['venue_address']),
                'city' => htmlspecialchars($_POST['venue_city']),
                'opening_time' => htmlspecialchars($_POST['opening_time']),
                'closing_time' => htmlspecialchars($_POST['closing_time']),
                'hourly_rate' => $hourlyRate,
                'logo_path' => 'public/Photos/VenueLogos/default.jpg'
            ];
            $vRes = Venue::create($conn, $venueData);
            if($vRes !== true){
                throw new Exception(is_string($vRes) ? $vRes : 'Failed to create venue.');
            }
            $conn->commit();
            self::getInstance()->notify('venue_admin_created', $data);
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
        $res = User::deleteVenueAdmin(Database::getInstance()->getConnection(), $id);
        if($res === true){
            self::getInstance()->notify('venue_admin_deleted', ['id'=>$id]);
            return 'VENUE_ADMIN_DELETED';
        }
        return is_string($res) ? $res : 'Failed to delete venue admin.';
    }

    // Fetch coaches list (super admin only)
    public static function getCoaches(): array
    {
        self::requireSuperAdmin();
        $searchTerm = trim($_GET['search'] ?? '');
        return User::listCoaches(Database::getInstance()->getConnection(), $searchTerm);
    }

    // Public coach listing for the frontend (no auth required)
    public static function getPublicCoaches(): array
    {
        $searchTerm = trim($_GET['search'] ?? '');
        return User::listCoaches(Database::getInstance()->getConnection(), $searchTerm);
    }

    // Handle coach creation
    public static function createCoach(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bio'])) { // Check for a coach-specific field
            return '';
        }
        
        $required = ['name','email','password','confirm_password','bio','hourly_rate','experience_years','location'];
        foreach($required as $f){
            if(empty($_POST[$f])){
                return 'All required fields must be filled for coach creation.';
            }
        }

        if($_POST['password'] !== $_POST['confirm_password']){
            return 'Passwords do not match.';
        }

        $email = htmlspecialchars($_POST['email']);
        if(User::findByEmail(Database::getInstance()->getConnection(), $email)){
            return 'Email already in use.';
        }

        $userData = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => $email,
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => 'coach',
            'phone' => htmlspecialchars($_POST['phone'] ?? '')
        ];

        $conn = Database::getInstance()->getConnection();
        $conn->begin_transaction();
        try {
            $newCoachId = User::createUser($conn, $userData);
            if(!is_int($newCoachId)){
                throw new Exception(is_string($newCoachId) ? $newCoachId : 'Failed to create coach user.');
            }

            // Handle optional profile image upload
            $profileImagePath = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $tmpPath = $_FILES['profile_image']['tmp_name'];
                $origName = $_FILES['profile_image']['name'];
                $fileSize = (int)$_FILES['profile_image']['size'];
                // Basic validations: size <= 2MB and mime type image/*
                if ($fileSize <= 2 * 1024 * 1024) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);
                    $allowed = ['image/jpeg','image/png','image/webp'];
                    if (in_array($mime, $allowed, true)) {
                        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
                            // Normalize extension based on mime
                            $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
                        }
                        $uploadDir = __DIR__ . '/../../public/uploads/coaches';
                        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
                        $filename = 'coach_' . $newCoachId . '_' . time() . '.' . $ext;
                        $destPath = $uploadDir . '/' . $filename;
                        if (@move_uploaded_file($tmpPath, $destPath)) {
                            // Store relative path starting with public/ to be consistent with other assets
                            $profileImagePath = 'public/uploads/coaches/' . $filename;
                        }
                    }
                }
            }

            $profileData = [
                'coach_id' => $newCoachId,
                'bio' => htmlspecialchars($_POST['bio']),
                'hourly_rate' => (float)$_POST['hourly_rate'],
                'experience_years' => (int)$_POST['experience_years'],
                'location' => htmlspecialchars($_POST['location']),
                'profile_image_path' => $profileImagePath
            ];

            $profileRes = User::createCoachProfile($conn, $profileData);
            if($profileRes !== true){
                 throw new Exception(is_string($profileRes) ? $profileRes : 'Failed to create coach profile.');
            }

            $conn->commit();
            self::getInstance()->notify('coach_created', $userData);
            return 'COACH_CREATED';
        } catch (Throwable $e) {
            $conn->rollback();
            return $e->getMessage();
        }
    }

    // Handle session request submission from public coach profile
    public static function createSessionRequest(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['request_coach_id'])) {
            return '';
        }
        $coachId = (int)$_POST['request_coach_id'];
        $name = trim(htmlspecialchars($_POST['name'] ?? ''));
        $email = trim(htmlspecialchars($_POST['email'] ?? ''));
        $phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
        $message = trim(htmlspecialchars($_POST['message'] ?? ''));

        if ($coachId <= 0) {
            return 'Invalid coach ID.';
        }
        if (empty($name) || empty($email)) {
            return 'Name and email are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address.';
        }

        $requestData = [
            'coach_id' => $coachId,
            'requester_id' => $_SESSION['user_id'] ?? null,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message
        ];

        $conn = Database::getInstance()->getConnection();
        $res = SessionRequest::create($conn, $requestData);
        if ($res === true) {
            $coach = User::findById($conn, $coachId);
            if ($coach && !empty($coach['email'])) {
                $subject = 'New Training Session Request via PadelUp';
                $body = "Hello " . htmlspecialchars(self::extractFirstName($coach['name'] ?? 'Coach')) . ",<br><br>" .
                        "You have a new session request from:<br>" .
                        "<strong>Name:</strong> " . $name . "<br>" .
                        "<strong>Email:</strong> " . $email . "<br>" .
                        (strlen($phone) ? ("<strong>Phone:</strong> " . $phone . "<br>") : "") .
                        (strlen($message) ? ("<strong>Message:</strong> " . nl2br($message) . "<br>") : "") .
                        "<br>Log in to your dashboard to review and respond.<br><br>" .
                        "— PadelUp";
                Mail::send($coach['email'], $subject, $body);
            }
            return 'REQUEST_SENT';
        }
        return is_string($res) ? $res : 'Failed to send request.';
    }

    // Return all session requests for the logged-in coach
    public static function getCoachRequests(): array
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'coach') {
            return [];
        }
        return SessionRequest::findByCoachId(Database::getInstance()->getConnection(), (int)$_SESSION['user_id']);
    }

    // Handle status update (accept / decline) by a coach for their own requests
    public static function updateSessionRequest(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_request_id']) || !isset($_POST['update_action'])) {
            return '';
        }
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'coach') {
            return 'Unauthorized';
        }
        $requestId = (int)$_POST['update_request_id'];
        $action = $_POST['update_action'] === 'accept' ? 'accepted' : ($_POST['update_action'] === 'decline' ? 'declined' : '');
        if (empty($action)) {
            return 'Invalid action';
        }

        $req = SessionRequest::findById(Database::getInstance()->getConnection(), $requestId);
        if (!$req) {
            return 'Request not found';
        }
        if ((int)$req['coach_id'] !== (int)$_SESSION['user_id']) {
            return 'Unauthorized';
        }

        $res = SessionRequest::updateStatus(Database::getInstance()->getConnection(), $requestId, $action);
        return $res === true ? 'STATUS_UPDATED' : (is_string($res) ? $res : 'Failed to update status');
    }

    public static function deleteCoach(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_coach_id'])) {
            return '';
        }
        $id = (int)$_POST['delete_coach_id'];
        $res = User::deleteById(Database::getInstance()->getConnection(), $id);
        if($res === true){
            self::getInstance()->notify('coach_deleted', ['id'=>$id]);
            return 'COACH_DELETED';
        }
        return 'Failed to delete coach.';
    }

    // Fetch all players for the admin user management page
    public static function getPlayers(): array
    {
        self::requireSuperAdmin();
        $searchTerm = trim($_GET['search'] ?? '');
        return User::listPlayers(Database::getInstance()->getConnection(), $searchTerm);
    }

    // Handle player deletion from the admin user management page
    public static function deletePlayer(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_player_id'])) {
            return '';
        }

        $id = (int)$_POST['delete_player_id'];
        $res = User::deleteById(Database::getInstance()->getConnection(), $id);
        if($res === true){
            self::getInstance()->notify('player_deleted', ['id'=>$id]);
            return 'PLAYER_DELETED';
        }
        return 'Failed to delete player.';
    }

    // Handle contacting a player from the admin user management page
    public static function contactPlayer(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !(isset($_POST['contact_player_id']) || isset($_POST['contact_coach_id']))) {
            return '';
        }

        $recipientEmail = filter_var($_POST['recipient_email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = htmlspecialchars($_POST['subject']);
        $message = htmlspecialchars($_POST['message']);

        if (!$recipientEmail || empty($subject) || empty($message)) {
            return 'Recipient, subject, and message are required.';
        }

        // --- EMAIL SENDING LOGIC WOULD GO HERE ---
        // Example:
        // $headers = 'From: no-reply@padelup.com' . "\r\n" . 'Reply-To: no-reply@padelup.com';
        // mail($recipientEmail, $subject, $message, $headers);

        return 'MESSAGE_SENT';
    }

    // Handle contacting a venue admin
    public static function contactVenueAdmin(): string
    {
        self::requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['contact_admin_id'])) {
            return '';
        }

        $recipientEmail = filter_var($_POST['recipient_email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = htmlspecialchars($_POST['subject']);
        $message = htmlspecialchars($_POST['message']);

        if (!$recipientEmail || empty($subject) || empty($message)) {
            return 'Recipient, subject, and message are required.';
        }

        // --- EMAIL SENDING LOGIC WOULD GO HERE ---
        // Example:
        // $headers = 'From: no-reply@padelup.com' . "\r\n" . 'Reply-To: no-reply@padelup.com';
        // mail($recipientEmail, $subject, $message, $headers);

        return 'MESSAGE_SENT';
    }
}
?>