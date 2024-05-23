<?php
session_start();
include_once 'assets/database/connect.php';
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; // ตัวเลือกของตัวเลขและตัวอักษรที่จะสุ่ม
$random_string = '';

for ($i = 0; $i < 7; $i++) {
    $random_index = mt_rand(0, strlen($characters) - 1); // สุ่มตัวเลขดัชนี
    $random_string .= $characters[$random_index]; // เพิ่มตัวเลขหรือตัวอักษรที่สุ่มได้ในสตริงสุ่ม
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $reservationdate = $_POST['reservation_date'];
        $items = $_POST['amount'];
        if (isset($_SESSION['user_login'])) {
            $user_id = $_SESSION['user_login'];
        }

        $user_query = $conn->prepare("SELECT surname FROM users WHERE user_id = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $firstname = $user['surname'];

        foreach ($_SESSION['reserve_cart'] as $item) {
            // Retrieve product details from the database based on the item
            $query = $conn->prepare("SELECT * FROM crud WHERE sci_name = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);
            $productName = $product['sci_name'];

            // Retrieve the quantity of the item
            $quantity = isset($items[$item]) ? $items[$item] : 0;

            // เพิ่มชื่อสินค้าและจำนวนที่ยืมลงในรายการ
            $itemList[] = $productName . ' (' . $quantity . ')';
        }

        // รวมรายการที่ยืมเป็นสตริงเดียวโดยคั่นด้วย comma
        $itemBorrowed = implode(', ', $itemList);

        $insert_query = $conn->prepare("INSERT INTO bookings 
        (user_id, firstname, product_name, created_at, reservation_date, ApprovalDateTime, Approver, serial_number, situation) VALUES 
        (:user_id, :firstname, :itemBorrowed, NOW(), :reservationdate, NULL, NULL, :random_string, 0)");
        $insert_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insert_query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $insert_query->bindParam(':itemBorrowed', $itemBorrowed, PDO::PARAM_STR);
        $insert_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
        $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
        $insert_query->execute();

        unset($_SESSION['reserve_cart']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    รออนุมัติจากAdminนะครับ
    <a href="home.php">กลับหน้าหลัก</a>
</body>

</html>