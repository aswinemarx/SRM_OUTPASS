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
    error_log("Session user_id is not set.");
    die(json_encode(['error' => 'User not logged in']));
}

// Retrieve HC ID from the session and ensure it's an integer
$hc_id = (int)$_SESSION['user_id'];

// Determine gender condition based on HC ID
$gender_condition = ($hc_id === 1) ? "s.gender = 'male'" : (($hc_id === 2) ? "s.gender = 'female'" : null);

if (!$gender_condition) {
    error_log("Invalid HC ID: " . $hc_id);
    die(json_encode(['error' => 'Invalid HC ID']));
}

// SQL query to fetch outpass data for the HC's students
$sql = "SELECT o.*, s.name AS student_name, s.r_no, s.p_no, s.gender, o.reason_for_leave, f.name AS faculty_name
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        JOIN fa f ON s.fa_id = f.id
        WHERE $gender_condition AND o.status = 'FA' AND CONCAT(o.date_out, ' ', o.time_out) > NOW()";

error_log("SQL Query: " . $sql);

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    error_log("SQL Prepare failed: " . $conn->error);
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Execute the statement
if (!$stmt->execute()) {
    error_log("SQL Execute failed: " . $stmt->error);
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

$result = $stmt->get_result();

// Check if any rows were fetched
if ($result->num_rows > 0) {
    $outpasses = [];
    
    // Fetch the rows and add them to the outpasses array
    while ($row = $result->fetch_assoc()) {
        $outpasses[] = $row;
    }

    // Return the outpass data as a JSON response
    header('Content-Type: application/json');
    echo json_encode($outpasses);
} else {
    error_log("No outpass records found.");
    // If no records are found, return a message indicating this
    echo json_encode(['message' => 'No outpass records found']);
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>