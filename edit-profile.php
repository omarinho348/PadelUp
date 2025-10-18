<?php 
include 'Includes/navbar.php';
include_once "includes/dbh.inc.php";

// Redirect to login page if not logged in
if (!isset($_SESSION['ID']) || !isset($_SESSION['FullName'])) {
    header("Location: signin.php");
    exit();
}

// Initialize variable to store messages
$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $fullName = htmlspecialchars($_POST['fullname']);
    $height = htmlspecialchars($_POST['height']);
    $hand = htmlspecialchars($_POST['hand']);
    $position = htmlspecialchars($_POST['position']);
    $skill = htmlspecialchars($_POST['skill']);
    $location = htmlspecialchars($_POST['location']);
    
    // Prepare SQL statement
    $sql = "UPDATE users SET 
            FullName = ?,
            Height = ?,
            DominantHand = ?,
            PreferredPosition = ?,
            SkillLevel = ?,
            Location = ?
            WHERE ID = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $fullName, $height, $hand, $position, $skill, $location, $_SESSION['ID']);
    
    // Execute query
    if ($stmt->execute()) {
        // Update session variables with new values
        $_SESSION['FullName'] = $fullName;
        $_SESSION['Height'] = $height;
        $_SESSION['DominantHand'] = $hand;
        $_SESSION['PreferredPosition'] = $position;
        $_SESSION['SkillLevel'] = $skill;
        $_SESSION['Location'] = $location;
        // is_admin is not editable here, but we should keep it in the session if it exists.
        
        $message = '<div class="success-message">Profile updated successfully!</div>';
    } else {
        $message = '<div class="error-message">Error updating profile: ' . $stmt->error . '</div>';
    }
    
    $stmt->close();
}

// Fetch current user data
$userId = $_SESSION['ID'];
$sql = "SELECT * FROM users WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$_SESSION['is_admin'] = $user['is_admin']; // Ensure session is up-to-date
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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