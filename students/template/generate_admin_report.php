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
$order = strtoupper($_GET['order'] ?? 'ASC');
$export = $_GET['export'] ?? '';

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

$sql .= " ORDER BY o.date_out $order";

// Execute
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDF Export using TCPDF
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

    // Table headers
    $headers = [
        'Student Name', 'Department', 'Reg No', 'Year', 'Status', 'Submission Date'
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
            $html .= '<td>' . htmlspecialchars($row['department']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['r_no']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['year']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['status']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['submission_date']) . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="6" align="center">No records found</td></tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('report.pdf', 'D');
    exit;
}

// Default: return JSON for preview
header('Content-Type: application/json');
echo json_encode($rows);
?>
