<?php
header('Content-Type: application/json');

// Database connection details
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if the user is logged in and has a valid user_id (fa_id)
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User is not logged in']));
}

// Get the FA ID from the session
$fa_id = $_SESSION['user_id']; // Assuming the logged-in user is a faculty member (fa)

// SQL Query to fetch faculty details
$sql = "SELECT * FROM fa WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(['error' => 'SQL prepare failed: ' . $conn->error]));
}

$stmt->bind_param("i", $fa_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any result is returned
if ($result->num_rows > 0) {
    $fa_details = $result->fetch_assoc();
    echo json_encode($fa_details);
} else {
    echo json_encode(['error' => 'No FA details found for FA ID ' . $fa_id]);
}

$stmt->close();
$conn->close();
?>
