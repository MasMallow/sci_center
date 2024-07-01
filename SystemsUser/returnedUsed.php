<?php
session_start();
require_once '../assets/database/config.php';
date_default_timezone_set('Asia/Bangkok');

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            header("Location: home.php");
            exit();
        }
    }
}
else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
    $return_id = $_POST['return_id'];
    $user_id = $_POST['user_id'];
    $currentDateTime = date('Y-m-d H:i:s');

    // Update the date_return in approve_to_use table
    $stmt = $conn->prepare("UPDATE approve_to_reserve SET date_return = :currentDateTime WHERE id = :return_id");
    $stmt->bindParam(':currentDateTime', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':return_id', $return_id, PDO::PARAM_INT);
    $stmt->execute();

    // Prepare message for LINE Notify
    $sMessage = "แจ้งเตือนการคืนอุปกรณ์\n";

    // Fetch the updated data from approve_to_use
    $update_query = $conn->prepare("SELECT * FROM approve_to_bookings WHERE id = :id");
    $update_query->bindParam(':id', $return_id, PDO::PARAM_INT);
    $update_query->execute();
    $update_data = $update_query->fetch(PDO::FETCH_ASSOC);

    $user_query = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);

    $sMessage .= "ชื่อผู้ยืม : " . $user['pre'] . ' ' . $user['firstname'] . ' ' . $user['lastname'] . ' ' . $user['role'] . ' ' . $user['agency'] . "\n";

    if ($update_data) {
        $items = explode(',', $update_data['list_name']);
        foreach ($items as $item) {
            $item_parts = explode('(', $item); // Split the item name and quantity
            $product_name = trim($item_parts[0]); // Trim the item name
            $quantity = str_replace(')', '', $item_parts[1]); // Remove the closing parenthesis from the quantity

            $sMessage .= "ชื่อรายการ: " . $product_name . " " . $quantity . " ชิ้น\n";

            // Update the amount in crud table
            $stmtUpdate = $conn->prepare("UPDATE crud SET amount = amount + :quantity WHERE sci_name = :product_name AND categories IN ('อุปกรณ์', 'เครื่องมือ')");
            $stmtUpdate->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmtUpdate->execute();
        }
        $sMessage .= "วันที่ยืม: " . date('d/m/Y H:i', strtotime($update_data['reservation_date'])) . "\n";
        $sMessage .= "วันที่คืน: " . date('d/m/Y H:i', strtotime($currentDateTime)) . "\n";
    }

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
    header('Location: returned_system');
    exit();
}
?>
