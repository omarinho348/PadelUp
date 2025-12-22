<?php
if (!isset($selectedVenue)) { $selectedVenue = null; }
if (!isset($venues)) { $venues = []; }
if (!isset($courts)) { $courts = []; }
if (!isset($bookings)) { $bookings = []; }
if (!isset($tournaments)) { $tournaments = []; }
if (!isset($message)) { $message = ''; }
if (!isset($success)) { $success = ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Venue Admin - PadelUp</title>
  <link rel="stylesheet" href="../../public/styling/styles.css" />
  <link rel="stylesheet" href="../../public/styling/venue-admin.css" />
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>
<div class="venue-admin">
<div class="container">
  <div class="va-header">
  <div style="display:flex;align-items:center;gap:18px;">
    <?php if (!empty($selectedVenue['logo_path'])): ?>
      <img src="../../<?php echo htmlspecialchars($selectedVenue['logo_path']); ?>" alt="Venue Logo" style="max-width:64px;max-height:64px;border-radius:8px;border:1px solid #eee;box-shadow:0 2px 8px rgba(0,0,0,0.07);background:#fff;" />
    <?php endif; ?>
    <div>
      <h1>Venue Admin</h1>
      <p>Manage your venue settings, courts, and bookings.</p>
    </div>
  </div>
  <?php if (!empty($venues) && count($venues) > 1): ?>
    <form method="GET" class="venue-switcher">
      <label>Select Venue</label>
      <select name="venue_id" onchange="this.form.submit()">
        <?php foreach($venues as $v): ?>
          <option value="<?php echo (int)$v['venue_id']; ?>" <?php if($selectedVenue && $selectedVenue['venue_id']==$v['venue_id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($v['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  <?php endif; ?>
</div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if(!$selectedVenue): ?>
    <div class="card">
      <h2>No Venue Found</h2>
      <p>You don't have a venue assigned yet. Please contact a super admin.</p>
    </div>
  <?php else: ?>
  <section class="grid">
    <div class="card">
      <h2>Venue Settings</h2>
      <form method="POST" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_venue" />
        <div class="field">
          <label>Venue Name</label>
          <input type="text" value="<?php echo htmlspecialchars($selectedVenue['name']); ?>" disabled />
        </div>
        <div class="field">
          <label>Hourly Rate</label>
          <input type="number" name="hourly_rate" min="0" step="1" value="<?php echo (int)$selectedVenue['hourly_rate']; ?>" required />
        </div>
        <div class="field">
          <label>Opening Time</label>
          <input type="time" name="opening_time" value="<?php echo htmlspecialchars($selectedVenue['opening_time']); ?>" required />
        </div>
        <div class="field">
          <label>Closing Time</label>
          <input type="time" name="closing_time" value="<?php echo htmlspecialchars($selectedVenue['closing_time']); ?>" required />
        </div>
        <div class="field">
          <label>Edit Venue Logo (JPG/PNG)</label>
          <input type="file" name="venue_logo" accept="image/jpeg,image/png" />
        </div>
        <div class="actions">
          <button type="submit" class="btn-primary">Save Settings</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-head">
        <h2>Courts</h2>
        <button id="openAddCourt" class="btn">+ Add Court</button>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($courts)): ?>
            <tr><td colspan="5" class="muted">No courts yet.</td></tr>
          <?php else: foreach($courts as $c): ?>
            <tr>
              <td><?php echo (int)$c['court_id']; ?></td>
              <td><?php echo htmlspecialchars($c['court_name']); ?></td>
              <td><?php echo htmlspecialchars($c['court_type']); ?></td>
              <td><?php echo ($c['is_active'] ? 'Open' : 'Closed'); ?></td>
              <td class="row-actions">
                <button class="action-btn edit" data-cid="<?php echo (int)$c['court_id']; ?>" data-name="<?php echo htmlspecialchars($c['court_name']); ?>" data-type="<?php echo htmlspecialchars($c['court_type']); ?>">Edit</button>
                <form method="POST" style="display:inline" onsubmit="return confirm('Toggle court status?');">
                  <input type="hidden" name="action" value="toggle_court" />
                  <input type="hidden" name="court_id" value="<?php echo (int)$c['court_id']; ?>" />
                  <input type="hidden" name="desired" value="<?php echo $c['is_active'] ? 'close' : 'open'; ?>" />
                  <button type="submit" class="action-btn"><?php echo $c['is_active'] ? 'Close' : 'Open'; ?></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this court? This removes its bookings.');">
                  <input type="hidden" name="action" value="delete_court" />
                  <input type="hidden" name="court_id" value="<?php echo (int)$c['court_id']; ?>" />
                  <button type="submit" class="action-btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2>Create Tournament</h2>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="create_tournament" />
        <div class="field">
          <label>Name</label>
          <input type="text" name="tournament_name" maxlength="150" required />
        </div>
        <div class="field">
          <label>Date</label>
          <input type="date" name="tournament_date" required />
        </div>
        <div class="field">
          <label>Start Time</label>
          <input type="time" name="start_time" required />
        </div>
        <div class="field">
          <label>Max Level</label>
          <select name="max_level" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
          </select>
        </div>
        <div class="field">
          <label>Max Tournament Size</label>
          <select name="max_size" required>
            <option value="4">4</option>
            <option value="8">8</option>
            <option value="16">16</option>
          </select>
        </div>
        <div class="field">
          <label>Entrance Fee ($)</label>
          <input type="number" name="entrance_fee" min="0" step="0.01" value="0" required />
        </div>
        <div class="field">
          <label>Total Prize Money</label>
          <input type="number" name="total_prize_money" min="0" step="0.01" value="0.00" required />
        </div>
        <div class="actions">
          <button type="submit" class="btn-primary">Create Tournament</button>
        </div>
      </form>
    </div>

    <div class="card span-2">
      <h2>Bookings</h2>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Court</th>
              <th>User</th>
              <th>Contact</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($bookings)): ?>
            <tr><td colspan="8" class="muted">No bookings found.</td></tr>
          <?php else: foreach($bookings as $b): ?>
            <tr>
              <td><?php echo (int)$b['booking_id']; ?></td>
              <td><?php echo htmlspecialchars($b['court_name']); ?></td>
              <td><?php echo htmlspecialchars($b['user_name']); ?></td>
              <td class="row-actions">
                <button type="button"
                        class="action-btn view-user-info"
                        data-user-name="<?php echo htmlspecialchars($b['user_name']); ?>"
                        data-user-email="<?php echo htmlspecialchars($b['user_email'] ?? ''); ?>"
                        data-user-phone="<?php echo htmlspecialchars($b['user_phone'] ?? ''); ?>">
                  View info
                </button>
              </td>
              <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
              <td><?php echo htmlspecialchars(substr($b['start_time'],0,5).' - '.substr($b['end_time'],0,5)); ?></td>
              <td><?php echo htmlspecialchars($b['status']); ?></td>
              <td class="row-actions">
                <?php 
                  $isPaid = ($b['status'] === 'paid');
                  $isCancelled = ($b['status'] === 'cancelled');
                ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Mark this booking as Paid?');">
                  <input type="hidden" name="action" value="update_booking_status" />
                  <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>" />
                  <input type="hidden" name="new_status" value="paid" />
                  <button type="submit" class="action-btn" <?php echo ($isPaid || $isCancelled) ? 'disabled' : ''; ?>>Mark Paid</button>
                </form>
                <?php if(!$isPaid): ?>
                  <form method="POST" style="display:inline" onsubmit="return confirm('Cancel this booking?');">
                    <input type="hidden" name="action" value="update_booking_status" />
                    <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>" />
                    <input type="hidden" name="new_status" value="cancelled" />
                    <button type="submit" class="action-btn danger" <?php echo $isCancelled ? 'disabled' : ''; ?>>Cancel</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- My Tournaments Section -->
  <section class="va-section" style="margin-top: 32px;">
    <div class="card span-2">
      <h2>My Tournaments</h2>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Date</th>
              <th>Start</th>
              <th>Level</th>
              <th>Fee</th>
              <th>Prize</th>
              <th>Registered</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($tournaments)): ?>
            <tr><td colspan="10" class="muted">No tournaments found.</td></tr>
          <?php else: foreach($tournaments as $t): ?>
            <tr>
              <td><?php echo (int)$t['tournament_id']; ?></td>
              <td><?php echo htmlspecialchars($t['tournament_name']); ?></td>
              <td><?php echo htmlspecialchars($t['tournament_date']); ?></td>
              <td><?php echo htmlspecialchars(substr($t['start_time'],0,5)); ?></td>
              <td><?php echo htmlspecialchars($t['max_level']); ?></td>
              <td>$<?php echo number_format($t['entrance_fee'],2); ?></td>
              <td>$<?php echo number_format($t['total_prize_money'],2); ?></td>
              <td><?php echo Tournament::getRegistrationCount($conn, (int)$t['tournament_id']) . ' / ' . (int)$t['max_size']; ?></td>
              <td>
                <span class="status-badge status-<?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></span>
              </td>
              <td class="row-actions">
                <?php if($t['status'] === 'scheduled'): ?>
                  <?php 
                    // Check if draw exists
                    $hasDraw = Tournament::hasDraw($conn, (int)$t['tournament_id']);
                  ?>
                  <?php if($hasDraw): ?>
                    <button type="button" class="action-btn" onclick="openResultsModal(<?php echo (int)$t['tournament_id']; ?>, '<?php echo htmlspecialchars($t['tournament_name']); ?>')">Enter Results</button>
                  <?php endif; ?>
                  <form method="POST" style="display:inline" onsubmit="return confirm('Mark this tournament as completed?');">
                    <input type="hidden" name="action" value="update_tournament_status" />
                    <input type="hidden" name="tournament_id" value="<?php echo (int)$t['tournament_id']; ?>" />
                    <input type="hidden" name="new_status" value="completed" />
                    <button type="submit" class="action-btn">Complete</button>
                  </form>
                  <form method="POST" style="display:inline" onsubmit="return confirm('Cancel this tournament?');">
                    <input type="hidden" name="action" value="update_tournament_status" />
                    <input type="hidden" name="tournament_id" value="<?php echo (int)$t['tournament_id']; ?>" />
                    <input type="hidden" name="new_status" value="cancelled" />
                    <button type="submit" class="action-btn danger">Cancel</button>
                  </form>
                <?php else: ?>
                  <span class="muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Add Court Modal -->
  <div id="addCourtModal" class="modal" style="display:none;">
    <div class="modal-dialog">
      <button class="modal-close" id="closeAddCourt">✕</button>
      <h3>Add Court</h3>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="create_court" />
        <div class="field">
          <label>Court Name</label>
          <input type="text" name="court_name" required />
        </div>
        <div class="field">
          <label>Type</label>
          <select name="court_type">
            <option value="outdoor">Outdoor</option>
            <option value="indoor">Indoor</option>
            <option value="covered">Covered</option>
          </select>
        </div>
        <div class="actions">
          <button type="submit" class="btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Court Modal -->
  <div id="editCourtModal" class="modal" style="display:none;">
    <div class="modal-dialog">
      <button class="modal-close" id="closeEditCourt">✕</button>
      <h3>Edit Court</h3>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="update_court" />
        <input type="hidden" name="court_id" id="editCourtId" />
        <div class="field">
          <label>Court Name</label>
          <input type="text" name="court_name" id="editCourtName" required />
        </div>
        <div class="field">
          <label>Type</label>
          <select name="court_type" id="editCourtType">
            <option value="outdoor">Outdoor</option>
            <option value="indoor">Indoor</option>
            <option value="covered">Covered</option>
          </select>
        </div>
        <div class="actions">
          <button type="submit" class="btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- User Info Modal -->
  <div id="userInfoModal" class="modal" style="display:none;">
    <div class="modal-dialog">
      <button class="modal-close" id="closeUserInfo">✕</button>
      <h3>User Info</h3>
      <div class="form-grid">
        <div class="field">
          <label>Name</label>
          <div id="uimName" class="muted"></div>
        </div>
        <div class="field">
          <label>Email</label>
          <div id="uimEmail" class="muted"></div>
        </div>
        <div class="field">
          <label>Phone</label>
          <div id="uimPhone" class="muted"></div>
        </div>
      </div>
      <div class="actions" style="margin-top:12px;">
        <button class="btn" id="closeUserInfoBottom">Close</button>
      </div>
    </div>
  </div>

  <?php endif; ?>
