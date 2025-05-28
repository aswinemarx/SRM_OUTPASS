<?php
// filepath: e:\outpass\SRM_OUTPASS\students\template\fetch_sections.php
include 'db.php';

$department_id = $_GET['department_id'];
$year = $_GET['year'];

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT DISTINCT section FROM students WHERE department_id = ? AND year = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $department_id, $year);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row['section'];
}

echo json_encode($sections);
$stmt->close();
$conn->close();
?>