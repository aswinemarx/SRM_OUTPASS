<?php
// filepath: e:\outpass\SRM_OUTPASS\students\template\fetch_departments.php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT id, name FROM departments"; // Replace with your table name
$result = $conn->query($sql);

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

echo json_encode($departments);
$conn->close();
?>