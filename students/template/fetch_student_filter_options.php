<?php
include 'db.php';
$conn = new mysqli($host, $username, $password, $dbname);

$departments = [];
$years = [];
$sections = [];

if ($conn->connect_error) {
    echo json_encode([
        'departments' => [],
        'years' => [],
        'sections' => []
    ]);
    exit;
}

// Fetch unique departments
$res = $conn->query("SELECT DISTINCT dept FROM student WHERE dept IS NOT NULL AND dept != ''");
while ($row = $res->fetch_assoc()) {
    $departments[] = $row['dept'];
}

// Fetch unique years
$res = $conn->query("SELECT DISTINCT year FROM student WHERE year IS NOT NULL AND year != ''");
while ($row = $res->fetch_assoc()) {
    $years[] = $row['year'];
}

// Fetch unique sections
$res = $conn->query("SELECT DISTINCT section FROM student WHERE section IS NOT NULL AND section != ''");
while ($row = $res->fetch_assoc()) {
    $sections[] = $row['section'];
}

echo json_encode([
    'departments' => $departments,
    'years' => $years,
    'sections' => $sections
]);

$conn->close();
?>