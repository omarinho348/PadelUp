<?php
require_once __DIR__ . '/../controllers/UserController.php';
$error = UserController::register();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="form-page">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="main-content-flex" style="padding-top: 80px;">
        <div class="signup-center-container">
            <div class="form-side">
                <div class="form-container">
                    <h2>Create Your Player Profile</h2>
                    <?php if (!empty($error)): ?>
                        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <form class="auth-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <input type="text" name="fullname" id="fullname" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" id="email" placeholder="Email@example.com" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" id="phone" placeholder="Phone Number (Optional)">
                        </div>
                        <div class="form-group two-columns">
                            <div class="form-field">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="dob">Date of Birth</label>
                                <input type="date" name="dob" id="dob" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="hand">Preferred Hand</label>
                            <select name="hand" id="hand" required>
                                <option value="">Select Hand</option>
                                <option value="right">Right</option>
                                <option value="left">Left</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience">Padel Level</label>
                            <div class="skill-selector">
                                <label class="skill-card">
                                    <input type="radio" name="skill" value="beginner" checked>
                                    <span class="skill-card-content">
                                        <span class="skill-icon">🎓</span>
                                        <strong>Beginner</strong>
                                        <small>Just getting started</small>
                                    </span>
                                </label>
                                <label class="skill-card">
                                    <input type="radio" name="skill" value="intermediate">
                                    <span class="skill-card-content">
                                        <span class="skill-icon">🎾</span>
                                        <strong>Intermediate</strong>
                                        <small>Play regularly</small>
                                    </span>
                                </label>
                                <label class="skill-card">
                                    <input type="radio" name="skill" value="advanced">
                                    <span class="skill-card-content">
                                        <span class="skill-icon">🏆</span>
                                        <strong>Advanced</strong>
                                        <small>Live for the game</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" id="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn-primary">Join The Game</button>
                        <p class="switch-form">
                            Already have an account? <a href="signin.php">Sign in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<?php include __DIR__ . '/partials/footer.php'; ?>