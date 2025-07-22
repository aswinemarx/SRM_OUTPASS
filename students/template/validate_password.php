<?php
<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['valid' => false]);
    exit;
}

$username = $_SESSION['username'];
$current_password = $_POST['current_password'] ?? '';

if (!$current_password) {
    echo json_encode(['valid' => false]);
    exit;
}

// Fetch hashed password from DB
$stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['valid' => false]);
    exit;
}

// If using password_hash (recommended)
if (password_verify($current_password, $user['password'])) {
    echo json_encode(['valid' => true]);
} else {
    echo json_encode(['valid' => false]);
}
?>

//if ($current_password === $user['password']) {
//    echo json_encode(['valid' => true]);
//} else {
//    echo json_encode(['valid' => false]);
//}