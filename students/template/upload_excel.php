<?php
ob_clean();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php'; // Make sure PhpSpreadsheet is installed via Composer
include 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
    $spreadsheet = IOFactory::load($_FILES['excelFile']['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Check for header row
    $headerRow = array_slice($rows[0], 0, 16);

    // Normalize header and expected for comparison
    $normalize = function($arr) {
        return array_map(function($v) {
            return strtolower(trim(preg_replace('/\s+/', '', (string)$v)));
        }, $arr);
    };
    $headerNorm = $normalize($headerRow);
    $expectedNorm = $normalize(['name','email','r_no','dept','section','year','s_no','p_no','gender','hostel','fa_id','hod_id','room_number','w_mail','p_mail','address']);

    if ($headerNorm !== $expectedNorm) {
        error_log('Header: ' . var_export($headerNorm, true));
        error_log('Expected: ' . var_export($expectedNorm, true));
        echo json_encode(['error' => 'Invalid header row. Please use the provided sample file.']);
        $conn->close();
        exit;
    }

    $successCount = 0;
    $failCount = 0;
    $messages = [];

    for ($i = 1; $i < count($rows); $i++) {
        $row = array_slice($rows[$i], 0, 16); // Only use first 16 columns
        if (count($row) < 16) {
            $failCount++;
            $messages[] = "Row $i: Incomplete row (less than 16 columns).";
            continue;
        }

        list($name, $email, $r_no, $dept, $section, $year, $s_no, $p_no, $gender, $hostel, $fa_id, $hod_id, $room_number, $w_mail, $p_mail, $address) = $row;

        // All fields mandatory
        if (
            !$name || !$email || !$r_no || !$dept || !$section || !$year ||
            !$s_no || !$p_no || !$gender || !$hostel || !$fa_id || !$hod_id ||
            !$room_number || !$w_mail || !$p_mail || !$address
        ) {
            $failCount++;
            $messages[] = "Row $i: All fields are mandatory. Missing value detected.";
            continue;
        }

        // Check if email already exists in users table
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($exists);
        $stmt->fetch();
        $stmt->close();

        if ($exists) {
            $failCount++;
            $messages[] = "Row $i: Email '$email' already exists in users table. Row ignored.";
            continue;
        }

        // 1. Insert into users table
        $password_plain = $r_no . substr($p_no, -4); // r_no + last 4 digits of p_no
        $default_password = password_hash($password_plain, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, 'student')");
        $stmt->bind_param("ss", $email, $default_password);
        if (!$stmt->execute()) {
            $failCount++;
            $messages[] = "Row $i: Failed to insert into users table.";
            $stmt->close();
            continue;
        }
        $stmt->close();

        // 2. Insert into student table
        $stmt = $conn->prepare("INSERT INTO student (name, email, r_no, dept, section, year, s_no, p_no, gender, hostel, fa_id, hod_id, room_number, w_mail, p_mail, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssiiisss",
            $name, $email, $r_no, $dept, $section, $year, $s_no, $p_no, $gender, $hostel, $fa_id, $hod_id, $room_number, $w_mail, $p_mail, $address
        );
        if ($stmt->execute()) {
            $successCount++;
        } else {
            $failCount++;
            $messages[] = "Row $i: Failed to insert into student table.";
        }
        $stmt->close();
    }

    echo json_encode([
        'message' => "Upload complete. Success: $successCount, Failed: $failCount.",
        'details' => $messages
    ]);
} else {
    echo json_encode(['error' => 'Failed to upload Excel file']);
}

$conn->close();
?>