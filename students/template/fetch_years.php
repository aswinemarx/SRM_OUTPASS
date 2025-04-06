<?php
// filepath: e:\outpass\SRM_OUTPASS\students\template\fetch_years.php
include 'db.php';

$department_id = $_GET['department_id'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT DISTINCT year FROM students WHERE department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

$years = [];
while ($row = $result->fetch_assoc()) {
    $years[] = $row['year'];
}

echo json_encode($years);
$stmt->close();
$conn->close();
?>