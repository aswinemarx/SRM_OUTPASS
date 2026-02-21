<?php
// Include your DB connection here
include 'db.php'; // Adjust path if needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Use Composer autoload like approve_HOD.php

$r_no = $_POST['r_no'] ?? '';
$p_no = $_POST['s_no'] ?? '';
$email = $_POST['email'] ?? '';

if (!$r_no || !$p_no || !$email) {
    exit('All fields are required.');
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) exit('DB connection failed.');

$stmt = $conn->prepare("SELECT id FROM student WHERE r_no = ? AND s_no = ? AND email = ?");
$stmt->bind_param("sss", $r_no, $p_no, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    // Generate new password
    $new_password = bin2hex(random_bytes(4)); // 8-char random password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update users table
    $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND user_type = 'student'");
    $update->bind_param("ss", $hashed_password, $email);
    $update->execute();

    // Send email using PHPMailer (same as approve_HOD.php)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '11g13abinith@gmail.com'; // Your Gmail email address
        $mail->Password = 'ewof lugy ksja ikbw';    // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@srmhostelportal.com', 'SRM Hostel Portal');
        $mail->addAddress($email);

        $mail->Subject = 'SRM Portal Password Reset';
        $mail->Body = "Dear Student,\n\nYour password has been reset. Your new password is: $new_password\n\nPlease log in and change your password immediately.";

        $mail->send();
        echo 'A new password has been sent to your college email.';
    } catch (Exception $e) {
        echo 'Failed to send email. Please contact admin.';
    }
} else {
    echo 'Details not found or incorrect.';
}
$stmt->close();
$conn->close();
?>