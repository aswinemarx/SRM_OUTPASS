<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

$fa_id = $_SESSION['user_id'] ?? $_GET['fa_id'] ?? null;
if (!$fa_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Faculty Advisor not authenticated']);
    exit;
}

// Filters
$student_name = $_GET['student_name'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'submission_time';
$order = strtoupper($_GET['order'] ?? 'ASC');
$export = $_GET['export'] ?? '';

// Allowed sort columns (only those still present)
$allowed_sort = [
    'student_name', 'status', 'fa_comment', 'submission_time'
];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'submission_time';
$order = $order === 'DESC' ? 'DESC' : 'ASC';

// Build SQL
$sql = "SELECT 
            s.name AS student_name,
            o.status,
            o.comment AS fa_comment,
            CONCAT(o.date_out, ' ', o.time_out) AS submission_time
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        WHERE s.fa_id = ?";
$params = [$fa_id];

if ($student_name) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$student_name%";
}
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($date_from) {
    $sql .= " AND o.date_out >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $sql .= " AND o.date_out <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY $sort_by $order";

// Execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export PDF using TCPDF
if ($export === 'pdf') {
    $pdf = new \TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SRM FA Portal');
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

// Export Excel using PhpSpreadsheet
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
            $sheet->setCellValue($cell, $cellValue);
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