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

    date_default_timezone_set('Africa/Cairo'); // Set the correct timezone for all date/time operations
    require_once __DIR__ . '/../models/Venue.php'; // For fetching venues for the modal

    // Handle POST requests for creating/joining matches
    $create_error = MatchController::createMatch();
    $join_error = MatchController::joinMatch();
    $leave_error = MatchController::leaveMatch();
    $result_error = MatchController::recordMatchResult();

    // Fetch all open matches, applying any GET filters
    $matches = MatchController::showMatches();

    // Fetch all venues for the "Create Match" modal dropdown
    $venues = Venue::listAll($GLOBALS['conn']);

    $current_user_id = $_SESSION['user_id'] ?? null;
    $isLoggedIn = !is_null($current_user_id);

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
                                    <div class="match-card-details">
                                        <div class="match-skill-level">
                                            <div class="skill-level-label">Skill Level</div>
                                            <div class="skill-level-value"><?php echo htmlspecialchars($match['min_skill_level']); ?> - <?php echo htmlspecialchars($match['max_skill_level']); ?></div>
                                        </div>
                                        <div class="match-players">
                                            <div class="player-avatars">
                                                <?php for($i = 0; $i < $match['current_players']; $i++): ?>
                                                <div class="player-avatar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
                                                    </svg>
                                                </div>
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
                                </div>
                                <div class="match-card-creator">
                                    Created by: <strong><?php echo htmlspecialchars($match['creator_name']); ?></strong>
                                </div>
                                
                                <?php
                                $isLoggedIn = !is_null($current_user_id);
                                $hasJoined = $isLoggedIn && MatchPlayer::hasJoined($GLOBALS['conn'], $match['match_id'], $current_user_id);
                                $isFull = $match['current_players'] >= $match['max_players'];
                                $canJoin = $isLoggedIn && !$isFull && !$hasJoined;
                                $isCreator = $isLoggedIn && ($current_user_id == $match['creator_id']);
                                // A match is considered playable if it's full and the date is today or in the past.
                                $match_datetime_str = $match['match_date'] . ' ' . $match['match_time'];
                                $isPlayable = $isFull && (new DateTime($match_datetime_str) <= new DateTime());
                                ?>
                                <div class="match-card-action">
                                    <?php if ($isCreator && $isPlayable): ?>
                                        <button type="button" class="btn btn-accent record-result-btn" 
                                                data-match-id="<?php echo $match['match_id']; ?>"
                                                data-players='<?php 
                                                    $other_players = array_filter(
                                                        MatchPlayer::getPlayersForMatch($GLOBALS['conn'], $match['match_id']), 
                                                        fn($p) => $p['user_id'] != $current_user_id
                                                    );
                                                    echo htmlspecialchars(json_encode(array_values($other_players)), ENT_QUOTES, 'UTF-8'); 
                                                ?>'>Record Result</button>
                                    <?php elseif ($hasJoined): ?>
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

    <!-- Record Match Result Modal -->
    <div id="record-result-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close-btn"><i data-feather="x"></i></button>
            <h2>Record Match Result</h2>
            <form id="record-result-form" method="POST" action="matchmaking.php">
                <input type="hidden" name="action" value="record_result">
                <input type="hidden" id="record-match-id" name="match_id" value="">

                <div class="form-group">
                    <label for="teammate_id">Who was your teammate?</label>
                    <select id="teammate_id" name="teammate_id" required>
                        <option value="">Select a player</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>

                <div class="form-group">
                    <label>Match Score</label>
                    <div class="score-input-grid">
                        <div class="score-header">Your Team</div>
                        <div class="score-header">Opponents</div>
                        
                        <!-- Set 1 -->
                        <input type="number" name="score[set1][team1]" min="0" max="7" placeholder="S1" required>
                        <input type="number" name="score[set1][team2]" min="0" max="7" placeholder="S1" required>

                        <!-- Set 2 -->
                        <input type="number" name="score[set2][team1]" min="0" max="7" placeholder="S2" required>
                        <input type="number" name="score[set2][team2]" min="0" max="7" placeholder="S2" required>

                        <!-- Set 3 (Optional) -->
                        <input type="number" name="score[set3][team1]" min="0" max="7" placeholder="S3">
                        <input type="number" name="score[set3][team2]" min="0" max="7" placeholder="S3">
                    </div>
                    <small class="form-text">Enter scores for each set. Leave Set 3 blank if not played.</small>
                </div>

                <button type="submit" class="btn-primary">Submit Result</button>
            </form>
        </div>
    </div>


    <!-- Confirmation Modal (for join/leave/create success/error) -->
    <div id="confirmation-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content confirmation-modal-content">
            <div id="confirmation-modal-icon">
                <!-- Icon will be injected by JS -->
            </div>
            <h2 id="confirmation-modal-title"></h2>
            <p id="confirmation-modal-message"></p>
            <button id="confirmation-modal-close" class="btn btn-primary">OK</button>
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

        // --- Record Result Modal Logic ---
        const recordResultModal = document.getElementById('record-result-modal');
        const recordResultBtns = document.querySelectorAll('.record-result-btn');
        const closeRecordModalBtn = recordResultModal.querySelector('.modal-close-btn');
        const recordMatchIdInput = document.getElementById('record-match-id');
        const teammateSelect = document.getElementById('teammate_id');

        recordResultBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const matchId = btn.dataset.matchId;
                const players = JSON.parse(btn.dataset.players);
                
                recordMatchIdInput.value = matchId;
                teammateSelect.innerHTML = '<option value="">Select a player</option>'; // Clear previous options
                players.forEach(player => {
                    const option = new Option(player.name, player.user_id);
                    teammateSelect.add(option);
                });

                recordResultModal.style.display = 'flex';
            });
        });

        closeRecordModalBtn.addEventListener('click', () => recordResultModal.style.display = 'none');
        recordResultModal.addEventListener('click', (e) => {
            if (e.target === recordResultModal) {
                recordResultModal.style.display = 'none';
            }
        });

    </script>
