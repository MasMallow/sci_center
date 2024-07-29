<?php
// เริ่มเซสชัน
session_start();
// เรียกใช้งานไฟล์คอนฟิกของฐานข้อมูล
require_once '../assets/config/Database.php';

// ------------------ CANCEL  REQUEST --------------------------------
// ตรวจสอบว่าเป็นการเรียกใช้งานแบบ POST และตรวจสอบว่ามีค่าของ reserveID ที่ถูกส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserveID'])) {
    // รับค่า reserveID จากแบบฟอร์ม
    $reserveID = $_POST['reserveID'];

    // เตรียมคำสั่ง SQL เพื่อลบรายการจากตาราง approve_to_reserve ที่ตรงกับ ID ที่ระบุ
    $stmt = $conn->prepare("DELETE FROM approve_to_reserve WHERE ID = :reserveID");
    // ผูกพารามิเตอร์ booking_id กับค่า reserveID
    $stmt->bindParam(':reserveID', $reserveID, PDO::PARAM_INT);
    // เรียกใช้คำสั่ง SQL
    $stmt->execute();

    // หลังจากลบรายการเสร็จแล้ว ให้เปลี่ยนเส้นทางไปยังหน้า TrackingReserve
    header('Location: /TrackingReserve');
    exit();
}
?>