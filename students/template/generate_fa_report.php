<?php
require_once 'db.php'; // Adjust path as needed
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

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);