<?php include 'Includes/navbar.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <div class="main-content-flex" style="padding-top: 80px;">
        <div class="signup-tagline">Welcome back to your padel community</div>
        <div class="overlay"></div>
        <div class="signup-center-container">
            <div class="form-side">
                <div class="form-container">
                    <h2>Sign In</h2>
                    <form class="auth-form signin-form">
                        <div class="form-group">
                            <input type="email" id="email" placeholder="Email Address" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" placeholder="Password" required>
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
                        <button type="submit" class="btn-primary">Sign In</button>
                        <p class="switch-form">
                            Don't have an account? <a href="signup.html">Create one</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer class="minimal-footer">
        <div class="footer-content">
            <div class="footer-brand">PadelUp</div>
            <div class="footer-links">
                <a href="#">About-Us</a>
                
            </div>
        </div>
    </footer>
</body>
</html>