<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, register_no, email FROM students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $studentId = $row['id'];
        $studentDetails = json_encode($row);

        // Generate QR code
        $qrCodeFileName = "qrcodes/student_$studentId.png";
        $command = escapeshellcmd("python generate_qrcode.py '$studentDetails' '$qrCodeFileName'");
        shell_exec($command);

        // Save QR code file path to the database
        $updateSql = "UPDATE students SET qr_code_path = '$qrCodeFileName' WHERE id = $studentId";
        $conn->query($updateSql);
    }
}

$conn->close();
?>