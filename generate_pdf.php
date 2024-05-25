<?php
require_once 'assets/database/connect.php';
require_once 'TCPDF-main/tcpdf.php'; // รวมไฟล์ TCPDF

// รับค่า user_id และวันเวลาจากพารามิเตอร์ GET และตรวจสอบว่ามีการส่งมาหรือไม่
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// สร้าง SQL query เพื่อดึงข้อมูลจากตาราง waiting_for_approval โดยใช้เงื่อนไขของ user_id และช่วงเวลาถ้ามีการระบุ
$sql = "SELECT * FROM waiting_for_approval WHERE 1";
if (!empty($user_id) && $user_id !== 'all') {
    $sql .= " AND udi = :user_id";
}
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND (borrowdatetime BETWEEN :start_date AND :end_date)";
}

// เตรียมและดำเนินการ SQL query
$stmt = $conn->prepare($sql);
if (!empty($user_id) && $user_id !== 'all') {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}
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

// เพิ่มหน้าใหม่
$pdf->AddPage();

// ตั้งค่า font
$pdf->SetFont('thsarabunnew', '', 16); // ใช้ฟอนต์ THSarabunNew ที่ติดตั้ง

// เพิ่มเนื้อหา
$html = '<h1>รายงานการยืมอุปกรณ์</h1>';
$html .= '<p><strong>วันที่เริ่มต้น:</strong> ' . date('d/m/Y', strtotime($start_date)) . '</p>';
$html .= '<p><strong>วันที่สิ้นสุด:</strong> ' . date('d/m/Y', strtotime($end_date)) . '</p>';
$html .= '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>รหัสผู้ใช้</th>
                    <th>ชื่อ</th>
                    <th>ชื่อสินค้า</th>
                    <th>วันที่ยืม</th>
                    <th>วันที่คืน</th>
                </tr>
            </thead>
            <tbody>';

if (count($data) > 0) {
    foreach ($data as $row) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row["udi"]) . '</td>';
        $html .= '<td>' . htmlspecialchars($row["firstname"]) . '</td>';

        $items = explode(',', $row['itemborrowed']);
        $html .= '<td>';
        foreach ($items as $item) {
            $item_parts = explode('(', $item);
            $product_name = trim($item_parts[0]);
            $quantity = str_replace(')', '', $item_parts[1]);
            $html .= $product_name . ' ' . $quantity . ' ชิ้น<br>';
        }
        $html .= '</td>';

        $html .= '<td>' . date('d/m/Y H:i:s', strtotime($row["borrowdatetime"])) . '</td>';
        $html .= '<td>' . date('d/m/Y H:i:s', strtotime($row["returndate"])) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5">ไม่พบข้อมูลในฐานข้อมูล</td></tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

// แสดง PDF
$pdf->Output('report.pdf', 'I');
?>
