<?php
require_once '../assets/config/Database.php';
require_once '../assets/TCPDF-main/tcpdf.php'; // Include TCPDF
include '../assets/includes/thai_date_time.php';

// Get user_id and date range from GET parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Create SQL query to fetch maintenance logs based on conditions
$sql = "SELECT * FROM logs_maintenance WHERE 1";
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND (start_maintenance BETWEEN :start_date AND :end_date)";
}

// Prepare and execute SQL query
$stmt = $conn->prepare($sql);
if (!empty($start_date) && !empty($end_date)) {
    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
}
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('รายงานการบำรุงรักษา');
$pdf->SetSubject('รายงานการบำรุงรักษา');

// Add a page
$pdf->AddPage();

// Add fonts
$font_path = '../assets/fonts/Sarabun-Regular.ttf';
$font_bold_path = '../assets/fonts/Sarabun-Bold.ttf';

if (!file_exists($font_path)) {
    die('Error: Font not found at ' . $font_path);
}
if (!file_exists($font_bold_path)) {
    die('Error: Font not found at ' . $font_bold_path);
}

$pdf->AddFont('THSarabunNew', '', 'Sarabun-Regular.ttf', true);
$pdf->AddFont('THSarabunNew', 'B', 'Sarabun-Bold.ttf', true);

$pdf->SetFont('THSarabunNew', '', 16); // Regular font
$pdf->SetFont('THSarabunNew', 'B', 16); // Bold font

// Create HTML content for PDF
ob_start();
?>

<style>
    .header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        background-color: #fff;
    }

    .details_PDF {
        text-align: center;
        font-size: 16px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: .5px solid #000;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }
</style>

<div class="header">
    รายงานการบำรุงรักษา
</div>
<?php if (!empty($start_date) && !empty($end_date)) : ?>
    <div class="details_PDF">
        ตั้งแต่ <?php echo thai_date_time_4($start_date); ?> ถึง <?php echo thai_date_time_4($end_date); ?>
    </div>
<?php endif; ?>
<div>
    <table>
        <thead>
            <tr>
                <th>วันที่เริ่มต้น</th>
                <th>วันที่สิ้นสุด</th>
                <th>ชื่อวิทยาศาสตร์</th>
                <th>หมายเหตุ</th>
                <th>รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data) > 0) : ?>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td><?php echo thai_date_time_3($row["start_maintenance"]); ?></td>
                        <td><?php echo thai_date_time_3($row["end_maintenance"]); ?></td>
                        <td><?php echo htmlspecialchars($row["sci_name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["note"]); ?></td>
                        <td><?php echo htmlspecialchars($row["details_maintenance"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align: center">ไม่พบข้อมูลการบำรุงรักษา</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$html = ob_get_clean();

// Add HTML content to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('maintenance_report.pdf', 'I');
?>
