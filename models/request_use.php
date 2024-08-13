<?php
session_start();
require_once '../assets/config/Database.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['user_login'])) {
    $_SESSION['successSign_up'] = 'กรุณาเข้าสู่ระบบ!';
    header("Location: /sign_in");
    exit();
}

// ------------------ REQUEST FOR USE --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reserve'])) {
        $reservationdate = $_POST['reservation_date'];
        $items = $_POST['amount'];
        $enddate = $_POST['end_date'];
        $userID = $_SESSION['user_login'];

        // ฟังก์ชันสำหรับการสร้างสตริงสุ่ม
        function generateRandomString($length = 7)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return substr(str_shuffle($characters), 0, $length);
        }

        $random_string = generateRandomString();

        // ตรวจสอบวันที่
        $currentDate = date('Y-m-d');
        if ($reservationdate < $currentDate) {
            $_SESSION['reserveError'] = 'วันที่ขอใช้ต้องไม่เป็นอดีต!';
            header("Location: /cart");
            exit();
        }

        if ($enddate < $currentDate || $enddate < $reservationdate) {
            $_SESSION['reserveError'] = 'วันที่สิ้นสุดต้องไม่เป็นอดีตและต้องไม่ก่อนวันที่ขอใช้!';
            header("Location: /cart");
            exit();
        }

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
            $query = $conn->prepare("SELECT * FROM crud WHERE serial_number = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productName = htmlspecialchars($product['sci_name']);
                $serial_number = htmlspecialchars($product['serial_number']);
                $quantity = isset($items[$item]) ? (int)$items[$item] : 0;

                // ตรวจสอบการจองซ้ำถ้าประเภทไม่ใช่ "วัสดุ"
                if ($product['categories'] !== 'วัสดุ') {
                    $reservation_check_query = $conn->prepare(
                        "SELECT * FROM approve_to_reserve WHERE sn_list LIKE :serial_number AND (
                            (reservation_date <= :reservationdate AND end_date >= :reservationdate) OR
                            (reservation_date <= :enddate AND end_date >= :enddate) OR
                            (reservation_date >= :reservationdate AND end_date <= :enddate)
                        ) AND situation != 2"
                    );
                    $reservation_check_query->bindValue(':serial_number', "%$serial_number%", PDO::PARAM_STR);
                    $reservation_check_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
                    $reservation_check_query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
                    $reservation_check_query->execute();

                    if ($reservation_check_query->rowCount() > 0) {
                        $errorMessages[] = "$productName <br> ได้มีคนทำการขอใช้ไว้แล้วในวันที่เลือก";
                    }
                }

                // ตรวจสอบจำนวนสินค้าว่ามีเพียงพอหรือไม่
                if ($quantity <= $product['amount']) {
                    $itemList[] = "$productName ($quantity)";
                } else {
                    $errorMessages[] = "$productName มีจำนวนไม่เพียงพอ (มีเพียง " . $product['amount'] . " ในระบบ)";
                }
            }
        }

        // แสดงข้อความข้อผิดพลาดหากมี
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $message) {
                echo $message . '<br>';
                // เก็บข้อมูลการจองใน session
                $_SESSION['reserveError'] = $message;

                header("Location: /cart");
                exit();
            }
        } else {
            // เตรียมข้อมูลสำหรับการจอง
            $itemBorrowed = implode(', ', $itemList);
            // แปลง array เป็น comma-separated string
            $sn_list = implode(', ', $_SESSION['reserve_cart']);

            $insert_query = $conn->prepare(
                "INSERT INTO approve_to_reserve (serial_number, userID, name_user, list_name, reservation_date, end_date, created_at, sn_list) 
                VALUES (:random_string, :userID, :name_user, :list_name, :reservationdate, :enddate, NOW(), :sn_list)"
            );
            $insert_query->bindParam(':userID', $userID, PDO::PARAM_INT);
            $insert_query->bindParam(':name_user', $firstname, PDO::PARAM_STR);
            $insert_query->bindParam(':list_name', $itemBorrowed, PDO::PARAM_STR);
            $insert_query->bindParam(':reservationdate', $reservationdate, PDO::PARAM_STR);
            $insert_query->bindParam(':enddate', $enddate, PDO::PARAM_STR);
            $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
            $insert_query->bindParam(':sn_list', $sn_list, PDO::PARAM_STR); // ใช้ตัวแปรที่แปลงแล้ว
            $insert_query->execute();
            // ล้างตะกร้าหลังจากการจองเสร็จสิ้น
            unset($_SESSION['reserve_cart']);
            // เก็บข้อมูลการจองใน session
            $_SESSION['reserve_1'] = $random_string;
            $_SESSION['reserve_2'] = $itemBorrowed;
            $_SESSION['reserve_3'] = $reservationdate;

            header("Location: /cart");
            exit();
        }
    }
}
