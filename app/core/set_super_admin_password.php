<?php
// One-time script to set super admin password to a known value
// IMPORTANT: Delete this file after running.

require_once __DIR__ . '/dbh.inc.php';

header('Content-Type: text/plain');

try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('DB connection not available.');
    }

    $email = 'admin@padelup.local';
    $newPasswordPlain = '123'; // requested value
    $newHash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

    // Find a super_admin (prefer the seeded email, fallback any super_admin)
    $sqlFind = "SELECT user_id,email FROM users WHERE email = ? OR role = 'super_admin' ORDER BY user_id ASC LIMIT 1";
    $stmt = $conn->prepare($sqlFind);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        // Update existing super admin
        $sqlUpd = "UPDATE users SET password_hash = ?, role = 'super_admin', email = ? WHERE user_id = ?";
        $stmt2 = $conn->prepare($sqlUpd);
        if (!$stmt2) throw new Exception('Prepare update failed: ' . $conn->error);
        $stmt2->bind_param('ssi', $newHash, $email, $row['user_id']);
        if (!$stmt2->execute()) throw new Exception('Update failed: ' . $stmt2->error);
        $stmt2->close();
        echo "Updated super admin (user_id={$row['user_id']}) with email {$email} and new password.\n";
    } else {
        // Create a new super admin
        $sqlIns = "INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?, 'super_admin')";
        $stmt3 = $conn->prepare($sqlIns);
        if (!$stmt3) throw new Exception('Prepare insert failed: ' . $conn->error);
        $name = 'Super Admin';
        $stmt3->bind_param('sss', $name, $email, $newHash);
        if (!$stmt3->execute()) throw new Exception('Insert failed: ' . $stmt3->error);
        $newId = $stmt3->insert_id;
        $stmt3->close();
        echo "Created new super admin (user_id={$newId}) with email {$email} and password.\n";
    }

    echo "Login with:\nEmail: {$email}\nPassword: {$newPasswordPlain}\n\nSECURITY: Change this password in production and delete this script (app/core/set_super_admin_password.php).\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
