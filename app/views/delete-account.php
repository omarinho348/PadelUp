<?php
require_once __DIR__ . '/../controllers/UserController.php';
$error = UserController::deleteAccount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    
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
<?php include __DIR__ . '/partials/footer.php'; ?>