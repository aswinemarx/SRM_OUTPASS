<?php
require_once '../../resources/db_connect.php'; // Adjust path as needed
session_start();

$fa_id = $_SESSION['fa_id'] ?? $_GET['fa_id'] ?? null;
if (!$fa_id) {
    echo json_encode(['error' => 'Faculty Advisor not authenticated']);
    exit;
}

// Get filters/sort from GET/POST
$student_name = $_GET['student_name'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$decision = $_GET['decision'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'submission_time';
$order = $_GET['order'] ?? 'DESC';
$export = $_GET['export'] ?? ''; // 'pdf' or 'excel'
$r_no = $_GET['r_no'] ?? '';
$date_out = $_GET['date_out'] ?? '';
$date_in = $_GET['date_in'] ?? '';
$reason = $_GET['reason'] ?? '';

// Build query with filters
$sql = "SELECT s.name AS student_name, o.*, f.comment AS fa_comment
        FROM outpass_requests o
        JOIN students s ON o.student_id = s.id
        LEFT JOIN fa_approval f ON o.id = f.outpass_id
        WHERE o.fa_id = ?";
$params = [$fa_id];

if ($student_name) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$student_name%";
}
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($decision) {
    $sql .= " AND f.decision = ?";
    $params[] = $decision;
}
if ($date_from) {
    $sql .= " AND o.submission_time >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $sql .= " AND o.submission_time <= ?";
    $params[] = $date_to;
}
if ($r_no) {
    $sql .= " AND s.r_no LIKE ?";
    $params[] = "%$r_no%";
}
if ($date_out) {
    $sql .= " AND o.date_out = ?";
    $params[] = $date_out;
}
if ($date_in) {
    $sql .= " AND o.date_in = ?";
    $params[] = $date_in;
}
if ($reason) {
    $sql .= " AND o.reason_for_leave LIKE ?";
    $params[] = "%$reason%";
}

// Update allowed_sort
$allowed_sort = [
    'student_name', 'submission_time', 'status', 'fa_comment',
    'r_no', 'date_out', 'date_in', 'reason_for_leave'
];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'submission_time';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
$sql .= " ORDER BY $sort_by $order";

// Prepare and execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($export === 'pdf') {
    // Use a PDF library like TCPDF or FPDF to generate PDF
    // ...PDF generation code here...
    exit;
}
if ($export === 'excel') {
    // Use a library like PhpSpreadsheet to generate Excel
    // ...Excel generation code here...
    exit;
}

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);