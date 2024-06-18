<?php
session_start();
require_once '../assets/database/dbConfig.php';
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ตรวจสอบการส่งข้อมูลแบบ POST และการกดปุ่ม complete_maintenance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_maintenance'])) {
    $ids = $_POST['selected_ids'];
    $end_maintenance = $_POST['end_maintenance'];
    $details_maintenance = $_POST['details_maintenance'] ?? '' ;

    $sMessage = "แจ้งเตือนการบำรุงรักษา\n";

    // วนลูปเพื่ออัพเดทข้อมูลการบำรุงรักษาในฐานข้อมูล
    foreach ($ids as $id) {
        try {
            // อัพเดทสถานะ availability ในตาราง crud
            $update_query = $conn->prepare("UPDATE crud SET availability = 0 WHERE ID = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query_01 = $update_query->execute();

            // อัพเดทวันที่บำรุงรักษาในตาราง info_sciname
            $update_query = $conn->prepare("UPDATE info_sciname SET last_maintenance_date = :end_maintenance WHERE ID = :id");
            $update_query->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query_02 = $update_query->execute();

            // อัพเดทรายละเอียดการบำรุงรักษาในตาราง logs_maintenance
            $update_query = $conn->prepare("UPDATE logs_maintenance SET end_maintenance = :end_maintenance, details_maintenance = :details_maintenance WHERE ID = :id");
            $update_query->bindParam(':end_maintenance', $end_maintenance, PDO::PARAM_STR);
            $update_query->bindParam(':details_maintenance', $details_maintenance, PDO::PARAM_STR);
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query_03 = $update_query->execute();

            // ดึงข้อมูลรายละเอียดของรายการเพื่อใช้ในการแจ้งเตือน
            $stmt = $conn->prepare("SELECT * FROM crud WHERE ID = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                $sMessage .= "รายการ: " . htmlspecialchars($item['sci_name'], ENT_QUOTES, 'UTF-8') . "\n";
                $sMessage .= "ประเภท: " . htmlspecialchars($item['categories'], ENT_QUOTES, 'UTF-8') . "\n";
            }

            if ($update_query_01 && $update_query_02 && $update_query_03) {
                $_SESSION['maintenanceSuccess'] = "เริ่มต้นกระบวนการการบำรุงรักษา";
            } else {
                $_SESSION['error'] = "Data has not been updated successfully";
            }

            header("Location: /maintenance/end_maintenance");
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            header("Location: /maintenance/end_maintenance");
            exit;
        }
    }

    // เพิ่มวันที่สิ้นสุดการบำรุงรักษาในข้อความ
    $sMessage .= "วันที่บำรุงรักษาสำเร็จ : " . date('d/m/Y H:i:s') . "\n";
    $sMessage .= "-------------------------------";

    // Line Notify settings
    $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";
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

    // ตรวจสอบผลการส่งแจ้งเตือน
    if (curl_error($chOne)) {
        echo 'error:' . curl_error($chOne);
    } else {
        $result_ = json_decode($result, true);
        if ($result_['status'] == 200) {
            echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'การบำรุงรักษาเสร็จสิ้น',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href = 'home.php';
                        });
                      </script>";
        } else {
            echo "<script>alert('Notification failed: " . htmlspecialchars($result_['message'], ENT_QUOTES, 'UTF-8') . "');</script>";
        }
    }
    curl_close($chOne);
}
