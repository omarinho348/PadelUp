<?php
if (!isset($venueAdmins)) { $venueAdmins = []; }
if (!isset($creationMessage)) { $creationMessage = ''; }
if (!isset($deleteMessage)) { $deleteMessage = ''; }
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Venue Admins - PadelUp</title>
  <link rel="stylesheet" href="../../public/styling/styles.css" />
  <link rel="stylesheet" href="../../public/styling/admin.css" />
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>
<div class="container admin-container">
  <div class="admin-header">
    <h1>Venue Admins</h1>
    <p>Manage accounts with venue administration privileges.</p>
  </div>

  <?php if($deleteMessage && $deleteMessage !== 'VENUE_ADMIN_DELETED'): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;">
      <?php echo htmlspecialchars($deleteMessage); ?>
    </div>
  <?php elseif($deleteMessage === 'VENUE_ADMIN_DELETED'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">
      Venue admin deleted successfully.
    </div>
  <?php endif; ?>

  <?php if($creationMessage && $creationMessage !== 'VENUE_ADMIN_CREATED'): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;">
      <?php echo htmlspecialchars($creationMessage); ?>
    </div>
  <?php elseif($creationMessage === 'VENUE_ADMIN_CREATED'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">
      Venue admin created successfully.
    </div>
  <?php endif; ?>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="margin:0;">Existing Venue Admins</h2>
    <button id="openAdminModal" class="btn btn-icon" style="background:var(--accent);color:#fff;width:46px;height:46px;display:flex;align-items:center;justify-content:center;font-size:28px;line-height:1;border-radius:50%;">+</button>
  </div>
  <div class="admin-grid" style="grid-template-columns: 1fr; align-items: flex-start;">
    <div>
      <div class="admin-table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($venueAdmins)): ?>
            <tr><td colspan="6" style="text-align:center;padding:18px;">No venue admins yet.</td></tr>
          <?php else: foreach($venueAdmins as $admin): ?>
            <tr>
              <td><?php echo (int)$admin['user_id']; ?></td>
              <td><?php echo htmlspecialchars($admin['name']); ?></td>
              <td><?php echo htmlspecialchars($admin['email']); ?></td>
              <td><?php echo htmlspecialchars($admin['phone'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this venue admin and all their venues?');" style="display:inline;">
                  <input type="hidden" name="delete_admin_id" value="<?php echo (int)$admin['user_id']; ?>" />
                  <button type="submit" style="background:#dc3545;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    </div>
  </div>
</div>

<!-- Modal Overlay -->
<div id="adminModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:2000;">
  <div style="width:480px;max-width:92%;background:#fff;border-radius:18px;padding:28px;box-shadow:0 20px 40px rgba(0,0,0,0.25);position:relative;">
    <button id="closeAdminModal" style="position:absolute;top:12px;right:12px;background:transparent;border:none;font-size:20px;cursor:pointer;color:#444;">✕</button>
    <h2 style="margin:0 0 4px;font-size:26px;">New Venue Admin</h2>
    <p style="margin:0 0 18px;font-size:14px;color:#555;">Create a venue admin and their initial venue.</p>
    <form method="POST" id="venueAdminForm" class="auth-form" style="display:grid;gap:14px;">
      <div>
        <h3 style="margin:0 0 8px;font-size:16px;">Admin Info</h3>
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required style="margin-top:10px;" />
        <input type="text" name="phone" placeholder="Phone (optional)" style="margin-top:10px;" />
        <input type="password" name="password" placeholder="Password" required style="margin-top:10px;" />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required style="margin-top:10px;" />
      </div>
      <div>
        <h3 style="margin:8px 0 8px;font-size:16px;">Venue Info</h3>
        <input type="text" name="venue_name" placeholder="Venue Name" required />
        <input type="text" name="venue_address" placeholder="Address" required style="margin-top:10px;" />
        <input type="text" name="venue_city" placeholder="City" required style="margin-top:10px;" />
        <div style="display:flex;gap:10px;margin-top:10px;">
          <div style="flex:1;display:flex;flex-direction:column;">
            <label style="font-size:12px;margin-bottom:4px;">Opening Time</label>
            <input type="time" name="opening_time" required />
          </div>
          <div style="flex:1;display:flex;flex-direction:column;">
            <label style="font-size:12px;margin-bottom:4px;">Closing Time</label>
            <input type="time" name="closing_time" required />
          </div>
        </div>
        <div style="display:flex;flex-direction:column;margin-top:10px;">
          <label style="font-size:12px;margin-bottom:4px;">Hourly Rate (EGP)</label>
          <input type="number" name="hourly_rate" min="0" step="1" placeholder="e.g. 25" required />
        </div>
      </div>
      <button type="submit" class="btn-primary" style="margin-top:4px;">Create Venue Admin + Venue</button>
    </form>
  </div>
</div>

<script>
 (function(){
   const openBtn = document.getElementById('openAdminModal');
   const modal = document.getElementById('adminModal');
   const closeBtn = document.getElementById('closeAdminModal');
   const form = document.getElementById('venueAdminForm');
   function open(){ modal.style.display='flex'; setTimeout(()=>{ const first=form.querySelector('input[name="name"]'); if(first) first.focus(); },50); }
   function close(){ modal.style.display='none'; }
   openBtn.addEventListener('click', open);
   closeBtn.addEventListener('click', close);
   modal.addEventListener('click', e=>{ if(e.target===modal) close(); });
   document.addEventListener('keydown', e=>{ if(e.key==='Escape' && modal.style.display==='flex') close(); });
 })();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>