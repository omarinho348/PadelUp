<?php
if (!isset($selectedVenue)) { $selectedVenue = null; }
if (!isset($venues)) { $venues = []; }
if (!isset($courts)) { $courts = []; }
if (!isset($bookings)) { $bookings = []; }
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

    <div class="card span-2">
      <h2>Bookings</h2>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Court</th>
              <th>User</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($bookings)): ?>
            <tr><td colspan="6" class="muted">No bookings found.</td></tr>
          <?php else: foreach($bookings as $b): ?>
            <tr>
              <td><?php echo (int)$b['booking_id']; ?></td>
              <td><?php echo htmlspecialchars($b['court_name']); ?></td>
              <td><?php echo htmlspecialchars($b['user_name']); ?></td>
              <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
              <td><?php echo htmlspecialchars(substr($b['start_time'],0,5).' - '.substr($b['end_time'],0,5)); ?></td>
              <td><?php echo htmlspecialchars($b['status']); ?></td>
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
})();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
