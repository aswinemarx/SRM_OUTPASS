<?php
<?php
// Include your DB connection here
include 'db.php'; // Adjust path if needed

$r_no = $_POST['r_no'] ?? '';
$p_no = $_POST['p_no'] ?? '';
$w_mail = $_POST['w_mail'] ?? '';

if (!$r_no || !$p_no || !$w_mail) {
    exit('All fields are required.');
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) exit('DB connection failed.');

$stmt = $conn->prepare("SELECT id FROM student WHERE r_no = ? AND p_no = ? AND w_mail = ?");
$stmt->bind_param("sss", $r_no, $p_no, $w_mail);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    // Generate new password
    $new_password = bin2hex(random_bytes(4)); // 8-char random password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update users table
    $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND user_type = 'student'");
    $update->bind_param("ss", $hashed_password, $r_no);
    $update->execute();

    // Send email
    require 'PHPMailer/PHPMailerAutoload.php'; // Adjust path as needed
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com'; // Set your email
        $mail->Password = 'your_app_password';    // Set your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@srmhostelportal.com', 'SRM Hostel Portal');
        $mail->addAddress($w_mail);

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