<?php
session_start();

// Database connection details
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if user is logged in (session exists)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User is not logged in']);
    exit;
}

// Assuming session has 'user_id' which corresponds to the student ID
$student_id = $_SESSION['user_id'];

// Prepare the SQL query to fetch student details
$stmt = $conn->prepare("SELECT name, r_no, email FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id); // Use "i" for integer binding
$stmt->execute();
$stmt->bind_result($name, $r_no, $email);
$stmt->fetch();

// Check if any student data was fetched
if ($name) {
    $student = array(
        "name" => $name,
        "r_no" => $r_no,
        "email" => $email
    );
    echo json_encode($student);
} else {
    echo json_encode(['error' => 'No student details found for the provided user ID']);
}

$stmt->close();
$conn->close();
?>
