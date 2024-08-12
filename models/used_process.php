<?php
session_start();
require_once '../assets/config/config.php';
require_once '../assets/config/Database.php';
date_default_timezone_set('Asia/Bangkok');

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] != 'approved') {
            header("Location: /");
            exit();
        }
    }
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['reserve_id'])) {
    $reserve_id = $_POST['reserve_id'];
    $sn_list = $_POST['sn_list'];
    $list_name = $_POST['list_name'];

    // Parse the sn_list to extract serial numbers
    $serial_numbers = explode(',', $sn_list);

    // Parse the list_name to extract item names and quantities
    preg_match_all('/(.*?)[(](\d+)[)]/', $list_name, $list_matches, PREG_SET_ORDER);

    // Ensure the number of items in sn_list matches list_name
    if (count($serial_numbers) !== count($list_matches)) {
        // Handle the error as needed
        die("Mismatch between serial numbers and quantities.");
    }

    // Update the usage status and reduce the quantity in the database
    $conn->beginTransaction();

    // Update the approve_to_reserve table to set Usage_item
    $updateUsageStmt = $conn->prepare("UPDATE approve_to_reserve SET Usage_item = 1 WHERE ID = :reserve_id AND userID = :user_id");
    $updateUsageStmt->bindParam(':reserve_id', $reserve_id, PDO::PARAM_INT);
    $updateUsageStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $updateUsageStmt->execute();

    // Reduce the quantity in the crud table for each item
    foreach ($list_matches as $index => $match) {
        $serial_number = trim($serial_numbers[$index]);
        $quantity = intval($match[2]);

        $updateCrudStmt = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE serial_number = :serial_number");
        $updateCrudStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $updateCrudStmt->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
        $updateCrudStmt->execute();
    }

    $conn->commit();

    // ตั้งค่า SESSION เพื่อแจ้งเตือนการเริ่มต้นใช้งานสำเร็จ
    $display_list = implode(', ', array_map(function ($match) {
        return trim($match[1]) . '(' . intval($match[2]) . ')';
    }, $list_matches));

    $_SESSION['USEDSTART_success'] = 'เริ่มต้นการใช้งานสำเร็จ';
    header("Location: /UsedStart");
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
    $update_query = $conn->prepare("SELECT * FROM approve_to_reserve WHERE id = :id");
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

    // ตั้งค่าข้อความแจ้งเตือนใน session
    $_SESSION['USEDEND_success'] = 'สิ้นสุดการใช้งาน';


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
    }
    curl_close($chOne);
    header('Location: /UsedEnd');
    exit();
}
