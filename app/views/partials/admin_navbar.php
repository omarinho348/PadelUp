<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="nav-bar">
    <a href="admin.php" class="nav-brand">PadelUp</a>
    <div class="nav-buttons">
        <!-- Home button for admin dashboard -->
        <a href="admin.php" class="btn btn-icon <?php if ($current_page == 'admin.php') echo 'nav-active'; ?>" title="Admin Dashboard" aria-label="Admin Dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5z"></path>
            </svg>
        </a>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</nav>