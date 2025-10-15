<?php
session_start(); // Always start session to check login state
$current_page = basename($_SERVER['PHP_SELF']);
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
        <a href="matchmaking.php" class="btn <?php if ($current_page == 'matchmaking.php') echo 'nav-active'; ?>">Matchmaking</a>
        <a href="venues.php" class="btn <?php if ($current_page == 'venues.php' || $current_page == 'reservation.php') echo 'nav-active'; ?>">Court Booking</a>
        <a href="marketplace.php" class="btn <?php if ($current_page == 'marketplace.php') echo 'nav-active'; ?>">Marketplace</a>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- ✅ If logged in: show profile + logout -->
            <a href="profile.php" class="btn <?php if ($current_page == 'profile.php') echo 'nav-active'; ?>">My Profile</a>
            <a href="logout.php" class="btn btn-primary">Logout</a>
        <?php else: ?>
            <!-- 🚪 If not logged in: show sign-in + sign-up -->
            <a href="signin.php" class="btn <?php if ($current_page == 'signin.php') echo 'nav-active'; ?>">Sign In</a>
            <?php
                // The 'Sign Up' button gets special styling.
                // It's 'nav-active' if on the signup page, otherwise it's 'btn-nav-primary' to stand out.
                $signup_class = ($current_page == 'signup.php') ? 'nav-active' : 'btn-nav-primary';
            ?>
            <a href="signup.php" class="btn <?php echo $signup_class; ?>">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>




</body>
</html>
