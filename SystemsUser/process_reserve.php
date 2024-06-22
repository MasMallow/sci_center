<?php
session_start();
include_once '../assets/database/dbConfig.php';

// Current date and time
date_default_timezone_set('Asia/Bangkok');
$approvaldatetime = date('Y-m-d H:i:s');

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && isset($_SESSION['staff_login'])) {
        $user_id = $_SESSION['staff_login'];
        $id = $_POST['id'];
        $userId = $_POST['userId'];
        $staff_id = $_SESSION['staff_login'];

        // Select firstname of the approver from the database
        $user_query = $conn->prepare("SELECT * FROM users_db WHERE userID = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $approver = $user_query->fetch(PDO::FETCH_ASSOC);

        // Check each item in the booking for existing bookings
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE id = :id");
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

            // Check for sufficient quantity
            if ($quantity > $result['amount']) {
                $errorMessages[] = 'จำนวนอุปกรณ์ ' . $product_name . ' ไม่พอ (มีเพียง ' . $result['amount'] . ' ชิ้นในสต็อก)';
            }
        }

        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                echo $message . '<br>';
            }
            echo '<a href="home.php">กลับหน้าหลัก</a><br>';
            exit;
        } else {
            // If no existing bookings, proceed with updating the booking
            $update_query = $conn->prepare("UPDATE approve_to_reserve SET approver = :approver, approvaldatetime = :approvaldatetime, situation = 1 WHERE id = :id");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->bindParam(':approver', $approver['firstname'], PDO::PARAM_STR);
            $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
            $update_query->execute();

            // Select user details
            $user_query = $conn->prepare("SELECT * FROM users_db WHERE userID = :userId");
            $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
            $user_query->execute();
            $user = $user_query->fetch(PDO::FETCH_ASSOC);

            // Create message for Line Notify
            $sMessage = "รายการจองวัสดุอุปกรณ์และเครื่องมือ\n";
            $sMessage .= "ชื่อผู้จอง : " . $user['pre'] . ' ' . $user['firstname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
            $sMessage .= "SN : " . $data['serial_number'] . "\n";
            $sMessage .= "วันที่กดจอง : " . date('d/m/Y H:i:s', strtotime($data['created_at'])) . "\n";
            $sMessage .= "วันที่จองใช้ : " . date('d/m/Y H:i:s', strtotime($data['reservation_date'])) . "\n";

            // Process each item in the booking
            foreach ($items as $item) {
                list($product_name, $quantity) = explode('(', $item);
                $product_name = trim($product_name);
                $quantity = str_replace(')', '', $quantity);

                $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";
            }

            $sMessage .= "ผู้อนุมัติการจอง : " . $approver['pre'] . ' ' . $approver['firstname'] . ' ' . $approver['lastname'] . "\n";
            $sMessage .= "-------------------------------";

            // Line Notify settings
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

            // Result error handling
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
            header('Location: /home.php');
            exit;
        }
    }elseif (isset($_POST['cancel'])) {
        $id = $_POST['id'];
        $userId = $_POST['userId'];

        // Update booking in the database
        $update_query = $conn->prepare("UPDATE approve_to_reserve SET situation = 2 WHERE id = :id AND user_id = :udi");
        $update_query->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query->bindParam(':udi', $userId, PDO::PARAM_INT);
        $update_query->execute();

        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $sMessage = "รายการจองวัสดุอุปกรณ์และเครื่องมือ\n";

        // Select user details
        $user_query = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT); // Bind the user_id parameter
        $user_query->execute(); // Execute the query

        // Fetch the user data. This fetches a single row since userID is unique
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        // Check if user data was fetched successfully
        if ($user) {
            // Concatenate user information into $sMessage
            $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['firstname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
        } else {
            // Handle the case where no user was found for the given userID
            $sMessage .= "ไม่พบข้อมูลผู้ยืมสำหรับ userID: " . $user_id . "\n";
        }
        $sMessage .= "SN : " . $data['serial_number'] . "\n";
        $sMessage .= "วันที่กดจอง : " . date('d/m/Y H:i:s', strtotime($data['created_at'])) . "\n";
        $sMessage .= "วันที่จองใช้ : " . date('d/m/Y H:i:s', strtotime($data['reservation_date'])) . "\n";

        // Process each item in the booking
        $items = explode(',', $data['list_name']);
        foreach ($items as $item) {
            list($product_name, $quantity) = explode('(', $item);
            $product_name = trim($product_name);
            $quantity = str_replace(')', '', $quantity);

            $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";
        }

        $sMessage .= "****ไม่อนุมัติการจอง****\n";
        $sMessage .= "-------------------------------";

        // Line Notify settings
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

        // Result error handling
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
        header('Location: /home.php');
        exit;
    }
}
