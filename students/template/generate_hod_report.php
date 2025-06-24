<?php
<?php
require_once '../../resources/db_connect.php'; // Adjust path as needed
session_start();

$hod_id = $_SESSION['hod_id'] ?? $_GET['hod_id'] ?? null;
if (!$hod_id) {
    echo json_encode(['error' => 'HOD not authenticated']);
    exit;
}

// Fetch HOD's department
$stmt = $pdo->prepare("SELECT dept FROM hods WHERE id = ?");
$stmt->execute([$hod_id]);
$dept = $stmt->fetchColumn();
if (!$dept) {
    echo json_encode(['error' => 'Department not found']);
    exit;
}

// Filters
$status = $_GET['status'] ?? '';
$fa_name = $_GET['fa_name'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'submitted_date';
$order = $_GET['order'] ?? 'DESC';
$export = $_GET['export'] ?? ''; // 'pdf' or 'excel'

// Build query
$sql = "SELECT s.name AS student_name, o.submitted_date, o.status, f.name AS fa_name, o.reason
        FROM outpass_requests o
        JOIN students s ON o.student_id = s.id
        JOIN faculty_advisors f ON o.fa_id = f.id
        WHERE s.dept = ?";
$params = [$dept];

if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($fa_name) {
    $sql .= " AND f.name LIKE ?";
    $params[] = "%$fa_name%";
}
if ($date_from) {
    $sql .= " AND o.submitted_date >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $sql .= " AND o.submitted_date <= ?";
    $params[] = $date_to;
}
$allowed_sort = ['student_name', 'submitted_date', 'status', 'reason'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'submitted_date';
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