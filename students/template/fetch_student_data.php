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

$sql = "SELECT id, name, register_no, email FROM students";
$result = $conn->query($sql);

$studentsData = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $studentsData[] = $row;
    }
    echo json_encode($studentsData);
} else {
    echo json_encode([]);
}

$conn->close();
?>