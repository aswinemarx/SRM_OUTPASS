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

// Start the session to access session variables
session_start();

// Check if the user is logged in and has a valid user_id (hod_id)
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User is not logged in']));
}

// Get the HOD ID from the session
$hod_id = $_SESSION['user_id']; // Assuming the logged-in user is an HOD

// SQL Query to fetch HOD details
$sql = "SELECT * FROM hod WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(['error' => 'SQL prepare failed: ' . $conn->error]));
}

$stmt->bind_param("i", $hod_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any result is returned
if ($result->num_rows > 0) {
    $hod_details = $result->fetch_assoc();
    echo json_encode($hod_details);
} else {
    echo json_encode(['error' => 'No HOD details found for HOD ID ' . $hod_id]);
}

$stmt->close();
$conn->close();
?>
