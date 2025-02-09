<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root"; // Your MySQL username
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

// Retrieve student ID from the session and ensure it's an integer
$student_id = (int)$_SESSION['user_id'];

// Retrieve form data
$fromDate = $_POST['fromDate'];
$fromTime = $_POST['fromTime'];
$toDate = $_POST['toDate'];
$toTime = $_POST['toTime'];
$reason = $_POST['reason'];

// Validate the dates
$fromDateTime = strtotime($fromDate . ' ' . $fromTime);
$toDateTime = strtotime($toDate . ' ' . $toTime);
$now = time();
$threeDaysFromNow = strtotime('+3 days');

if ($fromDateTime <= $now || $fromDateTime > $threeDaysFromNow) {
    die(json_encode(['error' => 'Date (Out) must be greater than the present date and lesser than 3 days from now']));
}

if ($toDateTime <= $fromDateTime) {
    die(json_encode(['error' => 'Date (In) must be greater than Date (Out)']));
}

// Insert outpass request into the database
$sql = "INSERT INTO outpass (s_id, date_out, time_out, date_in, time_in, reason_for_leave, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("isssss", $student_id, $fromDate, $fromTime, $toDate, $toTime, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Outpass request submitted successfully']);
} else {
    echo json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
