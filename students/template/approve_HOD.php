<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader (adjust the path if you're using Composer)
require 'vendor/autoload.php'; // Adjust this path

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

// Retrieve outpass_id from POST data
if (!isset($_POST['outpass_id'])) {
    die(json_encode(['error' => 'Outpass ID (outpass_id) not provided']));
}

$outpass_id = (int)$_POST['outpass_id'];

// SQL query to update the outpass status to 'approved'
$sql = "UPDATE outpass SET status = 'Approved' WHERE outpass_id = ? AND status = 'HC'";

$stmt = $conn->prepare($sql);

// Check if the statement preparation was successful
if (!$stmt) {
    die(json_encode(['error' => 'SQL Prepare failed: ' . $conn->error]));
}

// Bind the outpass_id to the prepared statement
$stmt->bind_param("i", $outpass_id);

// Execute the statement
if (!$stmt->execute()) {
    die(json_encode(['error' => 'SQL Execute failed: ' . $stmt->error]));
}

// Check if the update was successful
if ($stmt->affected_rows === 0) {
    // Log failure to update
    error_log("No rows updated. Outpass ID: " . $outpass_id . " may be incorrect or already approved.");
    die(json_encode(['error' => 'No rows updated. Outpass ID (outpass_id) may be incorrect or already approved.']));
}

// Log success message for status update
error_log("Outpass approved successfully for outpass_id: " . $outpass_id);

// Get the outpass details for sending the email
$outpass_sql = "SELECT outpass.*, student.* FROM outpass 
                INNER JOIN student ON student.id = outpass.s_id 
                WHERE outpass.outpass_id = ?";
$outpass_stmt = $conn->prepare($outpass_sql);
$outpass_stmt->bind_param("i", $outpass_id);
$outpass_stmt->execute();
$outpass_result = $outpass_stmt->get_result();

if ($outpass_result->num_rows > 0) {
    $outpass = $outpass_result->fetch_assoc();

    // Set up PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configure PHPMailer for SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to use
        $mail->SMTPAuth = true;
        $mail->Username = '11g13abinith@gmail.com'; // Your Gmail email address
        $mail->Password = 'ewof lugy ksja ikbw'; // Your Gmail password (use app password if 2FA is enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@srmhostelportal.com', 'SRM Hostel Portal');
        $mail->addAddress($outpass['p_mail']);
        $mail->addAddress($outpass['w_mail']);

        // Email content
        $mail->Subject = "Outpass Approved for " . $outpass['name'];
        $message = "Dear Parent/Warden,\n\n";
        $message .= "We are pleased to inform you that the outpass request for " . $outpass['name'] . " has been approved.\n\n";
        $message .= "Here are the details of the outpass:\n";
        $message .= "Student Name: " . $outpass['name'] . "\n";
        $message .= "Register Number: " . $outpass['r_no'] . "\n";
        $message .= "Hostel Name: " . $outpass['hostel'] . "\n";
        $message .= "Student's Contact Number: " . $outpass['s_no'] . "\n";
        $message .= "Out Time: " . $outpass['date_out'] . " " . $outpass['time_out'] . "\n";
        $message .= "In Time: " . $outpass['date_in'] . " " . $outpass['time_in'] . "\n";
        $message .= "Address (for temporary stay): " . $outpass['address'] . "\n";
        $message .= "Reason for Temporary Stay: " . $outpass['reason_for_leave'] . "\n\n";
        $message .= "Should you have any concerns or need further clarification, please feel free to contact us at [College's Contact Number] or email us at [College's Email].\n\n";
        $message .= "Thank you for your understanding and cooperation.\n\n";
        $message .= "Best regards,\n";
        $message .= "[Your Full Name]\n";
        $message .= "[Your Position]\n";
        $message .= "[College Name]\n";
        $message .= "[Contact Information]";


        $mail->Body = $message;

        // Send the email
        $mail->send();
        echo json_encode(['message' => 'Outpass approved successfully and email sent']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to send email. Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['error' => 'Outpass not found']);
}

$stmt->close();
$conn->close();

?>
