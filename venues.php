<?php include 'Includes/navbar.php'; ?>



<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Choose a Venue</title>
  <link rel="stylesheet" href="styling/venues.css">

</head>

<body>
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

  <main id="venuesList" class="venues-grid">
    <!-- JS populates venue cards -->
  </main>

  <script src="Assets/scripts/venues.js"></script>
</body>
</html>
