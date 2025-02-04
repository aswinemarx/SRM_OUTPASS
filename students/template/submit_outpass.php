<!-- filepath: /Users/a./Desktop/OUTPASS/SRM_OUTPASS/students/template/submit_outpass.php -->
<?php
session_start();
$servername = "localhost";
$username = "root"; // Change this to your MySQL username
$password = "pass"; // Change this to your MySQL password
$dbname = "srm_student_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $s_id = $_SESSION['user_id']; // Get the student ID from the session
    $date_out = $_POST['fromDate'];
    $time_out = $_POST['fromTime'];
    $date_in = $_POST['toDate'];
    $time_in = $_POST['toTime'];
    $reason_for_leave = $_POST['reason'];

    // Prepare the SQL query
    $stmt = $conn->prepare("INSERT INTO outpass (s_id, date_out, time_out, date_in, time_in, reason_for_leave, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isssss", $s_id, $date_out, $time_out, $date_in, $time_in, $reason_for_leave);

    if ($stmt->execute()) {
        // Successful insertion
        header("Location: HRDSystem.html?success=1"); // Redirect to HRDSystem.html with success message
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>