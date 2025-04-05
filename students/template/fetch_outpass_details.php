<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

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

// Retrieve outpass ID from the GET request and ensure it's an integer
$outpass_id = isset($_GET['outpass_id']) ? (int)$_GET['outpass_id'] : 0;

if ($outpass_id <= 0) {
    die(json_encode(['error' => 'Invalid outpass ID']));
}

// SQL query to fetch outpass details
$sql = "SELECT o.reason_for_leave, o.address
        FROM outpass o 
        WHERE o.id = ?";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the outpass ID to the prepared statement
$stmt->bind_param("i", $outpass_id);

// Execute the statement
if (!$stmt->execute()) {
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

$result = $stmt->get_result();

// Check if any rows were fetched
if ($result->num_rows > 0) {
    $outpass = $result->fetch_assoc();

    // Return the outpass details as a JSON response
    header('Content-Type: application/json');
    echo json_encode($outpass);
} else {
    // If no records are found, return a message indicating this
    echo json_encode(['message' => 'No details found for this outpass']);
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>