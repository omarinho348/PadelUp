<?php 
require_once __DIR__ . '/../controllers/UserController.php';
UserController::requireSuperAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="container admin-container">
        <div class="admin-header">
            <h1>User Management</h1>
            <p>View, search, and manage all user accounts.</p>
        </div>

        <div class="admin-toolbar">
            <input type="text" placeholder="Search users by name or email..." class="admin-search">
            <a href="#" class="btn btn-nav-primary">Add New User</a>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Register Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Row 1 -->
                    <tr>
                        <td>1</td>
                        <td>John Doe</td>
                        <td>john.doe@example.com</td>
                        <td><span class="status-pill user">User</span></td>
                        <td>2023-10-27</td>
                        <td class="actions"><a href="#" class="btn-action edit">Edit</a><a href="#" class="btn-action delete">Delete</a></td>
                    </tr>
                    <!-- Example Row 2 (Admin) -->
                    <tr>
                        <td>2</td>
                        <td><?php echo htmlspecialchars($_SESSION['FullName']); ?></td>
                        <td><?php echo htmlspecialchars($_SESSION['Email']); ?></td>
                        <td><span class="status-pill admin">Admin</span></td>
                        <td>2023-09-15</td>
                        <td class="actions"><a href="#" class="btn-action edit">Edit</a><a href="#" class="btn-action delete">Delete</a></td>
                    </tr>
                    <!-- More user rows will be populated by PHP later -->
                </tbody>
            </table>
        </div>
    </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>