<?php
session_start();
require_once '../assets/config/config.php';
require_once '../assets/config/Database.php';
date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // รับค่าจากฟอร์ม
    $selectedIds = $_POST['selected_ids'];
    $start_maintenance = $_POST['start_maintenance'];
    $end_maintenance = $_POST['end_maintenance'];
    $name_staff = $_POST['name_staff'];
    $note = $_POST['note'] ?? ''; // ตรวจสอบว่ามี note หรือไม่ ถ้าไม่มีกำหนดเป็นค่าว่าง

    $sMessage = "แจ้งเตือนการบำรุงรักษา\n";

    // อัพเดทสถานะของอุปกรณ์
    $update_query = $conn->prepare("UPDATE crud SET availability = 1 WHERE id IN ($selectedIds)");
    $update_query->execute();

    // ดึงข้อมูลผู้ใช้
    if (isset($_SESSION['staff_login'])) {
        $userID = $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $staff_id = $_SESSION['staff_login'];
    $user_query = $conn->prepare("
                        SELECT userID, pre, firstname, lastname 
                        FROM users_db
                        WHERE userID = :staff_id");
    $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    $user_query->execute();
    $users_LOG = $user_query->fetch(PDO::FETCH_ASSOC);
    $authID = $users_LOG['userID'];
    $log_Name = $users_LOG['pre'] . $users_LOG['firstname'] . ' ' . $users_LOG['lastname'];
    $log_Status = 'เริ่มต้นการบำรุงรักษา';

    // ดึงข้อมูลอุปกรณ์
    $stmt = $conn->prepare("SELECT serial_number, sci_name, categories FROM crud WHERE id IN ($selectedIds)");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $serial_number = $item['serial_number'];
        $sci_name = $item['sci_name'];
        $categories = $item['categories'];

        // เพิ่มข้อมูลการบำรุงรักษาเข้าสู่ฐานข้อมูล
        $insert_query_01 = $conn->prepare("INSERT INTO logs_maintenance (serial_number, sci_name, categories, start_maintenance, end_maintenance, name_staff, note) VALUES (:serial_number, :sci_name, :categories, :start_maintenance, :end_maintenance, :name_staff, :note)");
        $insert_query_01->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
        $insert_query_01->bindParam(':sci_name', $sci_name, PDO::PARAM_STR);
        $insert_query_01->bindParam(':categories', $categories, PDO::PARAM_STR);
        $insert_query_01->bindParam(':start_maintenance', $start_maintenance, PDO::PARAM_STR);
        $insert_query_01->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
        $insert_query_01->bindParam(':name_staff', $name_staff, PDO::PARAM_STR);
        $insert_query_01->bindParam(':note', $note, PDO::PARAM_STR);
        $result_01 = $insert_query_01->execute();

        if (!$result_01) {
            $_SESSION['error'] = "Data has not been updated successfully";
            header("Location: /maintenance");
            exit;
        }

        // เพิ่มรายละเอียดในข้อความ
        if ($item) {
            $sMessage .= "รายการ: " . $item['sci_name'] . "\n";
            $sMessage .= "ประเภท: " . $item['categories'] . "\n";
        }
    }

    $_SESSION['maintenanceSuccess'] = "เริ่มต้นกระบวนการการบำรุงรักษา";

    // สรุปข้อความ
    $sMessage .= "วันที่บำรุงรักษา : " . date('d/m/Y') . "\n";
    $sMessage .= "บำรุงรักษาสำเร็จ : " . date('d/m/Y', strtotime($end_maintenance)) . "\n";
    $sMessage .= "หมายเหตุ: " . $note . "\n";
    $sMessage .= "-------------------------------";

    $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

    // ตั้งค่า Line Notify
    $chOne = curl_init();
    curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($chOne, CURLOPT_POST, 1);
    curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . urlencode($sMessage));
    $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken);
    curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($chOne);

    if (curl_error($chOne)) {
        echo 'error:' . curl_error($chOne);
    } else {
        $result_ = json_decode($result, true);
        if ($result_['status'] !== 200) {
            echo "Error sending message: " . $result_['message'];
        }
    }
    curl_close($chOne);
    header('Location: /maintenance_start');
    exit;
}
?>