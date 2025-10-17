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
  
        <?php if (isset($_SESSION['ID']) && isset($_SESSION['FullName'])): ?>
            <!-- ✅ If logged in: show welcome message, profile, and logout -->
            <!-- Home button (icon only) -->
            <a href="index.php" class="btn btn-icon <?php if ($current_page == 'index.php') echo 'nav-active'; ?>" title="Home" aria-label="Home">
                <!-- Home SVG icon (inline) -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5z"></path>
                </svg>
            </a>
            
            <!-- Welcome message with username -->
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['FullName']); ?></span>
            
            <a href="profile.php" class="btn <?php if ($current_page == 'profile.php') echo 'nav-active'; ?>">My Profile</a>
        <?php else: ?>
            <!-- 🚪 If not logged in: show sign-in + sign-up -->
            <!-- Home button for guests (icon only) -->
            <a href="index.php" class="btn btn-icon <?php if ($current_page == 'index.php') echo 'nav-active'; ?>" title="Home" aria-label="Home">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5z"></path>
                </svg>
            </a>
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
