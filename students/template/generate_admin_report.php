<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Filters
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$r_no = $_GET['r_no'] ?? '';
$year = $_GET['year'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'submission_date';
$order = strtoupper($_GET['order'] ?? 'ASC');
$export = $_GET['export'] ?? '';

// Allowed sort columns
$allowed_sort = ['submission_date', 'student_name', 'status', 'department', 'r_no', 'year'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'submission_date';
$order = $order === 'DESC' ? 'DESC' : 'ASC';

// Build query
$sql = "SELECT 
            s.name AS student_name,
            s.dept AS department,
            s.r_no,
            s.year,
            o.status,
            o.date_out AS submission_date
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        WHERE 1=1";
$params = [];

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
    $pdf->SetAuthor('SRM Admin Portal');
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