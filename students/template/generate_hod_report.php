<?php
require_once 'db.php';
session_start();

$hod_id = $_SESSION['user_id'] ?? $_GET['hod_id'] ?? null;
if (!$hod_id) {
    echo json_encode(['error' => 'HOD not authenticated']);
    exit;
}

// Filters (no default values for debugging/testing)
$status = $_GET['status'] ?? '';
$fa_name = $_GET['fa_name'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'date_out';
$order = strtoupper($_GET['order'] ?? 'ASC');
$export = $_GET['export'] ?? '';

// Validate sort_by
$allowed_sort = ['student_name', 'submitted_date', 'date_out', 'status', 'reason', 'reason_for_leave'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'date_out';
$order = $order === 'DESC' ? 'DESC' : 'ASC';

// Map 'submitted_date' to 'date_out' and 'reason' to 'reason_for_leave'
$sort_column = $sort_by;
if ($sort_by === 'submitted_date') $sort_column = 'date_out';
if ($sort_by === 'reason') $sort_column = 'reason_for_leave';

// Build SQL
$sql = "SELECT 
            s.name AS student_name,
            o.date_out AS submitted_date,
            o.status,
            f.name AS fa_name,
            o.reason_for_leave AS reason
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        JOIN fa f ON s.fa_id = f.id
        WHERE s.hod_id = ?";

$params = [$hod_id];

// Apply filters
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($fa_name) {
    $sql .= " AND f.name LIKE ?";
    $params[] = "%$fa_name%";
}
if ($date_from) {
    $sql .= " AND o.date_out >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $sql .= " AND o.date_out <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY $sort_column $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export logic (optional)
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