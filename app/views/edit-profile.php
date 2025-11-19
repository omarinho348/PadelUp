<?php 
require_once __DIR__ . '/../controllers/UserController.php';
[$message, $user, $profile] = UserController::editProfile();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container profile-container">
        <div class="edit-profile-header">
            <h1>Edit Your Profile</h1>
            <a href="profile.php" class="btn back-button">Back to Profile</a>
        </div>
        
        <?php echo $message; ?>
        
        <div class="edit-profile-form">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email (cannot be changed)</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <?php if ($profile): // Only show player details if the user is a player ?>
                <div class="form-section">
                    <h3>Player Details</h3>

                    <div class="form-group two-columns">
                        <div class="form-field">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" required>
                                <option value="male" <?php if($profile['gender'] == 'male') echo 'selected'; ?>>Male</option>
                                <option value="female" <?php if($profile['gender'] == 'female') echo 'selected'; ?>>Female</option>
                                <option value="other" <?php if($profile['gender'] == 'other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($profile['birth_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="side">Preferred Side</label>
                        <select id="side" name="side" required>
                            <option value="right" <?php if($profile['preferred_side'] == 'right') echo 'selected'; ?>>Right</option>
                            <option value="left" <?php if($profile['preferred_side'] == 'left') echo 'selected'; ?>>Left</option>
                        </select>
                    </div>
                    
                </div>
                <?php endif; ?>
                <div class="form-buttons">
                    <button type="submit" class="btn profile-btn-accent">Save Changes</button>
                    <a href="profile.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    
</body>
</html>