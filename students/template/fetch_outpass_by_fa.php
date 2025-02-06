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

// Retrieve faculty ID from the session and ensure it's an integer
$fa_id = (int)$_SESSION['user_id']; 

// SQL query to fetch outpass data for the faculty's students
$sql = "SELECT o.*, s.name AS student_name, s.r_no 
        FROM outpass o 
        JOIN student s ON o.s_id = s.id 
        WHERE s.fa_id = ? and o.status = 'pending'";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the faculty ID to the prepared statement
$stmt->bind_param("i", $fa_id);

// Execute the statement
if (!$stmt->execute()) {
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
    // If no records are found, return a message indicating this
    echo json_encode(['message' => 'No outpass records found']);
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>
