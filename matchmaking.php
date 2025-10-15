<?php include 'Includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padel Matchmaking - PadelUp</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <!-- Main Styles -->
    <link rel="stylesheet" href="styling/styles.css">
    <!-- Page-specific Styles -->
    <link rel="stylesheet" href="styling/matchmaking.css">
</head>
<body>
    <main class="matchmaking-container">
        <!-- Hero Section with Filters -->
        <section class="match-finder-hero" style="background-image: url('Assets/Photos/tapia_coello.jpg');">
            <h1 class="hero-title">Find Your Perfect Padel Match</h1>
            <div class="filter-bar">
                <div class="filter-item">
                    <i data-feather="map-pin"></i>
                    <input type="text" placeholder="City or Club...">
                </div>
                <div class="filter-item">
                    <i data-feather="calendar"></i>
                    <button class="when-filter-btn">Date</button>
                    <!-- Custom Date/Time Popover -->
                    <div class="when-popover">
                        <div class="day-selector">
                            <button class="day-btn active">Today</button>
                            <button class="day-btn">Tom</button>
                            <button class="day-btn">Mon</button>
                            <button class="day-btn">Tue</button>
                            <button class="day-btn">Wed</button>
                            <button class="day-btn">Thu</button>
                            <button class="day-btn">Fri</button>
                        </div>
                        <div class="time-range-selector">
                            <label>Time Range</label>
                            <div class="time-range-slider">
                                <div class="time-range-track"></div>
                            </div>
                            <div class="time-inputs">
                                <div class="time-input-group">
                                    <span class="time-label">Start</span>
                                    <span class="time-value" id="start-time">8:00 AM</span>
                                    <div class="time-stepper">
                                        <button class="stepper-btn" aria-label="Increase start time"><i data-feather="chevron-up"></i></button>
                                        <button class="stepper-btn" aria-label="Decrease start time"><i data-feather="chevron-down"></i></button>
                                    </div>
                                </div>
                                <div class="time-input-group">
                                    <span class="time-label">End</span>
                                    <span class="time-value" id="end-time">10:00 PM</span>
                                    <div class="time-stepper">
                                        <button class="stepper-btn" aria-label="Increase end time"><i data-feather="chevron-up"></i></button>
                                        <button class="stepper-btn" aria-label="Decrease end time"><i data-feather="chevron-down"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn-primary done-btn">Done</button>
                    </div>
                </div>
                <div class="filter-item">
                    <i data-feather="bar-chart-2"></i>
                    <select>
                        <option>Level</option>
                        <option value="beginner">Beginner (1.0 - 2.5)</option>
                        <option value="intermediate">Intermediate (3.0 - 4.5)</option>
                        <option value="advanced">Advanced (5.0 - 5.5)</option>
                        <option value="expert">Expert / Pro (6.0+)</option>
                    </select>
                </div>
                <div class="filter-item">
                    <i data-feather="users"></i>
                    <select>
                        <option> Game Type</option>
                        <option>Singles</option>
                        <option>Doubles</option>
                        <option>Mixed Doubles</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn-primary search-btn">Search</button>
                    <button class="btn create-match-header-btn">Create Match</button>
                </div>
            </div>
        </section>

        <!-- Match Feed / Lobby -->
        <section class="match-lobby">
            <div class="match-grid"> 
                <!-- Sample Match Card 1 (Full) -->
                <div class="match-card match-card--private">
                    <div class="card-header">
                        <span>Today, 7:00 PM</span>
                        <span class="tag private-tag">Private</span>
                    </div>
                    <div class="card-body">
                        <div class="location-info">
                            <i data-feather="map-pin"></i>
                            <div>
                                <strong>Padel Club Barcelona</strong>
                                <p>Carrer de la Pista, 123</p>
                            </div>
                        </div>
                        <div class="duration-info">
                            <i data-feather="clock"></i>
                            <span>90 min</span>
                        </div>
                        <div class="skill-level">
                            <div class="skill-bar intermediate"></div>
                            <span>Intermediate</span>
                        </div>
                        <div class="player-slots">
                            <div class="avatar-stack">
                                <img src="https://i.pravatar.cc/40?img=1" alt="Player 1">
                                <img src="https://i.pravatar.cc/40?img=2" alt="Player 2">
                                <img src="https://i.pravatar.cc/40?img=3" alt="Player 3">
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                            </div>
                            <span class="slot-count">3/4 Players</span>
                        </div>
                        <div class="host-info">
                            Hosted by <a href="#" class="host-username">AlexMartinez</a>
                            <div class="rating">★ 4.9</div>
                        </div>
                    </div>
                    <button class="btn-primary join-btn">Request to Join</button>
                </div>

                <!-- Sample Match Card 2 (with open slots) -->
                <div class="match-card match-card--public">
                    <div class="card-header">
                        <span>Tomorrow, 6:30 PM</span>
                        <span class="tag public-tag">Public</span>
                    </div>
                    <div class="card-body">
                        <div class="location-info">
                            <i data-feather="map-pin"></i>
                            <div>
                                <strong>Madrid Central Padel</strong>
                                <p>Plaza del Deporte, 5</p>
                            </div>
                        </div>
                        <div class="duration-info">
                            <i data-feather="clock"></i>
                            <span>120 min</span>
                        </div>
                        <div class="skill-level">
                            <div class="skill-bar beginner"></div>
                            <span>Beginner</span>
                        </div>
                        <div class="player-slots">
                            <div class="avatar-stack">
                                <img src="https://i.pravatar.cc/40?img=5" alt="Player 1">
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                            </div>
                            <span class="slot-count">1/4 Players</span>
                        </div>
                        <div class="host-info">
                            Hosted by <a href="#" class="host-username">SofiaG</a>
                            <div class="rating">★ 4.7</div>
                        </div>
                    </div>
                    <button class="btn-primary join-btn">Join Match</button>
                </div>

                <!-- Sample Match Card 3 -->
                <div class="match-card match-card--public">
                    <div class="card-header">
                        <span>Friday, 8:00 PM</span>
                        <span class="tag public-tag">Public</span>
                    </div>
                    <div class="card-body">
                        <div class="location-info">
                            <i data-feather="map-pin"></i>
                            <div>
                                <strong>Valencia Padel Center</strong>
                                <p>Avinguda de la Pista, 45</p>
                            </div>
                        </div>
                        <div class="duration-info">
                            <i data-feather="clock"></i>
                            <span>90 min</span>
                        </div>
                        <div class="skill-level">
                            <div class="skill-bar advanced"></div>
                            <span>Advanced</span>
                        </div>
                        <div class="player-slots">
                            <div class="avatar-stack">
                                <img src="https://i.pravatar.cc/40?img=11" alt="Player 1">
                                <img src="https://i.pravatar.cc/40?img=12" alt="Player 2">
                                <img src="https://i.pravatar.cc/40?img=14" alt="Player 3">
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                            </div>
                            <span class="slot-count">3/4 Players</span>
                        </div>
                        <div class="host-info">
                            Hosted by <a href="#" class="host-username">JuanVLC</a>
                            <div class="rating">★ 5.0</div>
                        </div>
                    </div>
                    <button class="btn-primary join-btn">Join Match</button>
                </div>

                <!-- Sample Match Card 4 -->
                <div class="match-card match-card--private">
                    <div class="card-header">
                        <span>Saturday, 11:00 AM</span>
                        <span class="tag private-tag">Private</span>
                    </div>
                    <div class="card-body">
                        <div class="location-info">
                            <i data-feather="map-pin"></i>
                            <div>
                                <strong>Seville Padel Club</strong>
                                <p>Calle Sol, 88</p>
                            </div>
                        </div>
                        <div class="duration-info">
                            <i data-feather="clock"></i>
                            <span>60 min</span>
                        </div>
                        <div class="skill-level">
                            <div class="skill-bar intermediate"></div>
                            <span>Intermediate</span>
                        </div>
                        <div class="player-slots">
                            <div class="avatar-stack">
                                <img src="https://i.pravatar.cc/40?img=21" alt="Player 1">
                                <img src="https://i.pravatar.cc/40?img=22" alt="Player 2">
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                                <div class="avatar-placeholder"><i data-feather="plus"></i></div>
                            </div>
                            <span class="slot-count">2/4 Players</span>
                        </div>
                        <div class="host-info">
                            Hosted by <a href="#" class="host-username">MariaS</a>
                            <div class="rating">★ 4.8</div>
                        </div>
                    </div>
                    <button class="btn-primary join-btn">Request to Join</button>
                </div>

                <!-- Sample Match Card 5 (Full) -->
                <div class="match-card match-card--full">
                    <div class="card-header">
                        <span>Today, 9:00 PM</span>
                        <span class="tag public-tag">Public</span>
                    </div>
                    <div class="card-body">
                        <div class="location-info">
                            <i data-feather="map-pin"></i>
                            <div>
                                <strong>PadelUp Club Madrid</strong>
                                <p>Avenida de Padel, 101</p>
                            </div>
                        </div>
                        <div class="duration-info">
                            <i data-feather="clock"></i>
                            <span>90 min</span>
                        </div>
                        <div class="skill-level">
                            <div class="skill-bar intermediate"></div>
                            <span>Intermediate</span>
                        </div>
                        <div class="player-slots">
                            <div class="avatar-stack">
                                <img src="https://i.pravatar.cc/40?img=31" alt="Player 1">
                                <img src="https://i.pravatar.cc/40?img=32" alt="Player 2">
                                <img src="https://i.pravatar.cc/40?img=33" alt="Player 3">
                                <img src="https://i.pravatar.cc/40?img=34" alt="Player 4">
                            </div>
                            <span class="slot-count">4/4 Players</span>
                        </div>
                        <div class="host-info">
                            Hosted by <a href="#" class="host-username">CarlosR</a>
                            <div class="rating">★ 4.8</div>
                        </div>
                    </div>
                    <button class="btn-primary join-btn">Match Full</button>
                </div>

            </div>
        </section>
    </main>

    <!-- User Profile Popover (Hidden by default) -->
    <div id="user-popover" class="user-popover" style="display: none; position: absolute;">
        <div class="popover-header">
            <img src="https://i.pravatar.cc/60?img=1" alt="User Avatar" class="popover-avatar">
            <div>
                <strong class="popover-name">Alex Martinez</strong>
                <span class="popover-skill">Advanced (4.5)</span>
            </div>
        </div>
        <p class="popover-bio">Loves aggressive net play and fast-paced games. Looking for competitive matches.</p>
        <div class="popover-stats">★ 4.9 | 88 Matches</div>
    </div>

    <!-- Create Match Modal (Hidden by default) -->
    <div id="create-match-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close-btn"><i data-feather="x"></i></button>
            <h2>Create a New Match</h2>
            <form class="create-match-form">
                <!-- Form fields similar to the filter bar -->
                <div class="form-group">
                    <label for="match-location">Location</label>
                    <input type="text" id="match-location" placeholder="Padel Club Name">
                </div>
                <div class="form-group">
                    <label for="match-datetime">Date & Time</label>
                    <input type="datetime-local" id="match-datetime">
                </div>
                <div class="form-group">
                    <label for="match-duration">Duration</label>
                    <select id="match-duration">
                        <option>60 min</option>
                        <option selected>90 min</option>
                        <option>120 min</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="match-skill">Skill Level</label>
                    <select id="match-skill">
                        <option value="beginner">Beginner (1.0 - 2.5)</option>
                        <option value="intermediate">Intermediate (3.0 - 4.5)</option>
                        <option value="advanced">Advanced (5.0 - 5.5)</option>
                        <option value="expert">Expert / Pro (6.0+)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Players Needed</label>
                    <div class="player-count-selector">
                        <button type="button" class="player-count-btn">1</button>
                        <button type="button" class="player-count-btn">2</button>
                        <button type="button" class="player-count-btn active">3</button>
                        <button type="button" class="player-count-btn">4</button>
                        <button type="button" class="player-count-btn">5</button>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Create Match</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="minimal-footer">
        <div class="footer-content">
            <div class="footer-brand">PadelUp</div>
            <div class="footer-links">
                <a href="#">About-Us</a>
            </div>
        </div>
    </footer>

    <script>
        feather.replace(); // Initialize Feather Icons

        // --- JAVASCRIPT FOR INTERACTIVE FILTERS ---
        const whenFilterBtn = document.querySelector('.when-filter-btn');
        const whenPopover = document.querySelector('.when-popover');
        const dayButtons = document.querySelectorAll('.day-btn');
        const doneBtn = document.querySelector('.done-btn');
        const startTimeValue = document.getElementById('start-time');
        const endTimeValue = document.getElementById('end-time');
        const increaseStartTimeBtn = document.querySelector('[aria-label="Increase start time"]');
        const decreaseStartTimeBtn = document.querySelector('[aria-label="Decrease start time"]');
        const increaseEndTimeBtn = document.querySelector('[aria-label="Increase end time"]');
        const decreaseEndTimeBtn = document.querySelector('[aria-label="Decrease end time"]');
        let selectedDay = 'Today'; // Default value

        // --- Modal Logic ---
        const createMatchModal = document.getElementById('create-match-modal');
        const createMatchBtn = document.querySelector('.create-match-header-btn');
        const closeModalBtn = createMatchModal.querySelector('.modal-close-btn');


        // Toggle the popover's visibility when the "Anytime" button is clicked
        whenFilterBtn.addEventListener('click', (e) => {
          e.stopPropagation(); // Prevents the window click event from firing immediately
          whenPopover.classList.toggle('visible');
        });

        // Close the popover when clicking anywhere else on the page
        const closePopover = () => {
          if (whenPopover.classList.contains('visible')) {
            whenPopover.classList.remove('visible');
          }
        };
        window.addEventListener('click', closePopover);
        whenPopover.addEventListener('click', (e) => e.stopPropagation()); // Prevent clicks inside the popover from closing it

        // Handle active state for day selector buttons
        dayButtons.forEach(button => {
            button.addEventListener('click', () => {
                dayButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                selectedDay = button.textContent; // Store selected day
            });
        });

        // --- Time Stepper Logic ---
        const timeStep = 30; // 30 minutes

        const parseTime = (timeString) => {
            const [time, period] = timeString.split(' ');
            let [hours, minutes] = time.split(':').map(Number);
            if (period === 'PM' && hours !== 12) hours += 12;
            if (period === 'AM' && hours === 12) hours = 0; // Midnight case
            return { hours, minutes };
        };

        const formatTime = (date) => {
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const period = hours >= 12 ? 'PM' : 'AM';
            let displayHours = hours % 12;
            if (displayHours === 0) displayHours = 12;
            const displayMinutes = String(minutes).padStart(2, '0');
            return `${displayHours}:${displayMinutes} ${period}`;
        };

        const adjustTime = (timeElement, minuteChange) => {
            const { hours, minutes } = parseTime(timeElement.textContent);
            const date = new Date();
            date.setHours(hours, minutes, 0, 0);
            date.setMinutes(date.getMinutes() + minuteChange);
            timeElement.textContent = formatTime(date);
        };

        increaseStartTimeBtn.addEventListener('click', () => adjustTime(startTimeValue, timeStep));
        decreaseStartTimeBtn.addEventListener('click', () => adjustTime(startTimeValue, -timeStep));
        increaseEndTimeBtn.addEventListener('click', () => adjustTime(endTimeValue, timeStep));
        decreaseEndTimeBtn.addEventListener('click', () => adjustTime(endTimeValue, -timeStep));


        // Handle "Done" button click
        doneBtn.addEventListener('click', () => {
            const startTime = document.getElementById('start-time').textContent;
            const endTime = document.getElementById('end-time').textContent;

            // Update the main button text
            whenFilterBtn.textContent = `${selectedDay}, ${startTime} - ${endTime}`;
            
            closePopover(); // Close the popover
        });

        // --- "Create Match" Modal Functionality ---
        createMatchBtn.addEventListener('click', () => {
            createMatchModal.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            createMatchModal.style.display = 'none';
        });

        // Close modal if user clicks on the overlay
        createMatchModal.addEventListener('click', (e) => {
            if (e.target === createMatchModal) {
                createMatchModal.style.display = 'none';
            }
        });

        // Handle active state for player count buttons in modal
        const playerCountBtns = document.querySelectorAll('.player-count-btn');
        playerCountBtns.forEach(button => {
            button.addEventListener('click', () => {
                playerCountBtns.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    </script>
</body>
</html>