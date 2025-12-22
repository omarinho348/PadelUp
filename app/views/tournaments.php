<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Tournaments - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/tournaments.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<?php
require_once __DIR__ . '/../controllers/TournamentsController.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageData = TournamentsController::getTournamentsPageData();
$tournaments = $pageData['tournaments'];
$currentUserId = $pageData['currentUserId'];
?>

    <div class="tournaments-header">
        <div class="container">
            <div class="main-heading">
                <h1>Weekly Tournaments</h1>
                <p>Find and join padel tournaments happening near you.</p>
            </div>

            <div class="filters-container">
                <input type="text" placeholder="Search by name or location...">
                <select><option value="">All Categories</option><option value="mens">Men's</option><option value="womens">Women's</option><option value="mixed">Mixed</option></select>
                <input type="date">
                <button class="btn btn-primary">Find Tournaments</button>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'registered'): ?>
            <div class="alert alert-success">You have registered for the tournament.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <div class="tournaments-grid">
            <?php if (empty($tournaments)): ?>
                <div class="muted">No tournaments found.</div>
            <?php else: foreach ($tournaments as $t): ?>
                <div class="tournament-card">
                    <div class="card-header">
                        <span class="tournament-level">Level <?php echo htmlspecialchars($t['max_level']); ?></span>
                        <span class="tournament-date"><?php echo date('F j, Y', strtotime($t['tournament_date'])); ?></span>
                    </div>
                    <div class="card-body">
                        <h3 class="tournament-title"><?php echo htmlspecialchars($t['tournament_name']); ?></h3>
                        <p class="tournament-location"><?php echo htmlspecialchars($t['venue_name'] . ($t['venue_city'] ? ', ' . $t['venue_city'] : '')); ?></p>
                        <div class="tournament-details">
                            <div class="detail-item">
                                <span class="detail-label">Fee</span>
                                <span class="detail-value">$<?php echo number_format($t['entrance_fee'],2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Prize</span>
                                <span class="detail-value">$<?php echo number_format($t['total_prize_money'],2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Start</span>
                                <span class="detail-value"><?php echo htmlspecialchars(substr($t['start_time'],0,5)); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Slots</span>
                                <span class="detail-value"><?php echo (int)$t['reg_count'] . ' / ' . (int)$t['max_size']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?php 
                        $isFull = (bool)$t['is_full'];
                        $within12Hours = (bool)$t['within_12_hours'];
                        $showDraw = (bool)$t['show_draw'];
                        $registrationClosed = (bool)$t['registration_closed'];
                        ?>

                        <?php if ($showDraw): ?>
                            <a href="/PadelUp/app/views/tournament_draw.php?id=<?php echo (int)$t['tournament_id']; ?>" class="btn btn-primary">View Tournament Draw</a>
                        <?php elseif ($currentUserId && !empty($t['has_registered'])): ?>
                            <button class="btn" disabled>Registered</button>
                        <?php elseif ($registrationClosed): ?>
                            <button class="btn" disabled>Registration Closed</button>
                        <?php elseif ($currentUserId): ?>
                            <button class="btn btn-primary open-partner-modal" data-tournament-id="<?php echo (int)$t['tournament_id']; ?>">Register</button>
                        <?php else: ?>
                            <a class="btn btn-primary" href="/PadelUp/app/views/signin.php">Sign in to register</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Registration Status</h2>
        <p id="modalMessage"></p>
        <button id="modalOk" class="btn btn-primary">OK</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmationModal');
    const span = document.getElementsByClassName('close')[0];
    const okButton = document.getElementById('modalOk');
    const modalMessage = document.getElementById('modalMessage');

    // Partner modal elements
    const partnerModal = document.createElement('div');
    partnerModal.id = 'partnerModal';
    partnerModal.className = 'modal';
    partnerModal.style.display = 'none';
    partnerModal.innerHTML = `
      <div class="modal-content">
        <span class="close close-partner">&times;</span>
        <h2>Enter Partner Email</h2>
        <p>Please enter your partner's email to register your team.</p>
        <form id="partnerForm">
          <input type="email" id="partnerEmail" name="partner_email" placeholder="partner@example.com" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;" />
          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
            <button type="button" id="partnerCancel" class="btn">Cancel</button>
            <button type="submit" class="btn btn-primary">Register Team</button>
          </div>
        </form>
      </div>`;
    document.body.appendChild(partnerModal);

    let activeTournamentId = null;

    // Close modal when clicking X or OK
    span.onclick = function() { modal.style.display = 'none'; }
    okButton.onclick = function() { modal.style.display = 'none'; location.reload(); }
    partnerModal.querySelector('.close-partner').onclick = function() { partnerModal.style.display = 'none'; }
    partnerModal.querySelector('#partnerCancel').onclick = function() { partnerModal.style.display = 'none'; }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
        if (event.target == partnerModal) {
            partnerModal.style.display = 'none';
        }
    }

    // Open partner modal when clicking register button
    document.querySelectorAll('.open-partner-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            activeTournamentId = this.getAttribute('data-tournament-id');
            partnerModal.style.display = 'block';
            const emailInput = document.getElementById('partnerEmail');
            if (emailInput) { emailInput.value = ''; emailInput.focus(); }
        });
    });

    // Handle partner form submission
    document.getElementById('partnerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = document.getElementById('partnerEmail');
        const partnerEmail = emailInput.value.trim();
        if (!partnerEmail || !activeTournamentId) return;

        // Disable form buttons temporarily
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true; submitBtn.textContent = 'Registering...';

        const params = new URLSearchParams();
        params.append('tournament_id', activeTournamentId);
        params.append('partner_email', partnerEmail);

        fetch('/PadelUp/public/api/register_tournament.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(r => r.json())
        .then(data => {
            partnerModal.style.display = 'none';
            if (data.success) {
                modalMessage.textContent = data.message;
            } else {
                modalMessage.textContent = 'Error: ' + data.error;
            }
            modal.style.display = 'block';
        })
        .catch(() => {
            partnerModal.style.display = 'none';
            modalMessage.textContent = 'An error occurred. Please try again.';
            modal.style.display = 'block';
        })
        .finally(() => {
            submitBtn.disabled = false; submitBtn.textContent = 'Register Team';
        });
    });
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>