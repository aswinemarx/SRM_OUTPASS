<?php
session_start();
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$s_id = $_SESSION['user_id']; // Get the student ID from the session

// Correct SQL query with INNER JOIN
$sql = "SELECT outpass.*, student.* FROM outpass 
        INNER JOIN student ON student.id = outpass.s_id 
        WHERE student.id = ? 
        ORDER BY outpass.outpass_id DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $s_id); // Bind the student ID parameter
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
