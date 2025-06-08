<?php
include 'db.php';

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if (!$id || !$name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Get old email for this HOD
    $stmt0 = $conn->prepare("SELECT email FROM hod WHERE id = ?");
    $stmt0->bind_param("i", $id);
    $stmt0->execute();
    $stmt0->bind_result($old_email);
    $stmt0->fetch();
    $stmt0->close();

    if (!$old_email) {
        throw new Exception("HOD record not found.");
    }

    // 2. Insert new user if not exists
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_type = 'hod'");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->bind_result($exists);
    $stmt_check->fetch();
    $stmt_check->close();

    if (!$exists) {
        // You may want to set a default password or copy from old user
        $default_password = password_hash('changeme', PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, 'hod')");
        $stmt_insert->bind_param("ss", $email, $default_password);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    // 3. Update hod table (child table)
    $stmt2 = $conn->prepare("UPDATE hod SET name = ?, email = ? WHERE id = ?");
    $stmt2->bind_param("ssi", $name, $email, $id);
    $stmt2->execute();
    $stmt2->close();

    // 4. Delete old user if email changed
    if ($old_email !== $email) {
        $stmt_del = $conn->prepare("DELETE FROM users WHERE username = ? AND user_type = 'hod'");
        $stmt_del->bind_param("s", $old_email);
        $stmt_del->execute();
        $stmt_del->close();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
