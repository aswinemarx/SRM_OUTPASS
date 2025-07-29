<?php
include 'db.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$dept = $_POST['dept'] ?? '';

if (!$name || !$email || !$dept) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Insert into users table if not exists
$stmt2 = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_type = 'fa'");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$stmt2->bind_result($exists);
$stmt2->fetch();
$stmt2->close();

if (!$exists) {
    $default_password = password_hash($email, PASSWORD_DEFAULT); // Use email as initial password
    $stmt3 = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, 'fa')");
    $stmt3->bind_param("ss", $email, $default_password);
    $stmt3->execute();
    $stmt3->close();
}

// Now insert into fa table
$stmt = $conn->prepare("INSERT INTO fa (name, email, dept) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $dept);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
$conn->close();
?>