<?php
session_start();
include_once '../assets/config/config.php';
include_once '../assets/config/Database.php';
date_default_timezone_set('Asia/Bangkok');

// กำหนดวันที่และเวลาปัจจุบัน
$approvaldatetime = date('Y-m-d H:i:s');

// ตรวจสอบว่าพนักงานเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ตรวจสอบว่าคำขอเป็น POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $id = $_POST['id'];
        $userID = $_POST['userID'];
        $staff_id = $_SESSION['staff_login'];

        echo ($id);

        // เลือกข้อมูลผู้อนุมัติจากฐานข้อมูล
        $staffSelect = $conn->prepare("SELECT * FROM users_db WHERE userID = :staff_id");
        $staffSelect->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $staffSelect->execute();
        $approver = $staffSelect->fetch(PDO::FETCH_ASSOC);
        $approver_name = $approver['pre'] . $approver['firstname'] . ' ' . $approver['lastname'];

        // ตรวจสอบรายการในคำขอการจอง
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE ID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $items = explode(',', $data['list_name']);
        $errorMessages = [];
        foreach ($items as $item) {
            list($product_name, $quantity) = explode('(', $item);
            $product_name = trim($product_name);
            $quantity = str_replace(')', '', $quantity);

            $Check_data = $conn->prepare("SELECT * FROM crud WHERE sci_name = :product_name");
            $Check_data->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $Check_data->execute();
            $result = $Check_data->fetch(PDO::FETCH_ASSOC);

            // ตรวจสอบจำนวนอุปกรณ์
            if ($quantity > $result['amount']) {
                $errorMessages[] = 'จำนวนอุปกรณ์ ' . $product_name . ' ไม่พอ (มีเพียง ' . $result['amount'] . ' ชิ้นในสต็อก)';
            }
        }

        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                echo $message . '<br>';
            }
            echo '<a href="Home.php">กลับหน้าหลัก</a><br>';
            exit;
        } else {
            // ถ้าไม่มีข้อผิดพลาดในการจอง, ทำการอัปเดตข้อมูลการจอง
            $update_query = $conn->prepare("
                    UPDATE approve_to_reserve 
                    SET approver = :approver, approvaldatetime = :approvaldatetime, situation = 1 
                    WHERE ID = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->bindParam(':approver', $approver_name, PDO::PARAM_STR);
            $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
            $update_query->execute();

            // เลือกข้อมูลผู้ใช้งาน
            $user_query = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
            $user_query->bindParam(':userID', $userID, PDO::PARAM_INT);
            $user_query->execute();
            $user = $user_query->fetch(PDO::FETCH_ASSOC);

            // สร้างข้อความสำหรับแจ้งเตือน Line Notify
            $sMessage = "รายการจองวัสดุอุปกรณ์และเครื่องมือ\n";
            $sMessage .= "ชื่อผู้จอง : " . $user['pre'] . ' ' . $user['firstname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
            $sMessage .= "SN : " . $data['serial_number'] . "\n";
            $sMessage .= "วันที่กดจอง : " . date('d/m/Y H:i:s', strtotime($data['created_at'])) . "\n";
            $sMessage .= "วันที่จองใช้ : " . date('d/m/Y H:i:s', strtotime($data['reservation_date'])) . "\n";

            // ประมวลผลแต่ละรายการในคำขอการจอง
            foreach ($items as $item) {
                list($product_name, $quantity) = explode('(', $item);
                $product_name = trim($product_name);
                $quantity = str_replace(')', '', $quantity);

                $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";
            }

            $sMessage .= "ผู้อนุมัติการจอง : " . $approver['pre'] . ' ' . $approver['firstname'] . ' ' . $approver['lastname'] . "\n";
            $sMessage .= "-------------------------------";

            // การตั้งค่า Line Notify
            $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
            $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken);
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            // การจัดการข้อผิดพลาด
            if (curl_error($chOne)) {
                echo 'error:' . curl_error($chOne);
            }
            curl_close($chOne);

            $_SESSION['approve_success'] = 'อนุมัติการขอใช้เรียบร้อย';
            header('Location: /approve_request');
            exit;
        }
    }
}
