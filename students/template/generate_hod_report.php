<?php
require_once 'db.php';
require_once 'vendor/autoload.php'; // For PhpSpreadsheet and TCPDF

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

$hod_id = $_SESSION['user_id'] ?? $_GET['hod_id'] ?? null;
if (!$hod_id) {
    echo json_encode(['error' => 'HOD not authenticated']);
    exit;
}

// Filters
$status = $_GET['status'] ?? '';
$fa_name = $_GET['fa_name'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$order = strtoupper($_GET['order'] ?? 'ASC');
$export = $_GET['export'] ?? '';

// Default sort column and order
$sort_column = 'date_out';
$order = $order === 'DESC' ? 'DESC' : 'ASC';

// Build SQL
$sql = "SELECT 
            s.name AS student_name,
            f.name AS fa_name,
            s.r_no,
            o.date_in,
            o.date_out,
            o.status,
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

// Export to PDF
if ($export === 'pdf') {
    $pdf = new \TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SRM HOD Portal');
    $pdf->SetTitle('Outpass Report');
    $pdf->SetHeaderData('', 0, 'Outpass Report', '');
    $pdf->setHeaderFont(Array('helvetica', '', 12));
    $pdf->setFooterFont(Array('helvetica', '', 10));
    $pdf->SetDefaultMonospacedFont('helvetica');
    $pdf->SetMargins(10, 20, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Table headers
    $headers = [
        'Student Name', 'FA Name', 'Reg No', 'Date In', 'Date Out', 'Status', 'Reason'
    ];
    $html = '<h2>Outpass Report</h2><table border="1" cellpadding="4"><tr>';
    foreach ($headers as $h) {
        $html .= '<th>' . $h . '</th>';
    }
    $html .= '</tr>';

    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['student_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['fa_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['r_no']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['date_in']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['date_out']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['status']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['reason']) . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" align="center">No records found</td></tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('outpass_report.pdf', 'D');
    exit;
}

// Export to Excel
if ($export === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $headers = ['Student Name', 'FA Name', 'Reg No', 'Date In', 'Date Out', 'Status', 'Reason'];
    $col = 1;
    foreach ($headers as $header) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
        $sheet->setCellValue($cell, $header);
        $col++;
    }

    // Data
    $rowNum = 2;
    foreach ($rows as $row) {
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['student_name']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['fa_name']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['r_no']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['date_in']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['date_out']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['status']);
        $sheet->setCellValueByColumnAndRow($col++, $rowNum, $row['reason']);
        $rowNum++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="outpass_report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);
?>
