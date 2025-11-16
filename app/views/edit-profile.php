<?php 
require_once __DIR__ . '/../controllers/UserController.php';
[$message, $user] = UserController::editProfile();
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
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['FullName']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email (cannot be changed)</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['Email']); ?>" disabled>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Player Details</h3>
                    
                    <div class="form-group">
                        <label for="height">Height (cm)</label>
                        <input type="number" id="height" name="height" min="100" max="250" value="<?php echo htmlspecialchars($user['Height']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hand">Dominant Hand</label>
                        <select id="hand" name="hand" required>
                            <option value="right" <?php if($user['DominantHand'] == 'right') echo 'selected'; ?>>Right</option>
                            <option value="left" <?php if($user['DominantHand'] == 'left') echo 'selected'; ?>>Left</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Preferred Position</label>
                        <select id="position" name="position" required>
                            <option value="rightside" <?php if($user['PreferredPosition'] == 'rightside') echo 'selected'; ?>>Right Side</option>
                            <option value="leftside" <?php if($user['PreferredPosition'] == 'leftside') echo 'selected'; ?>>Left Side</option>
                            <option value="both" <?php if($user['PreferredPosition'] == 'both') echo 'selected'; ?>>Both</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Padel Level</label>
                        <div class="skill-selector">
                            <label class="skill-card">
                                <input type="radio" name="skill" value="beginner" <?php if($user['SkillLevel'] == 'beginner') echo 'checked'; ?>>
                                <span class="skill-card-content">
                                    <span class="skill-icon">🎓</span>
                                    <strong>Beginner</strong>
                                    <small>Just getting started</small>
                                </span>
                            </label>
                            <label class="skill-card">
                                <input type="radio" name="skill" value="intermediate" <?php if($user['SkillLevel'] == 'intermediate') echo 'checked'; ?>>
                                <span class="skill-card-content">
                                    <span class="skill-icon">🎾</span>
                                    <strong>Intermediate</strong>
                                    <small>Play regularly</small>
                                </span>
                            </label>
                            <label class="skill-card">
                                <input type="radio" name="skill" value="advanced" <?php if($user['SkillLevel'] == 'advanced') echo 'checked'; ?>>
                                <span class="skill-card-content">
                                    <span class="skill-icon">🏆</span>
                                    <strong>Advanced</strong>
                                    <small>Live for the game</small>
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($user['Location']); ?>" required>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn profile-btn-accent">Save Changes</button>
                    <a href="profile.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    
</body>
</html>