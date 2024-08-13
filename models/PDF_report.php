<?php
require_once '../assets/config/config.php';
require_once '../assets/config/Database.php';
require_once '../assets/TCPDF-main/tcpdf.php'; // รวมไฟล์ TCPDF
include '../assets/includes/thai_date_time.php';

// รับค่า user_id และวันเวลาจากพารามิเตอร์ GET และตรวจสอบว่ามีการส่งมาหรือไม่
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// สร้าง SQL query เพื่อดึงข้อมูลจากตาราง approve_to_reserve โดยใช้เงื่อนไขของ user_id และช่วงเวลาถ้ามีการระบุ
$sql = "SELECT * FROM approve_to_reserve WHERE situation = 1";

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND (reservation_date BETWEEN :start_date AND :end_date)";
}

// เตรียมและดำเนินการ SQL query
$stmt = $conn->prepare($sql);

if (!empty($start_date) && !empty($end_date)) {
    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
}
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สร้างเอกสาร PDF ใหม่
$pdf = new TCPDF();

// ตั้งค่าข้อมูลเอกสาร
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('รายงานการยืมอุปกรณ์');
$pdf->SetSubject('รายงานการยืมอุปกรณ์');

// ตั้งค่ากระดาษและเพิ่มหน้าใหม่
$pdf->AddPage();

// เพิ่มฟอนต์ใหม่ลงใน TCPDF
$font_path = '../assets/fonts/Sarabun-Regular.ttf';
$font_bold_path = '../assets/fonts/Sarabun-Bold.ttf';

// ตรวจสอบว่าไฟล์ฟอนต์มีอยู่จริง
if (!file_exists($font_path)) {
    die('Error: ฟอนต์ที่ระบุไม่พบในเส้นทาง ' . $font_path);
}
if (!file_exists($font_bold_path)) {
    die('Error: ฟอนต์ที่ระบุไม่พบในเส้นทาง ' . $font_bold_path);
}

// เพิ่มฟอนต์ลงใน TCPDF
$pdf->AddFont('THSarabunNew', '', 'Sarabun-Regular.ttf', true);
$pdf->AddFont('THSarabunNew', 'B', 'Sarabun-Bold.ttf', true);

// ตั้งค่า font
$pdf->SetFont('THSarabunNew', '', 16); // ใช้ฟอนต์ THSarabunNew
$pdf->SetFont('THSarabunNew', 'B', 16); // ใช้ฟอนต์ THSarabunNew Bold

// สร้าง HTML เนื้อหาของ PDF
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
    รายงานการขอใช้ศูนย์วิทยาศาสตร์
</div>
<?php if (!empty($start_date) && !empty($end_date)) : ?>
    <div class="details_PDF">
        ตั้งแต่ <?php echo thai_date_time_4($start_date); ?> ถึง <?php echo thai_date_time_4($end_date); ?>
    </div>
<?php endif; ?>
<div>
    <?php if (count($data) > 0) : ?>
        <?php foreach ($data as $row) : ?>
            <strong><?php echo htmlspecialchars($row["name_user"]); ?></strong><br>
            ทำรายการ <?php echo thai_date_time_2($row["created_at"]); ?><br>
            <?php
            $items = explode(',', $row['list_name']);
            foreach ($items as $item) {
                $item_parts = explode('(', $item);
                $product_name = trim($item_parts[0]);
                $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : '0';
                echo '- ' . htmlspecialchars($product_name) . ' ' . htmlspecialchars($quantity) . ' รายการ<br>';
            }
            ?>
            ทำการขอใช้ <?php echo thai_date_time_2($row["reservation_date"]); ?> ถึง <?php echo thai_date_time_2($row["end_date"]); ?><br>
            <?php
            if ($row["date_return"] == NULL) {
                echo 'ยังไม่นำอุปกรณ์ เครื่องมือมาคืน';
            } else {
                echo 'นำอุปกรณ์ และเครื่องมือ ' . thai_date_time_2($row["date_return"]);
            }
            ?>
            <br><br>
        <?php endforeach; ?>
    <?php else : ?>
        <div style="text-align: center">ไม่พบข้อมูลในวันที่เลือก</div>
    <?php endif; ?>
</div>

<?php
$html = ob_get_clean();

// เพิ่มเนื้อหา HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// แสดง PDF
$pdf->Output('report.pdf', 'I');
?>