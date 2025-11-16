<?php
require_once __DIR__ . '/../controllers/UserController.php';
$loginError = UserController::login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="form-page">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="main-content-flex" style="padding-top: 80px;">
        <div class="signup-center-container">
            <div class="form-side">
                <div class="form-container">
                    <h2>Sign In</h2>
                    <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            Registration successful! Please sign in with your new account.
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($loginError)): ?>
                        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            <?php echo $loginError; ?>
                        </div>
                    <?php endif; ?>
                    <form class="auth-form signin-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <input type="email" name="email" id="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" id="password" placeholder="Password" required>
                        </div>
                        <div class="form-links">
                            <div class="form-links-left">
                                <label class="remember-me">
                                    <input type="checkbox" id="remember">
                                    <span>Remember me</span>
                                </label>
                            </div>
                            <div class="form-links-right">
                                <a href="#" class="forgot-password">Forgot password?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Access The Court</button>
                        <p class="switch-form">
                            Not on the court yet? <a href="signup.php">Sign up</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<?php include __DIR__ . '/partials/footer.php'; ?>