<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['valid' => false, 'success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get username (email) from respective table
switch ($user_type) {
    case 'student':
        $stmt = $pdo->prepare("SELECT email FROM student WHERE id = ?");
        break;
    case 'fa':
        $stmt = $pdo->prepare("SELECT email FROM fa WHERE id = ?");
        break;
    case 'hod':
        $stmt = $pdo->prepare("SELECT email FROM hod WHERE id = ?");
        break;
    case 'hc':
        $stmt = $pdo->prepare("SELECT email FROM hc WHERE id = ?");
        break;
    default:
        echo json_encode(['valid' => false, 'success' => false, 'error' => 'Invalid user type']);
        exit;
}
$stmt->execute([$user_id]);
$user_row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_row) {
    echo json_encode(['valid' => false, 'success' => false, 'error' => 'User not found']);
    exit;
}

$username = $user_row['email'];

// Step 1: Validate current password
if (isset($_POST['current_password'])) {
    $current_password = $_POST['current_password'];
    $stmt2 = $pdo->prepare("SELECT password FROM users WHERE username = ? AND user_type = ?");
    $stmt2->execute([$username, $user_type]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($current_password, $user['password'])) {
        echo json_encode(['valid' => true]);
    } else {
        echo json_encode(['valid' => false, 'error' => 'Incorrect current password']);
    }
    exit;
}

// Step 2: Change password
if (isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
        exit;
    }
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt3 = $pdo->prepare("UPDATE users SET password = ? WHERE username = ? AND user_type = ?");
    if ($stmt3->execute([$hashed_password, $username, $user_type])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password.']);
    }
    exit;
}

// If neither, return error
echo json_encode(['valid' => false, 'success' => false, 'error' => 'Invalid request']);
exit;
?>