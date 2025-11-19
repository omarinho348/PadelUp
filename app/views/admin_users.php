<?php 
require_once __DIR__ . '/../controllers/UserController.php';
UserController::requireSuperAdmin();

// Handle deletion and fetch all players
$deleteMessage = UserController::deletePlayer();
$contactMessage = UserController::contactPlayer();
$players = UserController::getPlayers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Management - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/admin_navbar.php'; ?>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Player Management</h1>
            <p>View, search, and manage all player accounts.</p>
        </div>

        <?php if($deleteMessage && $deleteMessage !== 'PLAYER_DELETED'): ?>
            <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($deleteMessage); ?></div>
        <?php elseif($deleteMessage === 'PLAYER_DELETED'): ?>
            <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">Player deleted successfully.</div>
        <?php endif; ?>

        <?php if($contactMessage && $contactMessage !== 'MESSAGE_SENT'): ?>
            <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($contactMessage); ?></div>
        <?php elseif($contactMessage === 'MESSAGE_SENT'): ?>
            <div style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:16px;">Message sent successfully.</div>
        <?php endif; ?>

        <div class="admin-toolbar">
            <form method="GET" action="admin_users.php" class="admin-search-form">
                <input type="search" name="search" placeholder="Search players by name..." class="admin-search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn btn-primary" style="padding: 10px 16px; border-radius: 8px;">Search</button>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Skill Level</th>
                        <th>Gender</th>
                        <th>Preferred Side</th>
                        <th>Register Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($players)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:18px;">No players found.</td></tr>
                    <?php else: foreach($players as $player): ?>
                        <tr>
                            <td><?php echo (int)$player['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($player['name']); ?></td>
                            <td><?php echo htmlspecialchars($player['email']); ?></td>
                            <td><?php echo htmlspecialchars(number_format((float)($player['skill_level'] ?? 0), 2)); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($player['gender'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($player['preferred_side'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($player['created_at']))); ?></td>
                            <td class="actions">
                                <button class="btn-action contact contact-player-btn" 
                                        data-player-id="<?php echo (int)$player['user_id']; ?>"
                                        data-player-email="<?php echo htmlspecialchars($player['email']); ?>"
                                        data-player-name="<?php echo htmlspecialchars($player['name']); ?>">
                                    Contact
                                </button>
                                <form method="POST" action="admin_users.php" onsubmit="return confirm('Are you sure you want to block this player?');" style="display:inline;">
                                    <input type="hidden" name="block_player_id" value="<?php echo (int)$player['user_id']; ?>" />
                                    <button type="submit" class="btn-action block">Block</button>
                                </form>
                                <form method="POST" action="admin_users.php" onsubmit="return confirm('Are you sure you want to delete this player?');" style="display:inline;">
                                    <input type="hidden" name="delete_player_id" value="<?php echo (int)$player['user_id']; ?>" />
                                    <button type="submit" class="btn-action delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Modal for Contacting a Player -->
<div id="contactModal" class="modal-overlay" style="display:none;">
  <div class="modal-content" style="max-width: 550px;">
    <button id="closeContactModal" class="modal-close-btn">✕</button>
    <h2 style="margin:0 0 4px;font-size:26px;">Contact Player</h2>
    <p id="contactModalSub" style="margin:0 0 18px;font-size:14px;color:#555;">Your message will be sent to the player's email.</p>    
    <form method="POST" action="admin_users.php" id="contactForm" class="auth-form" style="display:grid;gap:14px; max-width: 100%;">
      <input type="hidden" name="contact_player_id" id="contact_player_id_input">
      
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

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
 (function(){
   const modal = document.getElementById('contactModal');
   const closeBtn = document.getElementById('closeContactModal');
   document.querySelectorAll('.contact-player-btn').forEach(btn => {
     btn.addEventListener('click', function() {
       document.getElementById('contact_player_id_input').value = this.dataset.playerId;
       document.getElementById('recipient_email_input').value = this.dataset.playerEmail;
       document.getElementById('contactModalSub').innerText = `Your message will be sent to ${this.dataset.playerName}.`;
       modal.style.display = 'flex';
     });
   });
   function close(){ modal.style.display='none'; }
   closeBtn.addEventListener('click', close);
   modal.addEventListener('click', e => { if(e.target === modal) close(); });
   document.addEventListener('keydown', e => { if(e.key === 'Escape' && modal.style.display === 'flex') close(); });
 })();
</script>
</body>
</html>