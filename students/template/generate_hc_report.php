<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Endpoint: Fetch unique hostel blocks for dropdown
if (isset($_GET['get_hostel_blocks'])) {
    header('Content-Type: application/json');
    $stmt = $pdo->query("SELECT DISTINCT hostel FROM student WHERE hostel IS NOT NULL AND hostel != '' ORDER BY hostel ASC");
    $blocks = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($blocks);
    exit;
}

// Authentication
$hc_id = $_SESSION['user_id'] ?? $_GET['hc_id'] ?? null;
if (!$hc_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Hostel Coordinator not authenticated']);
    exit;
}

// Filters
$hostel_block = $_GET['hostel_block'] ?? '';
$status = $_GET['status'] ?? '';
$date_out_from = $_GET['date_out_from'] ?? '';
$date_out_to = $_GET['date_out_to'] ?? '';
$export = $_GET['export'] ?? '';

// Build SQL
$sql = "SELECT 
            s.name AS student_name,
            s.r_no AS register_no,
            s.hostel AS hostel_block,
            o.date_out,
            o.date_in,
            o.status
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        WHERE 1=1";
$params = [];

if ($hostel_block) {
    $sql .= " AND s.hostel = ?";
    $params[] = $hostel_block;
}
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($date_out_from) {
    $sql .= " AND o.date_out >= ?";
    $params[] = $date_out_from;
}
if ($date_out_to) {
    $sql .= " AND o.date_out <= ?";
    $params[] = $date_out_to;
}

$sql .= " ORDER BY s.name ASC";

// Execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export Excel
if ($export === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $col = 1;
    foreach(array_keys($rows[0]) as $header) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
        $sheet->setCellValue($cell, ucwords(str_replace('_',' ',$header)));
        $col++;
    }

    // Data
    $rowNum = 2;
    foreach($rows as $row) {
        $col = 1;
        foreach($row as $cellValue) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $rowNum;
            $sheet->setCellValue($cell, $cellValue ?? '');
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

// Export PDF using TCPDF
if ($export === 'pdf') {
    $pdf = new \TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SRM HC Portal');
    $pdf->SetTitle('Outpass Report');
    $pdf->SetHeaderData('', 0, 'Outpass Report', '');
    $pdf->setHeaderFont(Array('helvetica', '', 12));
    $pdf->setFooterFont(Array('helvetica', '', 10));
    $pdf->SetDefaultMonospacedFont('helvetica');
    $pdf->SetMargins(10, 20, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Build HTML table
    $html = '<h2>Outpass Report</h2><table border="1" cellpadding="4"><tr>';
    foreach(array_keys($rows[0]) as $header) {
        $html .= '<th>' . ucwords(str_replace('_',' ',$header)) . '</th>';
    }
    $html .= '</tr>';
    foreach($rows as $row) {
        $html .= '<tr>';
        foreach($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('report.pdf', 'D');
    exit;
}

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);