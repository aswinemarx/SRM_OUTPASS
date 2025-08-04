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
$export = $_GET['export'] ?? '';

// Default: sort by date_out ascending
$sort_by = 'date_out';
$order = 'ASC';

// Build SQL (add date_in, date_out, r_no fields)
$sql = "SELECT 
            s.name AS student_name,
            o.date_in,
            o.date_out,
            s.r_no,
            o.status,
            o.comment AS fa_comment
        FROM outpass o
        JOIN student s ON o.s_id = s.id
        WHERE s.fa_id = ?";
$params = [$fa_id];

// Filters
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

    // Table headers in required order and human-readable
    $headers = [
        'student_name' => 'Student Name',
        'date_in'      => 'Date In',
        'date_out'     => 'Date Out',
        'r_no'         => 'Reg No',
        'status'       => 'Status',
        'fa_comment'   => 'FA Comment'
    ];

    $html = '<h2>Outpass Report</h2>';
    $html .= '<table border="1" cellpadding="4"><tr>';
    foreach ($headers as $h) {
        $html .= '<th>' . $h . '</th>';
    }
    $html .= '</tr>';

    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (array_keys($headers) as $key) {
                $html .= '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="' . count($headers) . '" align="center">No records found</td></tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('outpass_report.pdf', 'D');
    exit;
}

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);
?>
