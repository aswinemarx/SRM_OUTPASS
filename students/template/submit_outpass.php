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

// Retrieve student ID from the session and ensure it's an integer
$student_id = (int)$_SESSION['user_id'];

// Retrieve form data
$fromDate = $_POST['fromDate'];
$fromTime = $_POST['fromTime'];
$toDate = $_POST['toDate'];
$toTime = $_POST['toTime'];
$reason = $_POST['reason'];
$addressType = $_POST['addressType'];
// Fetch home address if address type is home
if ($addressType === 'home') {
    $sql = "SELECT address FROM student WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($address);
    $stmt->fetch();
    $stmt->close();
} else {
    $address = isset($_POST['address']) ? $_POST['address'] : null; // Retrieve the address from the form data
}

// Convert the 'fromTime' and 'toTime' to 24-hour format (if needed)
$fromTime24 = date("H:i", strtotime($fromTime));
$toTime24 = date("H:i", strtotime($toTime));

// Convert 12-hour format to 24-hour format (including the date)
$fromDateTime = strtotime($fromDate . ' ' . $fromTime24);
$toDateTime = strtotime($toDate . ' ' . $toTime24);

$now = time();
$threeDaysFromNow = strtotime('+3 days');

if ($fromDateTime <= $now || $fromDateTime > $threeDaysFromNow) {
    die(json_encode(['error' => 'Date (Out) must be greater than the present date and lesser than 3 days from now']));
}

if ($toDateTime <= $fromDateTime) {
    die(json_encode(['error' => 'Date (In) must be greater than Date (Out)']));
}

// Validate the times
$validStartTime = strtotime("06:00"); // 6:00 AM
$validEndTime = strtotime("21:00"); // 9:00 PM (inclusive)

// Allow 9:00 PM as valid time by adjusting comparison to ensure it's inclusive of 9:00 PM
if (date("H:i", $fromDateTime) < date("H:i", $validStartTime) || date("H:i", $fromDateTime) > date("H:i", $validEndTime)) {
    die(json_encode(['error' => 'Time (Out) must be between 6 AM and 9 PM']));
}

if (date("H:i", $toDateTime) < date("H:i", $validStartTime) || date("H:i", $toDateTime) > date("H:i", $validEndTime)) {
    die(json_encode(['error' => 'Time (In) must be between 6 AM and 9 PM']));
}

// Insert outpass request into the database
$sql = "INSERT INTO outpass (s_id, date_out, time_out, date_in, time_in, reason_for_leave, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("issssss", $student_id, $fromDate, $fromTime, $toDate, $toTime, $reason, $address);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Outpass request submitted successfully']);
} else {
    echo json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
