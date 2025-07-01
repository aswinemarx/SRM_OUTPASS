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

$export = $_GET['export'] ?? null;
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

header('Content-Type: application/json');
echo json_encode($rows);