<?php
require_once 'assets/database/dbConfig.php';
require_once 'assets/TCPDF-main/tcpdf.php'; // รวมไฟล์ TCPDF
include 'assets/includes/thai_date_time.php';

// รับค่า userID และวันเวลาจากพารามิเตอร์ GET และตรวจสอบว่ามีการส่งมาหรือไม่
$userID = isset($_GET['userID']) ? $_GET['userID'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// สร้าง SQL query เพื่อดึงข้อมูลจากตาราง approve_to_reserve โดยใช้เงื่อนไขของ userID และช่วงเวลาถ้ามีการระบุ
$sql = "SELECT * FROM approve_to_reserve WHERE 1 AND (situation = 1 OR situation = 3)";
if (!empty($userID) && $userID !== 'all') {
    $sql .= " AND userID = :userID";
}
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND (reservation_date BETWEEN :start_date AND :end_date)";
}

// เตรียมและดำเนินการ SQL query
$stmt = $conn->prepare($sql);
if (!empty($userID) && $userID !== 'all') {
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
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

// เพิ่มหน้าใหม่
$pdf->AddPage();

// ตั้งค่า font
$pdf->SetFont('thsarabunnew', '', 16); // ใช้ฟอนต์ THSarabunNew ที่ติดตั้ง

// สร้าง HTML content
$html = '<style>
            body, h1, h2, h3, h4, h5, h6, p, table {
                margin: 0;
                padding: 0;
            }
            table {
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid black;
            }
            th:first-child, td:first-child {
                border-top: none;
            }
        </style>';

$html .= '<body>
                <div class="details">';

if (!empty($start_date) && !empty($end_date)) {
    $html .= '<p><strong>วันที่เริ่มต้น:</strong> ' . thai_date_time($start_date) . '</p>';
    $html .= '<p><strong>วันที่สิ้นสุด:</strong> ' . thai_date_time($end_date) . '</p>';
}

$html .= '</div>
        <table>
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
        $html .= '<tr>
              <td>' . htmlspecialchars($row["userID"]) . '</td>
              <td>' . htmlspecialchars($row["name_user"]) . '</td>
              <td>';
        $items = explode(',', $row['list_name']);
        foreach ($items as $item) {
            $item_parts = explode('(', $item);
            $product_name = trim($item_parts[0]);
            $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : '0';
            $html .= $product_name . ' ' . $quantity . ' ชิ้น<br>';
        }
        $html .= '</td>
              <td>' . thai_date_time($row["reservation_date"]) . '</td>
              <td>' . thai_date_time($row["end_date"]) . '</td>
            </tr>';
    }
} else {
    $html .= '<tr>
              <td colspan="5" style="text-align: center">ไม่พบข้อมูลในฐานข้อมูล</td>
            </tr>';
}

$html .= '</tbody></table></body>';

// เพิ่มเนื้อหา HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// แสดง PDF
$pdf->Output('report.pdf', 'I');
