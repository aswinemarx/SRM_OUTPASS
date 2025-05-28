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

// Retrieve HC ID from the session
$hc_id = (int)$_SESSION['user_id'];

// SQL query to fetch HC details
$sql = "SELECT name, email FROM hc WHERE id = ?";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the HC ID to the prepared statement
$stmt->bind_param("i", $hc_id);

// Execute the statement
if (!$stmt->execute()) {
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

$result = $stmt->get_result();

// Check if any rows were fetched
if ($result->num_rows > 0) {
    $hc_details = $result->fetch_assoc();

    // Return the HC details as a JSON response
    header('Content-Type: application/json');
    echo json_encode($hc_details);
} else {
    // If no records are found, return an error message
    echo json_encode(['error' => 'No HC details found']);
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>