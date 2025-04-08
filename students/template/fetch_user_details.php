<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$id = $_POST['id'];
$userType = $_POST['userType'];

if ($userType === 'student') {
    $sql = "SELECT id, name, email, 'Student' AS role FROM student WHERE id = ?";
} else if ($userType === 'faculty') {
    $sql = "SELECT id, name, email, position AS role FROM faculty WHERE id = ?";
} else {
    die(json_encode(['error' => 'Invalid user type']));
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>