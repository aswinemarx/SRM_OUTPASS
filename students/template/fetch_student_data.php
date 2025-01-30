<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$studentId = 1; // Example student ID
$sql = "SELECT id, name, register_no, email FROM students WHERE id = $studentId";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $studentData = $result->fetch_assoc();
    echo json_encode($studentData);
} else {
    echo json_encode([]);
}

$conn->close();
?>