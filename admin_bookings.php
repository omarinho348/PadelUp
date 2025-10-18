<?php 
include 'Includes/navbar.php'; 
include_once "includes/dbh.inc.php";

// --- SECURITY CHECK ---
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Overview - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Bookings Overview</h1>
            <p>Monitor and manage all court reservations.</p>
        </div>

        <div class="admin-toolbar">
            <input type="date" class="admin-search">
            <select class="admin-search"><option>Filter by Venue...</option></select>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Venue</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Row 1 -->
                    <tr>
                        <td>#1024</td>
                        <td>John Doe</td>
                        <td>PadelUp Center Cairo</td>
                        <td>2023-11-05, 18:00</td>
                        <td><span class="status-pill confirmed">Confirmed</span></td>
                        <td class="actions"><a href="#" class="btn-action view">View</a><a href="#" class="btn-action delete">Cancel</a></td>
                    </tr>
                    <!-- More booking rows will be populated by PHP later -->
                </tbody>
            </table>
        </div>
    </div>

<?php include 'Includes/footer.php'; ?>
</body>
</html>