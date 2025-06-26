<?php
require_once 'db.php'; // Adjust path as needed
session_start();

$hc_id = $_SESSION['hc_id'] ?? $_GET['hc_id'] ?? null;
if (!$hc_id) {
    echo json_encode(['error' => 'Hostel Coordinator not authenticated']);
    exit;
}

$hostel_block = $_GET['hostel_block'] ?? '';
$status = $_GET['status'] ?? '';
$approval_date = $_GET['approval_date'] ?? '';

$sql = "SELECT s.name AS student_name, s.hostel_block, o.approval_date, o.return_date, o.return_time, o.status
        FROM outpass_requests o
        JOIN students s ON o.student_id = s.id
        WHERE o.hc_id = ? AND o.hod_approved = 1";
$params = [$hc_id];

if ($hostel_block) {
    $sql .= " AND s.hostel_block = ?";
    $params[] = $hostel_block;
}
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($approval_date) {
    $sql .= " AND o.approval_date = ?";
    $params[] = $approval_date;
}

$sql .= " ORDER BY s.name ASC"; // Default sort

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($rows);