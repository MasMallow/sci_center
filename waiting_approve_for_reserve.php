<?php
session_start();
require_once 'assets/database/dbConfig.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($conn)) {
    die('Database connection failed.');
}

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบสถานะของผู้ใช้
if (!$userData || $userData['status'] !== 'approved') {
    header("Location: home.php");
    exit();
}

// สร้างสตริงสุ่มสำหรับ serial number
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$random_string = substr(str_shuffle($characters), 0, 7);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reserve'])) {
        $reservationdate = $_POST['reservation_date'];
        $items = $_POST['amount'];
        $enddate = $_POST['end_date'];

        $user_query = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $firstname = htmlspecialchars($user['pre'] . $user['surname'] . ' ' . $user['lastname']);

        $itemList = [];
        $errorMessages = [];

        foreach ($_SESSION['reserve_cart'] as $item) {
            $query = $conn->prepare("SELECT * FROM crud WHERE img = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productName = htmlspecialchars($product['sci_name']);
                $quantity = isset($items[$item]) ? (int)$items[$item] : 0;

                // ตรวจสอบการจองล่วงหน้า
                if ($product['check_bookings'] != NULL) {
                    $errorMessages[] = "อุปกรณ์ $productName ได้มีคนทำการจองไว้แล้ว";
                }

                // ตรวจสอบจำนวนสินค้าว่ามีเพียงพอหรือไม่
                if ($quantity <= $product['amount']) {
                    $itemList[] = "$productName ($quantity)";
                } else {
                    $errorMessages[] = "อุปกรณ์ $productName มีจำนวนไม่เพียงพอ (มีเพียง " . $product['amount'] . " ชิ้นในสต็อก)";
                }
            }
        }

        // แสดงข้อความข้อผิดพลาดหากมี
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                echo $message . '<br>';
            }
            echo '<a href="cart_use">กลับหน้าตะกร้า</a><br>';
        } else {
            // เตรียมข้อมูลสำหรับการจอง
            $itemBorrowed = implode(', ', $itemList);

            $insert_query = $conn->prepare(
                "INSERT INTO approve_to_reserve (serial_number, user_id, name_user, list_name, reservation_date, end_date, created_at) 
                VALUES (:random_string, :user_id, :name_user, :list_name, :reservationdate, :enddate, NOW())"
            );
            $insert_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insert_query->bindParam(':name_user', $firstname, PDO::PARAM_STR);
            $insert_query->bindParam(':list_name', $itemBorrowed, PDO::PARAM_STR);
            $insert_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
            $insert_query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
            $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
            $insert_query->execute();

            // ล้างตะกร้าหลังจากการจองเสร็จสิ้น
            unset($_SESSION['reserve_cart']);

            // เก็บข้อมูลการจองใน session
            $_SESSION['reserve_1'] = $random_string;
            $_SESSION['reserve_2'] = $itemBorrowed;
            $_SESSION['reserve_3'] = $reservationdate;

            header("Location: cart_reserve");
            exit();
        }
    }
}
?>