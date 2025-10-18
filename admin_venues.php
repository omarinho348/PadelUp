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
    <title>Venue Management - PadelUp</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>Venue Management</h1>
            <p>Add, edit, and manage venues and their courts.</p>
        </div>

        <div class="admin-toolbar">
            <input type="text" placeholder="Search venues..." class="admin-search">
            <a href="#" class="btn btn-nav-primary">Add New Venue</a>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Venue Name</th>
                        <th>Location</th>
                        <th>Courts</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Row 1 -->
                    <tr>
                        <td>1</td>
                        <td>PadelUp Center Cairo</td>
                        <td>Cairo, Egypt</td>
                        <td>4</td>
                        <td><span class="status-pill active">Active</span></td>
                        <td class="actions"><a href="#" class="btn-action edit">Edit</a><a href="#" class="btn-action delete">Delete</a></td>
                    </tr>
                    <!-- More venue rows will be populated by PHP later -->
                </tbody>
            </table>
        </div>
    </div>

<?php include 'Includes/footer.php'; ?>
</body>
</html>