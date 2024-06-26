<?php
session_start();
require_once '../assets/database/dbConfig.php';
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลผู้ใช้
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db  
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_maintenance'])) {
    // รับค่าจากฟอร์ม
    $id = $_POST['selected_ids'];
    $serial_number = $_POST['serial_ids'];
    $end_maintenance = $_POST['end_maintenance'];
    $details_maintenance = $_POST['details_maintenance'] ?? '--';

    // ดึงข้อมูลผู้ใช้สำหรับ log
    $staff_id = $_SESSION['staff_login'];
    $user_query = $conn->prepare("
        SELECT userID, pre, firstname, lastname 
        FROM users_db 
        WHERE userID = :staff_id
    ");
    $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    $user_query->execute();
    $users_LOG = $user_query->fetch(PDO::FETCH_ASSOC);

    try {
        // สร้างชื่อผู้ใช้สำหรับ log
        $log_Name = $users_LOG['pre'] . $users_LOG['firstname'] . ' ' . $users_LOG['lastname'];

        // อัพเดทสถานะ availability ในตาราง crud
        $update_query_01 = $conn->prepare("UPDATE crud SET availability = 0 WHERE ID = :id");
        $update_query_01->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query_01->execute();

        // อัพเดทวันที่บำรุงรักษาในตาราง info_sciname
        $update_query_02 = $conn->prepare("UPDATE info_sciname SET last_maintenance_date = :end_maintenance WHERE ID = :id");
        $update_query_02->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
        $update_query_02->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query_02->execute();

        // อัพเดทรายละเอียดการบำรุงรักษาในตาราง logs_maintenance โดยเลือก serial_number อันใหม่ล่าสุด
        $update_query_03 = $conn->prepare("
            UPDATE logs_maintenance 
            SET end_maintenance = :end_maintenance, 
                details_maintenance = :details_maintenance 
            WHERE serial_number = :serial_number 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $update_query_03->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
        $update_query_03->bindParam(':details_maintenance', $details_maintenance, PDO::PARAM_STR);
        $update_query_03->bindParam(':serial_number', $serial_number, PDO::PARAM_INT);
        $update_query_03->execute();

        // ตรวจสอบการอัพเดททั้งสาม query
        if ($update_query_01 && $update_query_02 && $update_query_03) {
            $_SESSION['end_maintenanceSuccess'] = "สิ้นสุดกระบวนการการบำรุงรักษา";
        } else {
            $_SESSION['end_maintenanceError'] = "เกิดข้อผิดพลาด";
        }
        // เปลี่ยนเส้นทางไปยังหน้าสิ้นสุดการบำรุงรักษา
        header("Location: /maintenance/end_maintenance");
        exit;
    } catch (Exception $e) {
        // เกิดข้อผิดพลาด
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: /maintenance/end_maintenance");
        exit;
    }
}
        // // เพิ่มข้อมูลการบำรุงรักษาเข้าสู่ฐานข้อมูล logs_maintenance_2
        // $insert_query_01 = $conn->prepare("INSERT INTO logs_maintenance (log_Name, log_Date) VALUES (:log_name, NOW(),)");
        // $insert_query_01->bindParam(':log_name', $log_Name, PDO::PARAM_STR);
        // $insert_query_01->bindParam(':log_status', $log_Status, PDO::PARAM_STR);
        // $insert_query_01->execute();
