<?php
session_start();
include_once 'assets/database/connect.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        if (isset($_SESSION['staff_login'])) {
            $user_id = $_SESSION['staff_login'];
        }
        $sn = $_POST['id'];
        $userId = $_POST['userId'];
        $staff_id = $_SESSION['staff_login'];

        // Select surname of the approver from the database
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $approver = $user_query->fetch(PDO::FETCH_ASSOC);

        // Current date and time
        date_default_timezone_set('Asia/Bangkok');
        $approvaldatetime = date('Y-m-d H:i:s');

        // Update booking in the database
        $update_query = $conn->prepare("UPDATE bookings SET approver = :approver, approvaldatetime = :approvaldatetime WHERE serial_number = :sn");
        $update_query->bindParam(':sn', $sn, PDO::PARAM_INT);
        $update_query->bindParam(':approver', $approver['surname'], PDO::PARAM_STR); // Assuming approver is surname here
        $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
        $update_query->execute();

        // Select user details
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :userId");
        $user_query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        $sMessage = "รายการจองวัสดุอุปกรณ์และเครื่องมือ\n";

        $stmt = $conn->prepare("SELECT * FROM bookings WHERE serial_number = :sn");
        $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $sMessage .= "ชื่อผู้จอง : " . $user['pre'] . ' ' . $user['surname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";
        $sMessage .= "SN : " . $data['serial_number'] . "\n";
        $sMessage .= "วันที่กดจอง : " . date('d/m/Y H:i:s', strtotime($data['created_at'])) . "\n";
        $sMessage .= "วันที่จองใช้ : " . date('d/m/Y H:i:s', strtotime($data['reservation_date'])) . "\n";

        // Process each item in the booking
        $items = explode(',', $data['product_name']);
        foreach ($items as $item) {
            $item_parts = explode('(', $item); // Separate product name and quantity
            $product_name = trim($item_parts[0]);
            $quantity = str_replace(')', '', $item_parts[1]);

            $sMessage .= "ชื่อรายการ : " . $product_name . " " . $quantity . " ชิ้น\n";

            // Update the amount of each product in the crud table
            $stmtUpdate = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE sci_name = :product_name");
            $stmtUpdate->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmtUpdate->execute();
        }

        $sMessage .= "ผู้อนุมัติการจอง : " . $approver['pre'] . ' ' . $approver['surname'] . ' ' . $approver['lastname'] . "\n";
        $sMessage .= "-------------------------------";

        $sToken = "7ijLerwP9wvrN0e3ykl8y3y9c991p1WQuX1Dy8Pv3Fx";

        // Line Notify settings
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
        header('Location: /project/approval_reserve.php');
        exit;
    }
}
?>
