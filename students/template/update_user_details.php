<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];

if (strpos($role, 'Student') !== false) {
    $sql = "UPDATE student SET name = ?, email = ? WHERE id = ?";
} else {
    $sql = "UPDATE faculty SET name = ?, email = ?, position = ? WHERE id = ?";
}

$stmt = $conn->prepare($sql);
if (strpos($role, 'Student') !== false) {
    $stmt->bind_param("ssi", $name, $email, $id);
} else {
    $stmt->bind_param("sssi", $name, $email, $role, $id);
}

if ($stmt->execute()) {
    echo json_encode(['message' => 'User details updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update user details']);
}

$stmt->close();
$conn->close();
?>