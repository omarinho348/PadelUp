<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Court - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/reservation.css">
</head>
<body>
  <?php 
    require_once __DIR__ . '/../controllers/VenuesController.php';
    $venueId = isset($_GET['venue_id']) ? (int)$_GET['venue_id'] : 0;
    $venue = $venueId ? VenuesController::getVenue($venueId) : null;
    // Prepare safe defaults
    $vName = $venue['name'] ?? 'Select a court';
    $vAddress = isset($venue['address'],$venue['city']) ? ($venue['address'] . ', ' . $venue['city']) : 'Choose a date and time';
    $open = $venue['opening_time'] ?? '09:00:00';
    $close = $venue['closing_time'] ?? '22:00:00';
    $logo = $venue['logo_path'] ?? 'public/Photos/VenueLogos/default.jpg';
    if (strpos($logo, 'http') !== 0) {
      $logo = ltrim($logo, '/');
      // Build web path assuming app served at /PadelUp
      $logo = '/PadelUp/public/' . str_replace('public/', '', $logo);
    }
    include __DIR__ . '/partials/navbar.php'; 
  ?>
  <div class="container">
    <div class="reservation-content">
      <div class="top">
        <button id="back" aria-label="Go back to venues">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
        </button>
        <div>
          <h1 id="venueName"><?php echo htmlspecialchars($vName); ?></h1>
          <p id="venueAddress" class="sub"><?php echo htmlspecialchars($vAddress); ?></p>
        </div>
        <img id="venueImg" src="<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($vName); ?>" class="venue-image">
      </div>
  
      <div id="dateStrip" class="date-strip">
        <!-- JS populates dates -->
      </div>
  
      <div class="booking">
        <div id="courtsHeader" class="courts-header"></div>
        <div id="slotsGrid" class="slots-grid">
          <!-- JS populates slots -->
        </div>
      </div>
  
      <div class="summary">
        <div id="selectionInfo">No selection</div>
        <button id="confirm" disabled>Confirm Booking</button>
      </div>
  
      <!-- Confirmation Modal -->
      <div id="confirmModal" class="modal" aria-hidden="true">
        <div class="modal-content">
          <svg class="modal-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <p id="modalText">Your booking is confirmed!</p>
          <div class="modal-actions">
            <button id="modalClose">Done</button>
          </div>
        </div>
      </div>
    </div>

  </div>
  <script>
    window.venueConfig = {
      id: <?php echo (int)$venueId; ?>,
      opening: "<?php echo htmlspecialchars($open); ?>",
      closing: "<?php echo htmlspecialchars($close); ?>"
    };
  </script>
  <script src="../../public/scripts/reservation.js"></script>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>