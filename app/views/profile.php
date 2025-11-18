<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/UserController.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    header("Location: signin.php");
    exit();
}
// Fetch player profile (if role player)
$profile = null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'player') {
    $profile = UserController::getPlayerProfile();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container profile-container">
        <div class="profile-header">
            <div class="profile-cover-photo"></div>
            <div class="profile-info">
                <div class="profile-photo-container">
                    <div class="profile-photo">
                        <!-- Default profile icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                    <div class="profile-stats">
                        <?php if ($profile): ?>
                        <div class="stat">
                            <span class="stat-label">Skill Level</span>
                            <span class="stat-value"><?php echo ucfirst(htmlspecialchars($profile['skill_level'])); ?></span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Padel IQ</span>
                            <span class="stat-value"><?php echo (int)$profile['padel_iq_rating']; ?></span>
                        </div>
                        <?php if(!empty($profile['gender'])): ?>
                        <div class="stat">
                            <span class="stat-label">Gender</span>
                            <span class="stat-value"><?php echo ucfirst(htmlspecialchars($profile['gender'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($profile['preferred_hand'])): ?>
                        <div class="stat">
                            <span class="stat-label">Preferred Hand</span>
                            <span class="stat-value"><?php echo ucfirst(htmlspecialchars($profile['preferred_hand'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($profile['birth_date'])): ?>
                        <div class="stat">
                            <span class="stat-label">Birth Date</span>
                            <span class="stat-value"><?php echo htmlspecialchars($profile['birth_date']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php elseif(isset($_SESSION['role'])): ?>
                        <div class="stat">
                            <span class="stat-label">Role</span>
                            <span class="stat-value"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="edit-profile.php" class="btn profile-btn-accent">Edit Profile</a>
                    <a href="logout.php" class="btn">Logout</a>
                </div>
            </div>
        </div>
        
        <div class="profile-content">
            <div class="profile-tabs">
                <button class="tab-button active" data-tab="activity">Activity</button>
                <button class="tab-button" data-tab="matches">Matches</button>
                <button class="tab-button" data-tab="bookings">Bookings</button>
                <button class="tab-button" data-tab="settings">Settings</button>
            </div>
            
            <div class="tab-content">
                <!-- Activity Tab (Default Active) -->
                <div id="activity" class="tab-pane active">
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h3>No Recent Activity</h3>
                        <p>Your recent matches and bookings will appear here</p>
                        <a href="matchmaking.php" class="btn profile-btn-accent">Find a Match</a>
                    </div>
                </div>
                
                <!-- Matches Tab -->
                <div id="matches" class="tab-pane">
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 22h14"></path>
                            <path d="M5 2h14"></path>
                            <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
                            <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
                        </svg>
                        <h3>No Matches Yet</h3>
                        <p>Your past and upcoming matches will appear here</p>
                        <a href="matchmaking.php" class="btn profile-btn-accent">Find Players</a>
                    </div>
                </div>
                
                <!-- Bookings Tab -->
                <div id="bookings" class="tab-pane">
                    <h3>My Bookings</h3>
                    <div id="bookingsList">Loading...</div>
                </div>
                
                <!-- Settings Tab -->
                <div id="settings" class="tab-pane">
                    <div class="settings-form">
                        <h3>Account Settings</h3>
                        
                        <div class="account-actions">
                            <div class="action-description">
                                <h4>Edit your profile</h4>
                                <p>Update your personal information, preferences, and playing style.</p>
                            </div>
                            <a href="edit-profile.php" class="btn profile-btn-accent">Edit Profile</a>
                        </div>
                        
                        <div class="account-actions">
                            <div class="action-description">
                                <h4>Sign out of your account</h4>
                                <p>You'll need to enter your credentials when you want to log back in.</p>
                            </div>
                            <a href="logout.php" class="btn btn-danger">Logout</a>
                        </div>
                        
                        <div class="account-actions">
                            <div class="action-description">
                                <h4>Delete your account</h4>
                                <p>This will permanently delete your account and all associated data. This action cannot be undone.</p>
                            </div>
                            <a href="delete-account.php" class="btn btn-outline-danger">Delete Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        // Simple tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to current button
                    this.classList.add('active');
                    
                    // Show the corresponding tab pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });

        // Load bookings when bookings tab is activated
        document.querySelector('button[data-tab="bookings"]').addEventListener('click', loadBookings);

        async function loadBookings(){
            const list = document.getElementById('bookingsList');
            list.innerHTML = 'Loading...';
            try{
                const res = await fetch('/PadelUp/public/api/user_bookings.php');
                if(!res.ok) throw new Error('Failed to load bookings');
                const bookings = await res.json();
                if(!Array.isArray(bookings) || bookings.length === 0){
                    list.innerHTML = '<p>No bookings found.</p>';
                    return;
                }
                list.innerHTML = '';
                bookings.forEach(b => {
                    const div = document.createElement('div');
                    div.className = 'booking-row';
                    div.innerHTML = `<div><strong>${b.venue_name}</strong> — ${b.court_name}<br>
                        <span>${b.booking_date} ${b.start_time} - ${b.end_time}</span>
                        <span>Status: <b>${b.status}</b></span>
                        <span>Total: $${b.total_price}</span></div>`;
                    if(b.status !== 'cancelled' && b.status !== 'paid'){
                        const btn = document.createElement('button');
                        btn.className = 'cancel-btn';
                        btn.innerText = 'Cancel';
                        btn.onclick = async function(){
                            if(!confirm('Cancel this booking?')) return;
                            btn.disabled = true;
                            const resp = await fetch('/PadelUp/public/api/user_bookings.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ booking_id: b.booking_id })
                            });
                            if(resp.ok){
                                div.querySelector('span b').innerText = 'cancelled';
                                btn.remove();
                            }else{
                                let msg = 'Could not cancel booking';
                                try{ const er = await resp.json(); if(er && er.error) msg = er.error; }catch(_e){}
                                alert(msg);
                                btn.disabled = false;
                            }
                        };
                        div.appendChild(btn);
                    }
                    list.appendChild(div);
                });
            }catch(e){
                list.innerHTML = '<p>Error loading bookings.</p>';
            }
        }
    </script>

<?php include __DIR__ . '/partials/footer.php'; ?>