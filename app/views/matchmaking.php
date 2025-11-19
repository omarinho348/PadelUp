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
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <!-- Page-specific Styles -->
    <link rel="stylesheet" href="../../public/styling/matchmaking.css">
</head>
<body>
    <?php 
    require_once __DIR__ . '/../controllers/MatchController.php';
    require_once __DIR__ . '/../models/Venue.php'; // For fetching venues for the modal

    // Handle POST requests for creating/joining matches
    $create_error = MatchController::createMatch();
    $join_error = MatchController::joinMatch();
    $leave_error = MatchController::leaveMatch();

    // Fetch all open matches, applying any GET filters
    $matches = MatchController::showMatches();

    // Fetch all venues for the "Create Match" modal dropdown
    $venues = Venue::listAll($GLOBALS['conn']);

    $current_user_id = $_SESSION['user_id'] ?? null;

    include __DIR__ . '/partials/navbar.php'; 
    ?>
    <main class="matchmaking-container">
        <!-- Hero Section with Filters -->
        <section class="match-finder-hero" style="background-image: url('../../public/Photos/tapia_coello.jpg');">
            <h1 class="hero-title">Find Your Perfect Padel Match</h1>
            <form class="filter-bar" method="GET" action="matchmaking.php">
                <div class="filter-item">
                    <i data-feather="map-pin"></i>
                    <select name="venue_id">
                        <option value="">Any Venue</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?php echo $venue['venue_id']; ?>" <?php if (isset($_GET['venue_id']) && $_GET['venue_id'] == $venue['venue_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($venue['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <i data-feather="calendar"></i>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
                </div>
                <div class="filter-item">
                    <i data-feather="bar-chart-2"></i>
                    <select name="min_skill">
                        <option value="">Min Skill</option>
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if (isset($_GET['min_skill']) && $_GET['min_skill'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <i data-feather="bar-chart-2"></i>
                    <select name="max_skill">
                        <option value="">Max Skill</option>
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if (isset($_GET['max_skill']) && $_GET['max_skill'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary search-btn">Search</button>
                    <button type="button" class="btn create-match-header-btn">Create Match</button>
                </div>
            </form>
        </section>

        <!-- Match Feed / Lobby -->
        <section class="match-lobby">
            <?php if (!empty($join_error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($join_error); ?></div>
            <?php endif; ?>
            <?php if (!empty($create_error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($create_error); ?></div>
            <?php endif; ?>
            <?php if (!empty($leave_error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($leave_error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'created'): ?>
                <div class="alert alert-success">Match created successfully!</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'joined'): ?>
                <div class="alert alert-success">You have successfully joined the match!</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'left'): ?>
                <div class="alert alert-success">You have left the match.</div>
            <?php endif; ?>

            <div class="match-grid"> 
                <?php if (empty($matches)): ?>
                    <div class="empty-state">
                        <h3>No Open Matches Found</h3>
                        <p>Try adjusting your filters or be the first to create a new match!</p>
                        <button class="btn btn-primary create-match-header-btn">Create a Match</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($matches as $match): ?>
                        <article class="match-card">
                            <div class="match-card-content">
                                <div class="match-card-header">
                                    <div class="match-info-item">
                                        <i data-feather="map-pin"></i>
                                        <span><?php echo htmlspecialchars($match['venue_name']); ?></span>
                                    </div>
                                    <div class="match-info-item">
                                        <i data-feather="calendar"></i>
                                        <span><?php echo date("D, M j", strtotime($match['match_date'])); ?> at <?php echo date("g:i A", strtotime($match['match_time'])); ?></span>
                                    </div>
                                </div>
                                <div class="match-card-body">
                                    <div class="match-skill-level">
                                        <div class="skill-level-label">Skill Level</div>
                                        <div class="skill-level-value"><?php echo htmlspecialchars($match['min_skill_level']); ?> - <?php echo htmlspecialchars($match['max_skill_level']); ?></div>
                                    </div>
                                    <div class="match-players">
                                        <div class="player-avatars">
                                            <?php for($i = 0; $i < $match['current_players']; $i++): ?>
                                                <div class="player-avatar"><i data-feather="user"></i></div>
                                            <?php endfor; ?>
                                            <?php for($i = 0; $i < ($match['max_players'] - $match['current_players']); $i++): ?>
                                                <div class="player-avatar-empty"></div>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="player-count">
                                            <strong><?php echo (int)$match['current_players']; ?>/<?php echo (int)$match['max_players']; ?></strong> Players
                                        </div>
                                    </div>
                                </div>
                                <div class="match-card-creator">
                                    Created by: <strong><?php echo htmlspecialchars($match['creator_name']); ?></strong>
                                </div>
                            </div>
                            <div class="match-card-action">
                                <?php
                                    $isLoggedIn = !is_null($current_user_id);
                                    $hasJoined = $isLoggedIn && MatchPlayer::hasJoined($GLOBALS['conn'], $match['match_id'], $current_user_id);
                                    $isFull = $match['current_players'] >= $match['max_players'];
                                    $canJoin = $isLoggedIn && !$isFull && !$hasJoined;
                                    $isCreator = $isLoggedIn && $current_user_id == $match['creator_id'];
                                ?>
                                <?php if ($hasJoined): ?>
                                    <form method="POST" action="matchmaking.php">
                                        <input type="hidden" name="action" value="leave_match">
                                        <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                        <button type="submit" class="btn btn-secondary" <?php if ($isCreator) echo 'disabled title="Creators cannot leave a match"'; ?>>Leave</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="matchmaking.php">
                                        <input type="hidden" name="action" value="join_match">
                                        <input type="hidden" name="match_id" value="<?php echo $match['match_id']; ?>">
                                        <button type="submit" class="btn btn-primary" <?php if (!$canJoin) echo 'disabled'; ?>>
                                            <?php echo ($isFull) ? 'Full' : 'Join Match'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Create Match Modal (Hidden by default) -->
    <div id="create-match-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close-btn"><i data-feather="x"></i></button>
            <h2>Create a New Match</h2>
            <form class="create-match-form" method="POST" action="matchmaking.php">
                <input type="hidden" name="action" value="create_match">
                <div class="form-group">
                    <label for="venue_id">Venue</label>
                    <select id="venue_id" name="venue_id" required>
                        <option value="">Select a Venue</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?php echo $venue['venue_id']; ?>"><?php echo htmlspecialchars($venue['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="match_date">Date</label>
                        <input type="date" id="match_date" name="match_date" required>
                    </div>
                    <div>
                        <label for="match_time">Time</label>
                        <input type="time" id="match_time" name="match_time" required>
                    </div>
                </div>
                <div class="form-group two-columns">
                    <div>
                        <label for="min_skill_level">Min Skill</label>
                        <select id="min_skill_level" name="min_skill_level" required>
                            <?php for ($i = 1; $i <= 7; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label for="max_skill_level">Max Skill</label>
                        <select id="max_skill_level" name="max_skill_level" required>
                            <?php for ($i = 1; $i <= 7; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($i==7) echo 'selected';?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" rows="3" placeholder="e.g., 'Friendly game, looking for one more player.'"></textarea>
                </div>
                <button type="submit" class="btn-primary" <?php if (!$isLoggedIn) echo 'disabled'; ?>>
                    <?php echo $isLoggedIn ? 'Create Match' : 'Log in to Create'; ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        feather.replace(); // Initialize Feather Icons

        // --- Modal Logic ---
        const createMatchModal = document.getElementById('create-match-modal');
        const createMatchBtns = document.querySelectorAll('.create-match-header-btn');
        const closeModalBtn = createMatchModal.querySelector('.modal-close-btn');

        createMatchBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                createMatchModal.style.display = 'flex';
            });
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

        // Clean up success/error messages from URL
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            const currentParams = new URLSearchParams(window.location.search);
            // Keep filter params, remove status params
            currentParams.delete('status');
            currentParams.delete('match_id');
            if (currentParams.toString()) {
                window.history.replaceState({path: cleanUrl + '?' + currentParams.toString()}, '', cleanUrl + '?' + currentParams.toString());
            } else {
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
            }
        }
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
<?php include __DIR__ . '/partials/footer.php'; ?>