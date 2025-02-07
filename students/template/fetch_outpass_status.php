<?php
session_start();
$servername = "localhost";
$username = "root"; // Change this to your MySQL username
$password = ""; // Change this to your MySQL password
$dbname = "srm_student_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$s_id = $_SESSION['user_id']; // Get the student ID from the session

$sql = "SELECT * FROM outpass,student WHERE s_id = ? ORDER BY outpass_id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $s_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $outpass = $result->fetch_assoc();
    echo json_encode($outpass);
} else {
    echo json_encode(['error' => 'No outpass found']);
}

$stmt->close();
$conn->close();
?>