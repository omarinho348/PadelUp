<?php include 'Includes/navbar.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
 

    <div class="main-content-flex" style="padding-top: 80px;">
        <div class="signup-tagline">Find your next match. Level up your game.</div>
        <div class="overlay"></div>
        <div class="signup-center-container">
            <div class="form-side">
                <div class="form-container">
                    <h2>Create Account</h2>
                    <form class="auth-form">
                        <div class="form-group">
                            <input type="text" id="fullname" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" placeholder="Email Address" required>
                        </div>
                        <div class="form-group two-columns">
                            <div class="form-field">
                                <label for="gender">Gender</label>
                                <select id="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="dob">Date of Birth</label>
                                <input type="date" id="dob" required>
                            </div>
                        </div>
                        <div class="form-group two-columns">
                            <div class="form-field">
                                <label for="height">Height (cm)</label>
                                <input type="number" id="height" placeholder="Height in cm" min="100" max="250" required>
                            </div>
                            <div class="form-field">
                                <label for="hand">Dominant Hand</label>
                                <select id="hand" required>
                                    <option value="">Select Hand</option>
                                    <option value="right">Right</option>
                                    <option value="left">Left</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="position">Preferred Position</label>
                            <select id="position" required>
                                <option value="">Select Position</option>
                                <option value="rightside">Right Side</option>
                                <option value="leftside">Left Side</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience">Padel Level</label>
                            <select id="experience" name="experience" required class="level-select">
                            <option value="">Select Your Padel Level</option>
                            <option value="1.0">🎾 1.0 – Absolute Beginner (Low D)</option>
                            <option value="2.0">🎾 2.0 – Beginner (D)</option>
                            <option value="3.0">🎾 3.0 – Low Intermediate (High D / Low C)</option>
                            <option value="4.0">🥈 4.0 – Intermediate (C)</option>
                            <option value="4.5">🥈 4.5 – Upper Intermediate (High C)</option>
                            <option value="5.0">🥇 5.0 – Advanced (B)</option>
                            <option value="5.5">🥇 5.5 – High Advanced (High B)</option>
                            <option value="6.0">🏆 6.0 – Expert (A)</option>
                            <option value="7.0">🏆 7.0 – Professional / Elite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" placeholder="City, Country" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="confirm-password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn-primary">Create Account</button>
                        <p class="switch-form">
                            Already have an account? <a href="signin.html">Sign in</a>
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