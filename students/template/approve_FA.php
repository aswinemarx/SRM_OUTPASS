<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Ensure the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// Retrieve outpass_id and comment from POST data
if (!isset($_POST['outpass_id']) || !isset($_POST['comment'])) {
    die(json_encode(['error' => 'Outpass ID (outpass_id) or Comment (comment) not provided']));
}

$outpass_id = (int)$_POST['outpass_id'];
$comment = $conn->real_escape_string($_POST['comment']);

// SQL query to update the outpass status to 'approved' and save the comment field
$sql = "UPDATE outpass SET status = 'FA', comment = ? WHERE outpass_id = ? AND status = 'pending'";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the comment and outpass_id to the prepared statement
$stmt->bind_param("si", $comment, $outpass_id);

// Execute the statement
if (!$stmt->execute()) {
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

// Check if the update was successful
if ($stmt->affected_rows === 0) {
    die(json_encode(['error' => 'No rows updated. Outpass ID (outpass_id) may be incorrect or already approved.']));
}

// Log success message
error_log("Outpass approved successfully for outpass_id: " . $outpass_id);

// Return a success message
echo json_encode(['message' => 'Outpass approved successfully']);
?>