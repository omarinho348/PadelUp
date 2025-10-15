<?php
session_start(); // Always start session to check login state
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styling/styles.css">
</head>
<body>
    
<nav class="nav-bar">
    <a href="index.php" class="nav-brand">PadelUp</a>

    <div class="nav-buttons">
        <a href="marketplace.php" class="btn">Marketplace</a>
        <a href="venues.php" class="btn">Court Booking</a>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- ✅ If logged in: show profile + logout -->
            <a href="profile.php" class="btn">My Profile</a>
            <a href="logout.php" class="btn btn-primary">Logout</a>
        <?php else: ?>
            <!-- 🚪 If not logged in: show sign-in + sign-up -->
            <a href="signin.php" class="btn">Sign In</a>
            <a href="signup.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>




</body>
</html>
