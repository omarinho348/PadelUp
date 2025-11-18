<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose a Venue</title>
    <link rel="stylesheet" href="/PadelUp/public/styling/styles.css">
    <link rel="stylesheet" href="/PadelUp/public/styling/venues.css">
</head>
<body>
  <?php 
  require_once __DIR__ . '/../controllers/VenuesController.php';
  $venues = VenuesController::getAllVenues();
  include __DIR__ . '/partials/navbar.php'; 
  ?>
<div class="container">
  <div class="container">
  <header class="hero">
    <div class="hero-bg" role="img" aria-label="Padel courts">
      <!-- decorative background image set via CSS fallback to Assets/Photos/V1.png -->
    </div>
    <div class="hero-content">
      <h1>Find a court near you</h1>
      <p class="hero-sub">Choose a venue, pick a time and book your court.</p>
      <div class="hero-search">
        <input id="search" placeholder="Search by area, or address" aria-label="Search venues">
        <button id="searchBtn">Search</button>
      </div>
    </div>
  </header>
  </div>
</div>

  <main class="venues-grid">
    <?php if ($venues && count($venues)): ?>
      <?php foreach ($venues as $venue): 
        // Handle logo path - database stores as "public/Photos/VenueLogos/filename.png"
        $logoPath = $venue['logo_path'] ?? 'public/Photos/VenueLogos/default.jpg';
        
        // Convert to web-accessible path
        if (!str_starts_with($logoPath, 'http')) {
          // Remove 'public/' prefix if present since web root might be /public or /PadelUp
          $logoPath = str_replace('public/', '', $logoPath);
          $logoPath = '/PadelUp/public/' . ltrim($logoPath, '/');
        }
        
        // Format times in 12-hour with AM/PM
        $openingRaw = $venue['opening_time'] ?? null;
        $closingRaw = $venue['closing_time'] ?? null;
        $openingTime = $openingRaw ? date('g:i A', strtotime($openingRaw)) : '—';
        $closingTime = $closingRaw ? date('g:i A', strtotime($closingRaw)) : '—';
      ?>
        <div class="venue-card">
          <img src="<?php echo htmlspecialchars($logoPath); ?>" 
               alt="<?php echo htmlspecialchars($venue['name']); ?>" 
               loading="lazy"
               onerror="this.onerror=null; this.src='/PadelUp/public/Photos/VenueLogos/default.jpg';">
          <div class="venue-info">
            <h2 class="venue-name"><?php echo htmlspecialchars($venue['name']); ?></h2>
            <p class="venue-address"><?php echo htmlspecialchars($venue['address']); ?>, <?php echo htmlspecialchars($venue['city']); ?></p>
            <div class="venue-meta">
              <div class="meta-item">
                <span class="meta-label">Hours</span>
                <span class="meta-value"><?php echo htmlspecialchars($openingTime); ?> - <?php echo htmlspecialchars($closingTime); ?></span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Rate</span>
                <span class="meta-value">$<?php echo htmlspecialchars($venue['hourly_rate']); ?>/hr</span>
              </div>
            </div>
            <a href="court-reservation.php?venue_id=<?php echo urlencode($venue['venue_id']); ?>" class="venue-book-btn">Book Court</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-results">No venues found.</p>
    <?php endif; ?>
  </main>

  <!-- Removed JS population -->
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
