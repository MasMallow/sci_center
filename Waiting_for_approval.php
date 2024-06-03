<?php
session_start();
require_once 'assets/database/connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            header("Location: home.php");
            exit();
        }
    }
} else {
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
    if (isset($_POST['update'])) {
        $returnDate = $_POST['return_date'];
        $items = $_POST['amount'];

        if (isset($_SESSION['user_login'])) {
            $user_id = $_SESSION['user_login'];
        }
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $firstname = $user['pre'] . $user['surname'] . '' . $user['lastname'];

            $checkreturnDate = date("Y-m-d", strtotime($returnDate));
            foreach ($_SESSION['cart'] as $item) {
                // Retrieve product details from the database based on the item
                $query = $conn->prepare("SELECT * FROM crud WHERE img = :item");
                $query->bindParam(':item', $item, PDO::PARAM_STR);
                $query->execute();
                $product = $query->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    $productName = $product['sci_name'];
                    $checkBookingsDate = strtotime($product['check_bookings']); // แปลงวันที่ check_bookings เป็น timestamp Unix
                    $currentDate = time(); // วันที่ปัจจุบันเป็น timestamp Unix
                    $checkreturnDate = strtotime($checkreturnDate); // สมมติว่า $checkreturnDate ถูกกำหนดไว้แล้ว
            
                    // ตรวจสอบว่าวันที่ปัจจุบันอยู่ก่อนวันที่ check_bookings
                    if ($currentDate < $checkBookingsDate && $checkreturnDate < $checkBookingsDate) {
                        // ดึงจำนวนของสินค้า
                        $quantity = isset($items[$item]) ? $items[$item] : 0;

                        // เพิ่มสินค้าไปยังรายการยืม
                        $itemList[] = $productName . ' (' . $quantity . ')';
                    } else {
                        // ยกเลิกกระบวนการยืมสำหรับสินค้านี้
                        echo "อุปกรณ์ " . $product['sci_name'] . ' มีคนจองวันที่ ' . date("d-m-Y", $checkBookingsDate) . ' ต้องยืมหรือคืนก่อนวันที่ ' . date("d-m-Y", $checkBookingsDate) . '<br>';
                        // คุณสามารถเพิ่มการดำเนินการเพิ่มเติมได้ที่นี่ถ้าจำเป็น
                    }
                }
            }

            //รวมรายการที่ยืมเป็นสตริงเดียวโดยคั่นด้วย comma
            if (!empty($itemList)) {
                $itemBorrowed = implode(', ', $itemList);

                // เพิ่มข้อมูลลงในฐานข้อมูล
                $insert_query = $conn->prepare("INSERT INTO waiting_for_approval (UDI, FirstName, ItemBorrowed, BorrowDateTime, ReturnDate, ApprovalDateTime, Approver, sn, situation) VALUES (:udi, :firstname, :itemBorrowed, NOW(), :returnDate, NULL, NULL, :random_string, NULL)");
                $insert_query->bindParam(':udi', $user_id, PDO::PARAM_INT);
                $insert_query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
                $insert_query->bindParam(':itemBorrowed', $itemBorrowed, PDO::PARAM_STR);
                $insert_query->bindParam(':returnDate', $returnDate, PDO::PARAM_STR);
                $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
                $insert_query->execute();

                unset($_SESSION['cart']);
                echo'รออนุมัติจากAdminนะครับ';
            }
        }
        else {
            echo "Error: User not found";
        }
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
    <a href="home.php">กลับหน้าหลัก</a>
</body>

</html>