<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    // --- Confirmation Modal Logic ---
    document.addEventListener('DOMContentLoaded', () => {
        const confirmationModal = document.getElementById('confirmation-modal');
        const confirmationModalIcon = document.getElementById('confirmation-modal-icon');
        const confirmationModalTitle = document.getElementById('confirmation-modal-title');
        const confirmationModalMessage = document.getElementById('confirmation-modal-message');
        const confirmationModalClose = document.getElementById('confirmation-modal-close');

        const icons = {
            success: `<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
            error: `<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`
        };

        function showConfirmationModal(type, title, message) {
            if (!confirmationModal) return;
            confirmationModalIcon.innerHTML = icons[type] || '';
            confirmationModalTitle.textContent = title;
            confirmationModalMessage.textContent = message;
            confirmationModal.style.display = 'flex';
        }

        function closeConfirmationModal() {
            if (!confirmationModal) return;
            confirmationModal.style.display = 'none';
        }

        if (confirmationModal) {
            confirmationModalClose.addEventListener('click', closeConfirmationModal);
            confirmationModal.addEventListener('click', (e) => {
                if (e.target === confirmationModal) {
                    closeConfirmationModal();
                }
            });
        }

        // Check for PHP-generated errors
        <?php if (!empty($create_error)): ?>
            showConfirmationModal('error', 'Creation Failed', '<?php echo addslashes($create_error); ?>');
        <?php elseif (!empty($join_error)): ?>
            showConfirmationModal('error', 'Join Failed', '<?php echo addslashes($join_error); ?>');
        <?php elseif (!empty($leave_error)): ?>
            showConfirmationModal('error', 'Could Not Leave', '<?php echo addslashes($leave_error); ?>');
        <?php elseif (!empty($result_error)): ?>
            showConfirmationModal('error', 'Result Error', '<?php echo addslashes($result_error); ?>');
        <?php endif; ?>

        // Check for URL status parameters for success messages
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'created') {
            showConfirmationModal('success', 'Success!', 'Your match has been created successfully.');
        } else if (status === 'joined') {
            showConfirmationModal('success', 'You\'re In!', 'You have successfully joined the match.');
        } else if (status === 'left') {
            showConfirmationModal('success', 'Match Left', 'You have successfully left the match.');
        } else if (status === 'result_recorded') {
            showConfirmationModal('success', 'Success!', 'The match result has been recorded.');
        }

        // Clean up URL after showing modal
        if (status && window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/&?status=[^&]*/, '').replace(/\?&/, '?').replace(/\?$/, '');
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
    });
</script>