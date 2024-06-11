<?php
require_once 'assets/database/dbConfig.php';
require_once 'TCPDF-main/tcpdf.php'; // รวมไฟล์ TCPDF
include 'includes/thai_date_time.php';

// รับค่า user_id และวันเวลาจากพารามิเตอร์ GET และตรวจสอบว่ามีการส่งมาหรือไม่
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// สร้าง SQL query เพื่อดึงข้อมูลจากตาราง approve_to_use โดยใช้เงื่อนไขของ user_id และช่วงเวลาถ้ามีการระบุ
$sql = "SELECT * FROM approve_to_use WHERE 1 AND situation = 1";
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

// อ่านไฟล์ HTML
ob_start();
include('report_template1.html');
$html = ob_get_clean();

// เพิ่มเนื้อหา HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// แสดง PDF
$pdf->Output('report.pdf', 'I');
?>
