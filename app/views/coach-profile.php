<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../models/CoachProfile.php';

// Handle session request form submission (returns '' when not posted)
$requestMessage = UserController::createSessionRequest();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: coach-finder.php');
    exit();
}

$user = User::findById($GLOBALS['conn'], $id);
$profile = CoachProfile::findByUserId($GLOBALS['conn'], $id);
if (!$user || !$profile) {
    // If coach not found, redirect back
    header('Location: coach-finder.php');
    exit();
}

$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
$phone = htmlspecialchars($user['phone'] ?? '');
$bio = htmlspecialchars($profile['bio'] ?? '');
$hourly = isset($profile['hourly_rate']) ? number_format((float)$profile['hourly_rate'], 2) : '—';
$exp = htmlspecialchars($profile['experience_years'] ?? '0');
$location = htmlspecialchars($profile['location'] ?? '');
$imgIndex = ($id % 6) + 1;
$candidateWebp = __DIR__ . "/../../public/Photos/coach{$imgIndex}.webp";
$candidateJpg = __DIR__ . "/../../public/Photos/coach{$imgIndex}.jpg";
if (file_exists($candidateWebp)) {
    $imgPath = "../../public/Photos/coach{$imgIndex}.webp";
} elseif (file_exists($candidateJpg)) {
    $imgPath = "../../public/Photos/coach{$imgIndex}.jpg";
} else {
    $imgPath = "../../public/Photos/Coach1.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> - Coach Profile</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/coach-finder.css">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container coach-profile-wrapper">
        <div class="profile-card">
            <div class="profile-card-header">
                <?php
                    $names = preg_split('/\s+/', $name);
                    $initials = strtoupper(substr($names[0],0,1) . (isset($names[1])?substr($names[1],0,1):''));
                    $bgIndex = ($id % 6) + 1;
                    $avatarClass = 'avatar-bg-' . $bgIndex;
                ?>
                <div class="profile-avatar initials <?php echo $avatarClass; ?>">
                    <span class="avatar-initials-large"><?php echo $initials; ?></span>
                </div>

                <div class="profile-card-title">
                    <h1 class="profile-name"><?php echo $name; ?></h1>
                </div>
            </div>

            <div class="profile-card-body">
                <section class="profile-bio">
                    <h3>About</h3>
                    <p><?php echo $bio ?: 'This coach has not added a bio yet.'; ?></p>
                </section>

                <section id="contact-info" class="profile-contact">
                    <h3>Contact</h3>
                    <div class="contact-card">
                        <div><strong>Email:</strong> <span class="muted"><?php echo $email ?: 'Not set'; ?></span></div>
                        <div><strong>Phone:</strong> <span class="muted"><?php echo $phone ?: 'Not set'; ?></span></div>
                        <div><strong>Location:</strong> <span class="muted"><?php echo $location ?: 'Not set'; ?></span></div>
                    </div>
                </section>

                <!-- Request Session Section -->
                <section id="request-session" class="profile-request">
                    <h3>Request a Session</h3>

                    <?php if ($requestMessage === 'REQUEST_SENT'): ?>
                        <div class="success-message">Your request has been sent to <?php echo $name; ?>. The coach will contact you via the details you provided.</div>
                    <?php elseif (!empty($requestMessage)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($requestMessage); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="coach-profile.php?id=<?php echo $id; ?>" class="request-form">
                        <input type="hidden" name="request_coach_id" value="<?php echo $id; ?>">

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Message (optional)</label>
                            <textarea name="message" rows="4" placeholder="Preferred times, notes..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn profile-btn-accent">Request Session</button>
                    </form>
                </section>

                <section class="profile-details">
                    <h3>Details</h3>
                    <ul>
                        <li><strong>Hourly Rate:</strong> EGP <?php echo $hourly; ?> /hr</li>
                        <li><strong>Experience:</strong> <?php echo $exp; ?> years</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/partials/footer.php'; ?>