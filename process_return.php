<?php
session_start();
include_once 'assets/database/connect.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && isset($_POST['id'])) {
        $situation = 1;
        $userId = $_POST['udi'];
        $id = $_POST['id'];
        // รหัสผู้ดูแลระบบที่กำลังเข้าสู่ระบบ
        $staff_id = $_SESSION['staff_login'];

        // เลือกชื่อผู้ดูแลระบบจากฐานข้อมูล
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $approver = $user_query->fetch(PDO::FETCH_ASSOC);
        $approvername = $approver['surname'];

        // วันเวลาปัจจุบัน
        date_default_timezone_set('Asia/Bangkok');
        $approvaldatetime = date('Y-m-d H:i:s');

        // อัปเดตฐานข้อมูล
        $update_query = $conn->prepare("UPDATE approve_to_use SET approver = :approver, approvaldatetime = :approvaldatetime, situation = :situation WHERE id = :id");
        $update_query->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query->bindParam(':situation', $situation, PDO::PARAM_INT);
        $update_query->bindParam(':approver', $approvername, PDO::PARAM_STR);
        $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
        $update_query->execute();

        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :userId");
        $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        $sMessage = "รายการยืมวัสดุอุปกรณ์และเครื่องมือ\n";

        $stmt = $conn->prepare("SELECT * FROM approve_to_use WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $row) {
            $items = explode(',', $row['itemborrowed']);
            $productNames = [];

            $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['surname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
            $sMessage .= "SN : " . $row['sn'] . "\n"; // SN
            $sMessage .= "วันที่ขอยืม : " . date('d/m/Y H:i:s', strtotime($row['borrowdatetime'])) . "\n"; // Date of borrowing
            $sMessage .= "วันที่นำมาคืน : " . date('d/m/Y H:i:s', strtotime($row['returndate'])) . "\n"; // Return date

            // แยกข้อมูล Item Borrowed
            $items = explode(',', $row['itemborrowed']);
            foreach ($items as $item) {
                $item_parts = explode('(', $item); // แยกชื่ออุปกรณ์และจำนวน
                $product_name = trim($item_parts[0]); // ชื่ออุปกรณ์ (ตัดช่องว่างที่เป็นไปได้)
                $quantity = str_replace(')', '', $item_parts[1]); // จำนวน (ตัดวงเล็บออก)

                $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";

                $stmtUpdate = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE sci_name = :product_name");
                $stmtUpdate->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':product_name', $product_name, PDO::PARAM_STR);
                $stmtUpdate->execute();
            }
            $sMessage .= "ผู้อนุมัติการยืม : " . $approver['pre'] . ' ' . $approver['surname'] . ' ' . $approver['lastname'] . "\n";
            $sMessage .= "-------------------------------";
        }


        $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

        // Line Notify settings
        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($chOne);

        //Result error 
        if (curl_error($chOne)) {
            echo 'error:' . curl_error($chOne);
        } else {
            $result_ = json_decode($result, true);
            echo "<script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'การยืมเสร็จสิ้น',
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                window.location.href = 'home.php';
            });
            </script>";
        }
        curl_close($chOne);
        header('Location: /project/approve_for_use.php');
        exit;
    }
    if (isset($_POST['cancel'])) {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $userId = $_POST['udi'];

            // วันเวลาปัจจุบัน
            date_default_timezone_set('Asia/Bangkok');
            $approvaldatetime = date('Y-m-d H:i:s');

            // อัปเดตฐานข้อมูล
            $update_query = $conn->prepare("UPDATE approve_to_use SET situation = 2 WHERE id = :id ");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->execute();

            $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :userId");
            $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
            $user_query->execute();
            $user = $user_query->fetch(PDO::FETCH_ASSOC);

            $sMessage = "รายการยืมวัสดุอุปกรณ์และเครื่องมือ\n";

            $stmt = $conn->prepare("SELECT * FROM approve_to_use WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $row) {
                $items = explode(',', $row['itemborrowed']);
                $productNames = [];

                $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['surname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
                $sMessage .= "SN : " . $row['sn'] . "\n"; // SN
                $sMessage .= "วันที่ขอยืม : " . date('d/m/Y H:i:s', strtotime($row['borrowdatetime'])) . "\n"; // Date of borrowing
                $sMessage .= "วันที่นำมาคืน : " . date('d/m/Y H:i:s', strtotime($row['returndate'])) . "\n"; // Return date

                // แยกข้อมูล Item Borrowed
                $items = explode(',', $row['itemborrowed']);
                foreach ($items as $item) {
                    $item_parts = explode('(', $item); // แยกชื่ออุปกรณ์และจำนวน
                    $product_name = trim($item_parts[0]); // ชื่ออุปกรณ์ (ตัดช่องว่างที่เป็นไปได้)
                    $quantity = str_replace(')', '', $item_parts[1]); // จำนวน (ตัดวงเล็บออก)

                    $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";
                }
                $sMessage .= "****ไม่อนุมัติการยืม****" . "\n";
                $sMessage .= "-------------------------------";
            }

            $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

            // Line Notify settings
            $chOne = curl_init();
            curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($chOne, CURLOPT_POST, 1);
            curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
            $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($chOne);

            //Result error 
            if (curl_error($chOne)) {
                echo 'error:' . curl_error($chOne);
            } else {
                $result_ = json_decode($result, true);
                echo "<script>
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'การยืมเสร็จสิ้น',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'home.php';
                });
                </script>";
            }
            curl_close($chOne);
            header('Location: /project/approve_for_use.php');
            exit;
        }
    }
}
