<?php include 'Includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book a Court - PadelUp</title>
  <link rel="stylesheet" href="styling/styles.css">
  <link rel="stylesheet" href="styling/reservation.css">
</head>

<body>
  <div class="container">
    <div class="top">
      <button id="back" aria-label="Go back to venues">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
      </button>
      <div>
        <h1 id="venueName">Select a court</h1>
        <p id="venueAddress" class="sub">Choose a date and time</p>
      </div>
      <img id="venueImg" src="" alt="" class="venue-image">
    </div>

    <div id="dateStrip" class="date-strip">
      <!-- JS populates dates -->
    </div>

    <div class="booking">
      <div class="courts-header">
        <div class="court-title">Court A</div>
        <div class="court-title">Court B</div>
      </div>
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

    <footer class="minimal-footer">
        <div class="footer-content">
            <div class="footer-brand">PadelUp</div>
        </div>
    </footer>
  </div>
  <script src="Assets/scripts/reservation.js"></script>
</body>
</html>