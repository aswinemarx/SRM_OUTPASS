<?php
require_once '../../resources/db_connect.php'; // Adjust path as needed

$role = $_GET['role'] ?? '';
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$r_no = $_GET['r_no'] ?? '';
$year = $_GET['year'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'submission_date';
$order = $_GET['order'] ?? 'DESC';
$export = $_GET['export'] ?? ''; // 'pdf' or 'excel'

// Build query
$sql = "SELECT u.role, s.name AS student_name, s.dept AS department, s.r_no, s.year, o.status, o.submission_date
        FROM outpass_requests o
        JOIN students s ON o.student_id = s.id
        JOIN users u ON s.user_id = u.id
        WHERE 1=1";
$params = [];

if ($role) {
    $sql .= " AND u.role = ?";
    $params[] = $role;
}
if ($department) {
    $sql .= " AND s.dept LIKE ?";
    $params[] = "%$department%";
}
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($r_no) {
    $sql .= " AND s.r_no LIKE ?";
    $params[] = "%$r_no%";
}
if ($year) {
    $sql .= " AND s.year = ?";
    $params[] = $year;
}
if ($date_from) {
    $sql .= " AND o.submission_date >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $sql .= " AND o.submission_date <= ?";
    $params[] = $date_to;
}
$allowed_sort = ['submission_date', 'student_name', 'status', 'department', 'r_no', 'year'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'submission_date';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
$sql .= " ORDER BY $sort_by $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($export === 'pdf') {
    // PDF export logic (TCPDF/FPDF)
    // ...
    exit;
}
if ($export === 'excel') {
    // Excel export logic (PhpSpreadsheet)
    // ...
    exit;
}

header('Content-Type: application/json');
echo json_encode($rows);