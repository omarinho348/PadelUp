<?php include 'Includes/navbar.php'; ?>

<?php

// Include database connection
include_once "includes/dbh.inc.php";



// Process the account deletion request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if confirmation is provided
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Get user ID from session
        $userId = $_SESSION['ID'];
        
        // Prepare SQL to delete the user
        $sql = "DELETE FROM users WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            // Close the statement and connection
            $stmt->close();
            $conn->close();
            
            // Clear all session variables
            $_SESSION = array();
            
            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 42000, '/');
            }
            
            // Destroy the session
            session_destroy();
            
            // Redirect to the homepage with a message
            header("Location: index.php?account=deleted");
            exit();
        } else {
            $error = "An error occurred while deleting your account. Please try again.";
        }
    } else {
        $error = "You must confirm deletion by checking the confirmation box.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <div class="container profile-container">
        <div class="edit-profile-header">
            <h1>Delete Your Account</h1>
            <a href="profile.php" class="btn back-button">Back to Profile</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="edit-profile-form delete-account-form">
            <div class="warning-box">
                <h2>⚠️ Warning: This action cannot be undone</h2>
                <p>Deleting your account will permanently remove all your data from our system including:</p>
                <ul>
                    <li>Your profile information</li>
                    <li>Your booking history</li>
                    <li>Your match history and player connections</li>
                </ul>
                <p>You will not be able to recover this information later.</p>
            </div>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="delete-confirmation-form">
                <div class="confirmation-check">
                    <label>
                        <input type="checkbox" name="confirm_delete" value="yes" required>
                        I understand that this action cannot be reversed and I want to permanently delete my account
                    </label>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-danger">Permanently Delete My Account</button>
                    <a href="profile.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php include 'Includes/footer.php'; ?>