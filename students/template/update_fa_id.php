<?php

include 'db.php';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'DB connection failed']));
}

$mode = $_POST['mode'] ?? '';
if ($mode === 'filtered') {
    $dept = $_POST['dept'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $new_fa_id = $_POST['new_fa_id'] ?? '';

    if ($dept && $year && $section && $new_fa_id) {
        $stmt = $conn->prepare("UPDATE student SET fa_id = ? WHERE dept = ? AND year = ? AND section = ?");
        $stmt->bind_param("isss", $new_fa_id, $dept, $year, $section);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    // fallback: old logic (if needed)
    $old_fa_id = $_POST['old_fa_id'] ?? '';
    $new_fa_id = $_POST['new_fa_id'] ?? '';

    if ($old_fa_id && $new_fa_id) {
        $stmt = $conn->prepare("UPDATE student SET fa_id = ? WHERE fa_id = ?");
        $stmt->bind_param("ii", $new_fa_id, $old_fa_id);
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
}

$conn->close();
?>