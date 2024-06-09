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
        $staff_id = $_SESSION['staff_login'];

        // Fetch the approver details
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $approver = $user_query->fetch(PDO::FETCH_ASSOC);
        $approvername = $approver['surname'];

        // Get the current date and time
        date_default_timezone_set('Asia/Bangkok');
        $approvaldatetime = date('Y-m-d H:i:s');

        // Fetch user details
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :userId");
        $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        // Fetch the borrowing details
        $stmt = $conn->prepare("SELECT * FROM approve_to_use WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $items = explode(',', $data['list_name']);
        $insufficient_items = [];

        // Check availability of each item
        foreach ($items as $item) {
            $item_parts = explode('(', $item);
            $product_name = trim($item_parts[0]);
            $quantity = str_replace(')', '', $item_parts[1]);

            $stmtCheck = $conn->prepare("SELECT amount FROM crud WHERE sci_name = :product_name");
            $stmtCheck->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmtCheck->execute();
            $product = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($product['amount'] < $quantity) {
                $insufficient_items[] = $product_name;
            }
        }

        if (!empty($insufficient_items)) {
            $error_message = "ของมีไม่เพียงพอ: " . implode(', ', $insufficient_items);
            echo $error_message .'<a href="home.php"> กลับหน้าหลัก</a>';
            exit;
        }

        // Update the database if all items are available
        $update_query = $conn->prepare("UPDATE approve_to_use SET approver = :approver, approvaldatetime = :approvaldatetime, situation = :situation WHERE id = :id");
        $update_query->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query->bindParam(':situation', $situation, PDO::PARAM_INT);
        $update_query->bindParam(':approver', $approvername, PDO::PARAM_STR);
        $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
        $update_query->execute();

        $sMessage = "รายการยืมวัสดุอุปกรณ์และเครื่องมือ\n";
        $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['surname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
        $sMessage .= "SN : " . $data['serial_number'] . "\n";
        $sMessage .= "วันที่ขอยืม : " . date('d/m/Y H:i:s', strtotime($data['borrowdatetime'])) . "\n";
        $sMessage .= "วันที่นำมาคืน : " . date('d/m/Y H:i:s', strtotime($data['returndate'])) . "\n";

        // Update item amounts and prepare the notification message
        foreach ($items as $item) {
            $item_parts = explode('(', $item);
            $product_name = trim($item_parts[0]);
            $quantity = str_replace(')', '', $item_parts[1]);

            $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";

            $stmtUpdate = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE sci_name = :product_name");
            $stmtUpdate->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmtUpdate->execute();
        }

        $sMessage .= "ผู้อนุมัติการยืม : " . $approver['pre'] . ' ' . $approver['surname'] . ' ' . $approver['lastname'] . "\n";
        $sMessage .= "-------------------------------";

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

        // Result error
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

            // Get the current date and time
            date_default_timezone_set('Asia/Bangkok');
            $approvaldatetime = date('Y-m-d H:i:s');

            // Update the database to cancel the request
            $update_query = $conn->prepare("UPDATE approve_to_use SET situation = 2 WHERE id = :id ");
            $update_query->bindParam(':id', $id, PDO::PARAM_INT);
            $update_query->execute();

            // Fetch user details
            $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :userId");
            $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
            $user_query->execute();
            $user = $user_query->fetch(PDO::FETCH_ASSOC);

            $sMessage = "รายการยืมวัสดุอุปกรณ์และเครื่องมือ\n";

            // Fetch the borrowing details
            $stmt = $conn->prepare("SELECT * FROM approve_to_use WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $row) {
                $items = explode(',', $row['list_name']);

                $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['surname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
                $sMessage .= "SN : " . $row['serial_number'] . "\n"; // SN
                $sMessage .= "วันที่ขอยืม : " . date('d/m/Y H:i:s', strtotime($row['borrowdatetime'])) . "\n"; // Date of borrowing
                $sMessage .= "วันที่นำมาคืน : " . date('d/m/Y H:i:s', strtotime($row['returndate'])) . "\n"; // Return date

                // List borrowed items
                foreach ($items as $item) {
                    $item_parts = explode('(', $item); // Split item name and quantity
                    $product_name = trim($item_parts[0]); // Item name (trim potential spaces)
                    $quantity = str_replace(')', '', $item_parts[1]); // Quantity (remove parenthesis)

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

            // Result error 
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
?>
