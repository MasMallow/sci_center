<?php
session_start();
require_once 'assets/database/connect.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id AND status = 'approved'");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าผู้ใช้ได้รับการอนุมัติหรือไม่
if (!$userData) {
    $_SESSION['error'] = 'ผู้ใช้ไม่ได้รับการอนุมัติ!';
    header("Location: home.php");
    exit();
}

// ฟังก์ชันสำหรับการสร้างสตริงสุ่ม
function generateRandomString($length = 7) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($characters), 0, $length);
}

$random_string = generateRandomString();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['use_it'])) {
        $returnDate = $_POST['return_date'];
        $items = $_POST['amount'];

        // ดึงข้อมูลผู้ใช้
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $name_user = htmlspecialchars($user['pre'] . $user['surname'] . ' ' . $user['lastname']);

        // ตรวจสอบว่าตะกร้ามีสินค้าหรือไม่
        if (!is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $returnDateFormatted = date("Y-m-d", strtotime($returnDate));
        $itemList = [];
        $errorMessages = [];

        foreach ($_SESSION['cart'] as $item) {
            $query = $conn->prepare("SELECT * FROM crud WHERE img = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productName = htmlspecialchars($product['sci_name']);
                $checkBookingsDate = strtotime($product['check_bookings']);
                $currentDate = time();
                $returnDateTimestamp = strtotime($returnDateFormatted);
                $quantity = isset($items[$item]) ? (int)$items[$item] : 0;

                // ตรวจสอบว่ามีการจองหรือไม่และตรวจสอบจำนวนสินค้าว่ามีเพียงพอหรือไม่
                if (($currentDate < $checkBookingsDate && $returnDateTimestamp < $checkBookingsDate) || is_null($product['check_bookings'])) {
                    if ($quantity <= $product['amount']) {
                        $itemList[] = "$productName ($quantity)";
                    } else {
                        $errorMessages[] = "อุปกรณ์ $productName มีจำนวนไม่เพียงพอ (มีเพียง " . $product['amount'] . " ชิ้นในสต็อก)";
                    }
                } else {
                    $errorMessages[] = "อุปกรณ์ $productName มีคนจองวันที่ " . date("d-m-Y", $checkBookingsDate) . " ต้องยืมหรือคืนก่อนวันที่ " . date("d-m-Y", $checkBookingsDate);
                }
            }
        }

        // แสดงข้อความข้อผิดพลาดหากมี
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                echo $message . '<br>';
            }
        } else {
            if (!empty($itemList)) {
                $itemBorrowed = implode(', ', $itemList);

                // เตรียมข้อมูลสำหรับการยืมและบันทึกลงฐานข้อมูล
                $insert_query = $conn->prepare("INSERT INTO approve_to_use (user_id, serial_number, name_user, list_name, borrowdatetime, returndate, created_at) VALUES (:user_id, :random_string, :name_user, :list_name, NOW(), :returndate, NOW())");
                $insert_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
                $insert_query->bindParam(':name_user', $name_user, PDO::PARAM_STR);
                $insert_query->bindParam(':list_name', $itemBorrowed, PDO::PARAM_STR);
                $insert_query->bindParam(':returndate', $returnDateFormatted, PDO::PARAM_STR);
                $insert_query->execute();

                // ล้างตะกร้าหลังจากการยืมเสร็จสิ้น
                unset($_SESSION['cart']);

                // เก็บข้อมูลการยืมใน session
                $_SESSION['use_it_1'] = $random_string;
                $_SESSION['use_it_2'] = $itemBorrowed;

                header("Location: cart_use");
                exit();
            }
        }
    }
}
?>