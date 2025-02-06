<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";  // Your MySQL username
$password = "";  // Your MySQL password
$dbname = "srm_student_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Ensure the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// Retrieve outpass_id from POST data
if (!isset($_POST['outpass_id'])) {
    die(json_encode(['error' => 'Outpass ID (outpass_id) not provided']));
}

$outpass_id = (int)$_POST['outpass_id'];  // Cast to integer for security

// SQL query to update the outpass status to 'declined'
$sql = "UPDATE outpass SET status = 'denied' WHERE outpass_id = ? AND status = 'FA'";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the outpass_id to the prepared statement
$stmt->bind_param("i", $outpass_id);

// Execute the statement
if (!$stmt->execute()) {
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

// Check if the update was successful
if ($stmt->affected_rows === 0) {
    die(json_encode(['error' => 'No rows updated. Outpass ID (outpass_id) may be incorrect or already denied.']));
}

// Log success message
error_log("Outpass denied successfully for outpass_id: " . $outpass_id);

// Return a success message
echo json_encode(['message' => 'Outpass denied successfully']);
?>
