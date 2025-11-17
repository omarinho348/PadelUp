<?php
require_once __DIR__ . '/../controllers/UserController.php';
UserController::requireSuperAdmin();

// Process messages passed from admin.php and fetch fresh data
$creationMessage = $_POST['creationMessage'] ?? '';
$deleteMessage = $_POST['deleteMessage'] ?? '';
$contactMessage = $_POST['contactMessage'] ?? '';
$coaches = UserController::getCoaches(); // Now handles search
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Padel Coaches - PadelUp</title>
  <link rel="stylesheet" href="../../public/styling/styles.css" />
  <link rel="stylesheet" href="../../public/styling/admin.css" />
</head>
<body>
<?php include __DIR__ . '/partials/admin_navbar.php'; ?>
<div class="container admin-container">
  <div class="admin-header">
    <h1>PadelUp Coaches</h1>
    <p>Manage accounts for certified PadelUp coaches.</p>
  </div>

  <?php if($deleteMessage && $deleteMessage !== 'COACH_DELETED'): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($deleteMessage); ?></div>
  <?php elseif($deleteMessage === 'COACH_DELETED'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">Coach deleted successfully.</div>
  <?php endif; ?>

  <?php if($creationMessage && $creationMessage !== 'COACH_CREATED'): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($creationMessage); ?></div>
  <?php elseif($creationMessage === 'COACH_CREATED'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">Coach created successfully.</div>
  <?php endif; ?>

  <?php if($contactMessage && $contactMessage !== 'MESSAGE_SENT'): ?>
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($contactMessage); ?></div>
  <?php elseif($contactMessage === 'MESSAGE_SENT'): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">Message sent successfully.</div>
  <?php endif; ?>

  <div class="admin-toolbar">
      <form method="GET" action="PadelCoaches.php" class="admin-search-form">
          <input type="search" name="search" placeholder="Search coaches by name..." class="admin-search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
          <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 8px;">Search</button>
      </form>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="margin:0;">Existing Coaches</h2>
    <button id="openCoachModal" class="btn btn-icon" style="background:var(--accent);color:#fff;width:46px;height:46px;display:flex;align-items:center;justify-content:center;font-size:28px;line-height:1;border-radius:50%;">+</button>
  </div>
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Hourly Rate</th>
          <th>Experience</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($coaches)): ?>
        <tr><td colspan="7" style="text-align:center;padding:18px;">No coaches yet.</td></tr>
      <?php else: foreach($coaches as $coach): ?>
        <tr>
          <td><?php echo (int)$coach['user_id']; ?></td>
          <td><?php echo htmlspecialchars($coach['name']); ?></td>
          <td><?php echo htmlspecialchars($coach['email']); ?></td>
          <td>EGP <?php echo htmlspecialchars(number_format($coach['hourly_rate'], 2)); ?></td>
          <td><?php echo htmlspecialchars($coach['experience_years']); ?> years</td>
          <td>
            <button class="btn-action contact contact-coach-btn"
                    data-coach-id="<?php echo (int)$coach['user_id']; ?>"
                    data-coach-email="<?php echo htmlspecialchars($coach['email']); ?>"
                    data-coach-name="<?php echo htmlspecialchars($coach['name']); ?>">
                Contact
            </button>
            <form method="POST" action="admin.php" onsubmit="return confirm('Are you sure you want to delete this coach?');" style="display:inline;">
              <input type="hidden" name="delete_coach_id" value="<?php echo (int)$coach['user_id']; ?>" />
              <button type="submit" style="background:#dc3545;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:12px;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for Contacting a Coach -->
<div id="contactModal" class="modal-overlay" style="display:none;">
  <div class="modal-content" style="max-width: 550px;">
    <button id="closeContactModal" class="modal-close-btn">✕</button>
    <h2 style="margin:0 0 4px;font-size:26px;">Contact Coach</h2>
    <p id="contactModalSub" style="margin:0 0 18px;font-size:14px;color:#555;">Your message will be sent to the coach's email.</p>    
    <form method="POST" action="admin.php" id="contactForm" class="auth-form" style="display:grid;gap:14px; max-width: 100%;">
      <input type="hidden" name="contact_coach_id" id="contact_coach_id_input">
      
      <div class="form-group">
        <label for="recipient_email">Recipient</label>
        <input type="email" id="recipient_email_input" name="recipient_email" readonly style="background-color: #e9ecef; cursor: not-allowed;">
      </div>

      <div class="form-group">
        <label for="message_subject">Subject</label>
        <input type="text" id="message_subject_input" name="subject" value="A message from PadelUp Admin" required>
      </div>

      <div class="form-group">
        <label for="message_body">Message</label>
        <textarea id="message_body_input" name="message" rows="6" required placeholder="Write your message here..."></textarea>
      </div>

      <button type="submit" class="btn-primary" style="margin-top:4px; justify-self: flex-end;">Send Message</button>
    </form>
  </div>
</div>

<!-- Modal for Creating a New Coach -->
<div id="coachModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:2000;">
  <div style="width:480px;max-width:92%;background:#fff;border-radius:18px;padding:28px;box-shadow:0 20px 40px rgba(0,0,0,0.25);position:relative;">
    <button id="closeCoachModal" style="position:absolute;top:12px;right:12px;background:transparent;border:none;font-size:20px;cursor:pointer;color:#444;">✕</button>
    <h2 style="margin:0 0 4px;font-size:26px;">New PadelUp Coach</h2>
    <p style="margin:0 0 18px;font-size:14px;color:#555;">Create a new coach account and profile.</p>    
    <form method="POST" action="admin.php" id="coachForm" class="auth-form" style="display:grid;gap:14px; max-width: 100%;">
      <h3 style="margin:0 0 8px;font-size:16px;">Coach Info</h3>
      <input type="text" name="name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required style="margin-top:10px;" />
      <input type="text" name="phone" placeholder="Phone (optional)" style="margin-top:10px;" />
      <input type="password" name="password" placeholder="Password" required style="margin-top:10px;" />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required style="margin-top:10px;" />
      
      <h3 style="margin:8px 0 8px;font-size:16px;">Coach Profile</h3>
      <textarea name="bio" placeholder="Short Bio (e.g., 'Certified coach specializing in youth training...')" style="min-height: 80px; margin-top: 0;"></textarea>
      <div style="display:flex;gap:10px;margin-top:10px;">
        <div style="flex:1;display:flex;flex-direction:column;">
          <label style="font-size:12px;margin-bottom:4px;">Hourly Rate (EGP)</label>
          <input type="number" name="hourly_rate" min="0" step="0.01" placeholder="e.g. 300.00" required />
        </div>
        <div style="flex:1;display:flex;flex-direction:column;">
          <label style="font-size:12px;margin-bottom:4px;">Years of Experience</label>
          <input type="number" name="experience_years" min="0" step="1" placeholder="e.g. 5" required />
        </div>
      </div>
      <input type="text" name="location" placeholder="Location (e.g., Cairo, Egypt)" required style="margin-top:10px;" />

      <button type="submit" class="btn-primary" style="margin-top:4px;">Create Coach</button>
    </form>
  </div>
</div>

<script>
 (function(){
   const openBtn = document.getElementById('openCoachModal');
   const modal = document.getElementById('coachModal');
   const closeBtn = document.getElementById('closeCoachModal');
   const form = document.getElementById('coachForm');
   function open(){ modal.style.display='flex'; setTimeout(()=>{ const first=form.querySelector('input[name="name"]'); if(first) first.focus(); },50); }
   function close(){ modal.style.display='none'; }
   openBtn.addEventListener('click', open);
   closeBtn.addEventListener('click', close);
   modal.addEventListener('click', e=>{ if(e.target===modal) close(); });
   document.addEventListener('keydown', e=>{ if(e.key==='Escape' && modal.style.display==='flex') close(); });

   // Contact Modal JS
   const contactModal = document.getElementById('contactModal');
   const closeContactBtn = document.getElementById('closeContactModal');
   document.querySelectorAll('.contact-coach-btn').forEach(btn => {
     btn.addEventListener('click', function() {
       document.getElementById('contact_coach_id_input').value = this.dataset.coachId;
       document.getElementById('recipient_email_input').value = this.dataset.coachEmail;
       document.getElementById('contactModalSub').innerText = `Your message will be sent to ${this.dataset.coachName}.`;
       contactModal.style.display = 'flex';
     });
   });
   function closeContact(){ contactModal.style.display='none'; }
   closeContactBtn.addEventListener('click', closeContact);
   contactModal.addEventListener('click', e => { if(e.target === contactModal) closeContact(); });
   document.addEventListener('keydown', e => { if(e.key === 'Escape' && contactModal.style.display === 'flex') closeContact(); });
 })();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>