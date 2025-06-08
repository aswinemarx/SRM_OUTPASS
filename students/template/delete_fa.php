<?php
include 'db.php';

$fa_id = $_POST['fa_id'] ?? '';
$check_only = $_POST['check_only'] ?? '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Check if any student is assigned to this FA
$stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE fa_id = ?");
$stmt->bind_param("i", $fa_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($check_only) {
    echo json_encode(['assigned' => $count > 0]);
    $conn->close();
    exit;
}

if ($count > 0) {
    echo json_encode(['success' => false, 'error' => 'FA assigned to students']);
    $conn->close();
    exit;
}

// Delete from fa table
$stmt = $conn->prepare("DELETE FROM fa WHERE id = ?");
$stmt->bind_param("i", $fa_id);
$stmt->execute();
$stmt->close();

// Optionally, delete from users table if you want
$stmt2 = $conn->prepare("DELETE FROM users WHERE user_type = 'fa' AND username = (SELECT email FROM fa WHERE id = ?)");
$stmt2->bind_param("i", $fa_id);
$stmt2->execute();
$stmt2->close();

echo json_encode(['success' => true]);
$conn->close();
?>