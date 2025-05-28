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

// Check the request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch approved outpasses
    $sql = "SELECT o.outpass_id AS outpass_id, s.name AS student_name, o.reason_for_leave, o.date_out, o.date_in
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        WHERE o.status != 'denied' AND o.status != 'Approved' AND o.date_out >= CURDATE()";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $outpasses = [];
        while ($row = $result->fetch_assoc()) {
            $outpasses[] = $row;
        }
        echo json_encode($outpasses);
    } else {
        echo json_encode(['message' => 'No unapproved outpasses found']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['outpass_id']) || !isset($_POST['action'])) {
        echo json_encode(['error' => 'Outpass ID or action not provided']);
        exit;
    }

    $outpass_id = intval($_POST['outpass_id']);
    $action = $_POST['action'];
    $allowed_statuses = ['approve' => 'Approved', 'decline' => 'Denied'];
    $new_status = $allowed_statuses[$action] ?? null;

    if (!$new_status) {
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }

    $sql = "UPDATE outpass SET status = ? WHERE outpass_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("si", $new_status, $outpass_id);

    // Only show error if the update is NOT executed
    if ($stmt->execute()) {
        echo "<script>alert('Outpass successfully $new_status');</script>";
        $stmt->close();
        $conn->close();
        exit;
    } else {
        echo "<script>alert('Failed to update outpass status');</script>";
        $stmt->close();
        $conn->close();
        exit;
    }
}