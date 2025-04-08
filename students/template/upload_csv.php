<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
    $file = fopen($_FILES['csvFile']['tmp_name'], 'r');

    // Skip the header row
    fgetcsv($file);

    while (($row = fgetcsv($file)) !== false) {
        $userType = $row[0];
        $name = $row[1];
        $email = $row[2];
        $role = $row[3];
        $department = $row[4];

        if ($userType === 'student') {
            $sql = "INSERT INTO student (name, email, department) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $department);
        } else if ($userType === 'faculty') {
            $sql = "INSERT INTO faculty (name, email, position, department) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $role, $department);
        } else {
            continue; // Skip invalid rows
        }

        $stmt->execute();
    }

    fclose($file);
    echo json_encode(['message' => 'CSV file uploaded successfully']);
} else {
    echo json_encode(['error' => 'Failed to upload CSV file']);
}

$conn->close();
?>