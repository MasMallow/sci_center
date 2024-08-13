<?php
session_start();
require_once '../assets/config/Database.php';
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลผู้ใช้
$userID = $_SESSION['staff_login'];
$stmt = $conn->prepare("
    SELECT * 
    FROM users_db  
    WHERE userID = :userID
");
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // รับค่าจากฟอร์ม
    $id = filter_input(INPUT_POST, 'selected_ids', FILTER_SANITIZE_STRING);
    $end_maintenance = filter_input(INPUT_POST, 'end_maintenance', FILTER_SANITIZE_STRING);
    $details_maintenance = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING) ?: '--';

    if ($id && $end_maintenance) {
        try {
            echo 'อันนี้id: '.$id.'<br>';
            // ดึงข้อมูลผู้ใช้สำหรับ log
            $user_query = $conn->prepare("
                SELECT userID, pre, firstname, lastname 
                FROM users_db 
                WHERE userID = :staff_id
            ");
            $user_query->bindParam(':staff_id', $userID, PDO::PARAM_INT);
            $user_query->execute();
            $users_LOG = $user_query->fetch(PDO::FETCH_ASSOC);

            $log_Name = $users_LOG['pre'] . $users_LOG['firstname'] . ' ' . $users_LOG['lastname'];
            
            echo 'log_Name: '.$log_Name.'<br>';
            
            // เริ่มต้น transaction
            $conn->beginTransaction();

            // อัพเดทสถานะ availability ในตาราง crud
            $update_query_01 = $conn->prepare("UPDATE crud SET availability = 0 WHERE serial_number = :id");
            $update_query_01->bindParam(':id', $id, PDO::PARAM_STR);
            $update_query_01->execute();
            echo 'Update 01 Row Count: '.$update_query_01->rowCount().'<br>';

            // อัพเดทวันที่บำรุงรักษาในตาราง info_sciname
            $update_query_02 = $conn->prepare("UPDATE info_sciname SET last_maintenance_date = :end_maintenance WHERE serial_number = :id");
            $update_query_02->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
            $update_query_02->bindParam(':id', $id, PDO::PARAM_STR);
            $update_query_02->execute();
            echo 'Update 02 Row Count: '.$update_query_02->rowCount().'<br>';

            // ดึง serial_number ใหม่ล่าสุด
            $serial_query = $conn->prepare("
                SELECT serial_number 
                FROM logs_maintenance 
                WHERE serial_number = :id 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $serial_query->bindParam(':id', $id, PDO::PARAM_INT);
            $serial_query->execute();
            $serial_number_data = $serial_query->fetch(PDO::FETCH_ASSOC);
            $serial_number = $serial_number_data['serial_number'] ?? null;
            echo 'Serial Number: '.$serial_number.'<br>';

            if ($serial_number) {
                // อัพเดทรายละเอียดการบำรุงรักษาในตาราง logs_maintenance โดยเลือก id ที่ใหม่ล่าสุด
                $update_query_03 = $conn->prepare("
                    UPDATE logs_maintenance 
                    SET end_maintenance = :end_maintenance, 
                        details_maintenance = :details_maintenance 
                    WHERE id = (
                        SELECT MAX(id) 
                        FROM logs_maintenance 
                        WHERE serial_number = :serial_number
                    )
                ");
                $update_query_03->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
                $update_query_03->bindParam(':details_maintenance', $details_maintenance, PDO::PARAM_STR);
                $update_query_03->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
                $update_query_03->execute();
                echo 'Update 03 Row Count: ' . $update_query_03->rowCount() . '<br>';
            }

            // ตรวจสอบการอัพเดท
            if ($update_query_01->rowCount() > 0 && $update_query_02->rowCount() > 0 && $serial_number && $update_query_03->rowCount() > 0) {
                $_SESSION['end_maintenanceSuccess'] = "สิ้นสุดกระบวนการการบำรุงรักษา";
                $conn->commit();
            } else {
                $conn->rollBack();
                $_SESSION['end_maintenanceError'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        } catch (Exception $e) {
            // Rollback in case of error
            $conn->rollBack();
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "ข้อมูลไม่ครบถ้วน";
    }

    // Comment this out during debugging, uncomment when ready
    header("Location: /maintenance_end");
    exit;
}
