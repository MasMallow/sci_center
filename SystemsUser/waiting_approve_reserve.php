<?php
session_start();
require_once '../assets/database/dbConfig.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID    
        ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == '0') {
            unset($_SESSION['user_login']);
            header('Location: auth/sign_in');
            exit();
        }
    }
}

// ฟังก์ชันสำหรับการสร้างสตริงสุ่ม
function generateRandomString($length = 7)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($characters), 0, $length);
}

$random_string = generateRandomString();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reserve'])) {
        $reservationdate = $_POST['reservation_date'];
        $items = $_POST['amount'];
        $enddate = $_POST['end_date'];

        $user_query = $conn->prepare("
                SELECT * FROM users_db 
                WHERE userID = :userID");
        $user_query->bindParam(':userID', $userID, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $e_mail = ($user['email']);
        $firstname = htmlspecialchars($user['pre'] . $user['firstname'] . ' ' . $user['lastname']);

        $itemList = [];
        $errorMessages = [];

        foreach ($_SESSION['reserve_cart'] as $item) {
            $query = $conn->prepare("SELECT * FROM crud WHERE sci_name = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productName = htmlspecialchars($product['sci_name']);
                $quantity = isset($items[$item]) ? (int)$items[$item] : 0;

                // ตรวจสอบการจองซ้ำถ้าประเภทไม่ใช่ "วัสดุ"
                if ($product['categories'] !== 'วัสดุ') {
                    $reservation_check_query = $conn->prepare(
                        "SELECT * FROM approve_to_reserve WHERE list_name LIKE :productName AND (
                            (reservation_date <= :reservationdate AND end_date >= :reservationdate) OR
                            (reservation_date <= :enddate AND end_date >= :enddate) OR
                            (reservation_date >= :reservationdate AND end_date <= :enddate)
                        )"
                    );
                    $reservation_check_query->bindValue(':productName', "%$productName%", PDO::PARAM_STR);
                    $reservation_check_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
                    $reservation_check_query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
                    $reservation_check_query->execute();

                    if ($reservation_check_query->rowCount() > 0) {
                        $errorMessages[] = "อุปกรณ์ $productName ได้มีคนทำการจองไว้แล้ว";
                    }
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
            $insert_query->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $insert_query->bindParam(':name_user', $firstname, PDO::PARAM_STR);
            $insert_query->bindParam(':list_name', $itemBorrowed, PDO::PARAM_STR);
            $insert_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
            $insert_query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
            $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
            $insert_query->execute();

            $insert_logs = $conn->prepare(
                "INSERT INTO logs_usage (authID, authName, log_orDers, log_Data, created_at, reservation_date, end_date) 
                VALUES (:authID, :authName, :random_string, :list_name, NOW(), :reservationdate, :enddate)"
            );
            $insert_logs->bindParam(':authID', $userID, PDO::PARAM_INT);
            $insert_logs->bindParam(':authName', $firstname, PDO::PARAM_STR);
            $insert_logs->bindParam(':random_string', $random_string, PDO::PARAM_STR);
            $insert_logs->bindParam(':list_name', $itemBorrowed, PDO::PARAM_STR);
            $insert_logs->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
            $insert_logs->bindParam(':enddate', $enddate, PDO::PARAM_STR);
            $insert_logs->execute();

            // ล้างตะกร้าหลังจากการจองเสร็จสิ้น
            unset($_SESSION['reserve_cart']);

            // เก็บข้อมูลการจองใน session
            $_SESSION['reserve_1'] = $random_string;
            $_SESSION['reserve_2'] = $itemBorrowed;
            $_SESSION['reserve_3'] = $reservationdate;

            header("Location: $base_url/cart_systems");
            exit();
        }
    }
}
?>
