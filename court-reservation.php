<?php include 'Includes/navbar.php'; ?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Court Reservation</title>
    <link rel="stylesheet" href="styling/reservation.css">
        <link rel="stylesheet" href="styling/styles.css">

  </head>
  <body>
    <header class="top">
      <button id="back" aria-label="back">‹</button>
      <div class="venue" id="venueHeader">
        <img id="venueImg" src="Assets/Photos/V1.png" alt="venue" style="height:44px;width:64px;object-fit:cover;border-radius:8px;margin-right:12px;vertical-align:middle">
        <div>
          <h1 id="venueName">Tolip El Narges</h1>
          <div class="sub" id="venueAddress">New Cairo, Cairo</div>
        </div>
      </div>
    </header>

    <section class="date-strip" role="tablist" aria-label="Dates" id="dateStrip">
      <!-- Dates will be populated by scripts/reservation.js -->
    </section>

    <main class="booking">
      <div class="courts-header">
        <div class="court-title">Court A</div>
        <div class="court-title">Court B</div>
      </div>

      <div class="slots-grid" id="slotsGrid">
        <!-- JS will populate time slots for two courts -->
      </div>

      <div class="summary" id="summary">
        <div id="selectionInfo">No selection</div>
        <button id="confirm" disabled>Confirm</button>
      </div>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal" aria-hidden="true">
      <div class="modal-content">
        <h3>Booking confirmed</h3>
        <p id="modalText">Your court has been reserved. Cancellations must be made at least 4 hours before the booking time.</p>
        <div class="modal-actions">
          <button id="modalClose">Close</button>
        </div>
      </div>
    </div>

    <script src="Assets/scripts/reservation.js"></script>
  </body>
</html>