</div>
</div>

<script>
(function(){
  const addBtn = document.getElementById('openAddCourt');
  const addModal = document.getElementById('addCourtModal');
  const closeAdd = document.getElementById('closeAddCourt');
  if(addBtn){ addBtn.addEventListener('click',()=> addModal.style.display='flex'); }
  if(closeAdd){ closeAdd.addEventListener('click',()=> addModal.style.display='none'); }
  
  const editModal = document.getElementById('editCourtModal');
  const closeEdit = document.getElementById('closeEditCourt');
  if(closeEdit){ closeEdit.addEventListener('click',()=> editModal.style.display='none'); }
  document.querySelectorAll('button.edit').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.getElementById('editCourtId').value = btn.dataset.cid;
      document.getElementById('editCourtName').value = btn.dataset.name;
      document.getElementById('editCourtType').value = btn.dataset.type;
      editModal.style.display='flex';
    });
  });
  [addModal, editModal].forEach(m=>{
    if(!m) return; m.addEventListener('click', e=>{ if(e.target===m) m.style.display='none'; });
  })

  // User Info Modal logic
  const userInfoModal = document.getElementById('userInfoModal');
  const closeUserInfo = document.getElementById('closeUserInfo');
  const closeUserInfoBottom = document.getElementById('closeUserInfoBottom');
  function hideUserInfo(){ if(userInfoModal) userInfoModal.style.display = 'none'; }
  if(closeUserInfo){ closeUserInfo.addEventListener('click', hideUserInfo); }
  if(closeUserInfoBottom){ closeUserInfoBottom.addEventListener('click', hideUserInfo); }
  if(userInfoModal){ userInfoModal.addEventListener('click', e=>{ if(e.target===userInfoModal) hideUserInfo(); }); }

  document.querySelectorAll('button.view-user-info').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const name = btn.dataset.userName || 'Not provided';
      const email = btn.dataset.userEmail || 'Not provided';
      const phone = btn.dataset.userPhone || 'Not provided';
      const nameEl = document.getElementById('uimName');
      const emailEl = document.getElementById('uimEmail');
      const phoneEl = document.getElementById('uimPhone');
      if(nameEl) nameEl.textContent = name;
      if(emailEl) emailEl.textContent = email;
      if(phoneEl) phoneEl.textContent = phone;
      if(userInfoModal) userInfoModal.style.display = 'flex';
    });
  });
})();
</script>

  <!-- Tournament Results Modal -->
  <div id="resultsModal" class="modal" style="display:none;">
    <div class="modal-dialog" style="max-width: 700px;">
      <button class="modal-close" onclick="closeResultsModal()">✕</button>
      <h3 id="resultsModalTitle">Enter Match Results</h3>
      <div class="results-modal-content" id="resultsModalContent">
        <p style="text-align: center; padding: 20px; color: var(--muted);">Loading matches...</p>
      </div>
      <div style="text-align: right; margin-top: 16px;">
        <button type="button" class="btn btn-secondary" onclick="closeResultsModal()">Close</button>
      </div>
    </div>
  </div>

