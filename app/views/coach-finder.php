<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Coach - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/coach-finder.css">
</head>
<body>
    <?php 
    require_once __DIR__ . '/../controllers/UserController.php';
    // Allow an optional search query via GET
    $searchQuery = trim($_GET['search'] ?? '');
    $coaches = UserController::getPublicCoaches();
    include __DIR__ . '/partials/navbar.php'; 
    ?>

    <div class="coach-finder-header">
        <div class="container">
            <div class="main-heading">
                <h1>Find Your PadelUp Coach</h1>
                <p>Get personalized training from our range of certified coaches.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <form method="GET" action="coach-finder.php" style="margin-bottom:18px;display:flex;gap:8px;align-items:center;">
            <input type="search" name="search" placeholder="Search coaches by name..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="flex:1;padding:10px;border-radius:8px;border:1px solid #ddd;" aria-label="Search coaches by name" />
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <div class="coaches-grid">
            <?php if (empty($coaches)): ?>
                <div style="padding:18px;text-align:center;color:#666;">No coaches found. Try a different search.</div>
            <?php else: foreach($coaches as $coach): ?>
                <?php
                    $id = (int)$coach['user_id'];
                    $name = htmlspecialchars($coach['name']);
                    $location = htmlspecialchars($coach['location'] ?? '');
                    $bio = htmlspecialchars($coach['bio'] ?? '');
                    // preview: first 4 words
                    $bioPreview = 'Private Coach';
                    if (!empty($bio)) {
                        $words = preg_split('/\s+/', trim($bio));
                        $bioPreview = implode(' ', array_slice($words, 0, 4));
                    }
                    $hourly = isset($coach['hourly_rate']) ? number_format((float)$coach['hourly_rate'], 2) : '—';
                    $exp = htmlspecialchars($coach['experience_years'] ?? '0');
                    $email = htmlspecialchars($coach['email']);
                    $phone = htmlspecialchars($coach['phone'] ?? '');
                    // Profile image path (if uploaded)
                    $imagePath = $coach['profile_image_path'] ?? '';
                    $imgUrl = '';
                    if (!empty($imagePath)) {
                        if (!str_starts_with($imagePath, 'http')) {
                            $imgUrl = '/PadelUp/' . ltrim($imagePath, '/');
                        } else {
                            $imgUrl = $imagePath;
                        }
                    }
                    // Choose a fallback image from the bundled photos (cycle by id)
                    $imgIndex = ($id % 6) + 1; // 1..6
                    $names = preg_split('/\s+/', $name);
                    $initials = strtoupper(substr($names[0],0,1) . (isset($names[1]) ? substr($names[1],0,1) : ''));
                    $avatarClass = 'avatar-bg-' . $imgIndex;
                ?>
                <div class="coach-card" role="link" tabindex="0" data-href="coach-profile.php?id=<?php echo $id; ?>">
                    <div class="coach-avatar <?php echo empty($imgUrl) ? $avatarClass : ''; ?>">
                        <?php if (!empty($imgUrl)): ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Coach photo of <?php echo $name; ?>" class="coach-avatar-img" />
                        <?php else: ?>
                            <span class="avatar-initials"><?php echo $initials; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="coach-info">
                        <h3 class="coach-name"><a href="coach-profile.php?id=<?php echo $id; ?>" aria-label="View profile for <?php echo $name; ?>"><?php echo $name; ?></a></h3>
                        <p class="coach-location"><?php echo $location ?: 'Location not set'; ?></p>
                        <span class="coach-specialty"><?php echo htmlspecialchars($bioPreview); ?></span>
                        <div style="margin-top:12px;display:flex;gap:8px;justify-content:center;">
                            <a class="btn btn-primary" href="coach-profile.php?id=<?php echo $id; ?>" aria-label="View profile for <?php echo $name; ?>, EGP <?php echo $hourly; ?> per hour">View Profile — EGP <?php echo $hourly; ?> /hr</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <script>
    (function(){
      document.querySelectorAll('.coach-card').forEach(function(card){
        var href = card.dataset.href;
        if(!href) return;
        // Click handler: ignore clicks on links or buttons inside the card
        card.addEventListener('click', function(e){
          if (e.target.closest('a') || e.target.closest('button')) return;
          window.location.href = href;
        });
        // Keyboard support (Enter or Space)
        card.addEventListener('keydown', function(e){
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            window.location.href = href;
          }
        });
      });
    })();
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>