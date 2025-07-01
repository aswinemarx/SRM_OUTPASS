<?php
require_once 'db.php'; // Use your db.php for database connection

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
    require_once('fpdf/fpdf.php'); // Adjust path as needed

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);

    // Table header
    foreach(array_keys($rows[0]) as $header) {
        $pdf->Cell(40,10,ucwords(str_replace('_',' ',$header)),1);
    }
    $pdf->Ln();

    // Table data
    $pdf->SetFont('Arial','',10);
    foreach($rows as $row) {
        foreach($row as $cell) {
            $pdf->Cell(40,10,$cell,1);
        }
        $pdf->Ln();
    }

    $pdf->Output('D', 'report.pdf');
    exit;
}
if ($export === 'excel') {
    require 'vendor/autoload.php'; // Adjust path as needed
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $col = 1;
    foreach(array_keys($rows[0]) as $header) {
        $sheet->setCellValueByColumnAndRow($col, 1, ucwords(str_replace('_',' ',$header)));
        $col++;
    }

    // Data
    $rowNum = 2;
    foreach($rows as $row) {
        $col = 1;
        foreach($row as $cell) {
            $sheet->setCellValueByColumnAndRow($col, $rowNum, $cell);
            $col++;
        }
        $rowNum++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

header('Content-Type: application/json');
echo json_encode($rows);