<script>
let currentTournamentId = null;

function openResultsModal(tournamentId, tournamentName) {
  currentTournamentId = tournamentId;
  document.getElementById('resultsModalTitle').textContent = `Enter Match Results - ${tournamentName}`;
  document.getElementById('resultsModal').style.display = 'flex';
  loadTournamentMatches(tournamentId);
}

function closeResultsModal() {
  document.getElementById('resultsModal').style.display = 'none';
  currentTournamentId = null;
}

function loadTournamentMatches(tournamentId) {
  fetch(`/PadelUp/public/api/get_tournament_matches.php?tournament_id=${tournamentId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        renderMatches(data.matches);
      } else {
        document.getElementById('resultsModalContent').innerHTML = 
          `<p class="no-matches-message">Error loading matches: ${data.error || 'Unknown error'}</p>`;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('resultsModalContent').innerHTML = 
        '<p class="no-matches-message">Error loading matches. Please try again.</p>';
    });
}

function renderMatches(matches) {
  const content = document.getElementById('resultsModalContent');
  
  if (!matches || matches.length === 0) {
    content.innerHTML = '<p class="no-matches-message">No matches available yet. The draw must be generated first.</p>';
    return;
  }

  let html = '';
  matches.forEach(match => {
    const hasWinner = match.winner_seed !== null;
    
    html += `
      <div class="match-result-item">
        <h4>${match.round_name} - Match ${match.match_number}</h4>
        <div class="match-teams">
          <label class="team-option ${hasWinner && match.winner_seed === match.team1_seed ? 'selected' : ''}">
            <input type="radio" 
                   name="match_${match.round_number}_${match.match_number}" 
                   value="${match.team1_seed}"
                   ${hasWinner && match.winner_seed === match.team1_seed ? 'checked' : ''}
                   onchange="setMatchWinner(${match.round_number}, ${match.match_number}, ${match.team1_seed}, ${match.team2_seed}, ${match.team1_seed})"
                   ${match.team1_is_bye ? 'disabled' : ''}>
            <div class="team-names">
              ${match.team1_is_bye ? 
                '<span class="team-member-name">BYE</span>' :
                `<span class="team-member-name">${match.team1_player1}</span>
                 <span class="team-member-name">${match.team1_player2}</span>`
              }
            </div>
            ${hasWinner && match.winner_seed === match.team1_seed ? '<span class="winner-indicator">WINNER</span>' : ''}
          </label>
          
          <label class="team-option ${hasWinner && match.winner_seed === match.team2_seed ? 'selected' : ''}">
            <input type="radio" 
                   name="match_${match.round_number}_${match.match_number}" 
                   value="${match.team2_seed}"
                   ${hasWinner && match.winner_seed === match.team2_seed ? 'checked' : ''}
                   onchange="setMatchWinner(${match.round_number}, ${match.match_number}, ${match.team1_seed}, ${match.team2_seed}, ${match.team2_seed})"
                   ${match.team2_is_bye ? 'disabled' : ''}>
            <div class="team-names">
              ${match.team2_is_bye ? 
                '<span class="team-member-name">BYE</span>' :
                match.team2_is_tbd ?
                '<span class="team-member-name">TBD</span>' :
                `<span class="team-member-name">${match.team2_player1}</span>
                 <span class="team-member-name">${match.team2_player2}</span>`
              }
            </div>
            ${hasWinner && match.winner_seed === match.team2_seed ? '<span class="winner-indicator">WINNER</span>' : ''}
          </label>
        </div>
      </div>
    `;
  });
  
  content.innerHTML = html;
}

function setMatchWinner(roundNumber, matchNumber, team1Seed, team2Seed, winnerSeed) {
  fetch('/PadelUp/public/api/set_match_winner.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      tournament_id: currentTournamentId,
      round_number: roundNumber,
      match_number: matchNumber,
      team1_seed: team1Seed,
      team2_seed: team2Seed,
      winner_seed: winnerSeed
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Reload matches to update TBD teams in next rounds
      loadTournamentMatches(currentTournamentId);
    } else {
      alert('Error: ' + (data.error || 'Could not set winner'));
      // Reload to reset radio buttons
      loadTournamentMatches(currentTournamentId);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred');
    loadTournamentMatches(currentTournamentId);
  });
}

// Close modal when clicking outside
document.getElementById('resultsModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeResultsModal();
  }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
