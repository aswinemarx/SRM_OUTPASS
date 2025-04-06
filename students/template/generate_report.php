<?php
// filepath: e:\outpass\SRM_OUTPASS\students\template\generate_report.php
session_start();
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];
$department = $_POST['department'];
$year = $_POST['year'];
$section = $_POST['section'];
$registerNumber = $_POST['registerNumber'];

$sql = "SELECT s.register_number, s.name, s.department, s.year, s.section, o.date_out, o.date_in, o.reason_for_leave 
        FROM students s 
        JOIN outpass o ON s.id = o.s_id 
        WHERE o.date_out BETWEEN ? AND ?";

$params = [$fromDate, $toDate];
$types = "ss";

if (!empty($department)) {
    $sql .= " AND s.department = ?";
    $params[] = $department;
    $types .= "s";
}

if (!empty($year)) {
    $sql .= " AND s.year = ?";
    $params[] = $year;
    $types .= "i";
}

if (!empty($section)) {
    $sql .= " AND s.section = ?";
    $params[] = $section;
    $types .= "s";
}

if (!empty($registerNumber)) {
    $sql .= " AND s.register_number = ?";
    $params[] = $registerNumber;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reportData = [];
while ($row = $result->fetch_assoc()) {
    $reportData[] = $row;
}

echo json_encode($reportData);
$stmt->close();
$conn->close();
?>