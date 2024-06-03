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

$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$random_string = '';

for ($i = 0; $i < 7; $i++) {
    $random_index = mt_rand(0, strlen($characters) - 1);
    $random_string .= $characters[$random_index];
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
            $firstname = $user['pre'] . $user['surname'] . ' ' . $user['lastname'];

            $checkreturnDate = date("Y-m-d", strtotime($returnDate));
            $itemList = []; // Initialize the item list
            $errorMessages = []; // Initialize the error message array

            foreach ($_SESSION['cart'] as $item) {
                $query = $conn->prepare("SELECT * FROM crud WHERE img = :item");
                $query->bindParam(':item', $item, PDO::PARAM_STR);
                $query->execute();
                $product = $query->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    $productName = $product['sci_name'];
                    $checkBookingsDate = strtotime($product['check_bookings']);
                    $currentDate = time();
                    $checkreturnDate = strtotime($checkreturnDate);
                    $quantity = isset($items[$item]) ? (int)$items[$item] : 0;

                    if ($currentDate < $checkBookingsDate && $checkreturnDate < $checkBookingsDate || $product['check_bookings'] == NULL) {
                        if ($quantity <= $product['amount']) {
                            $itemList[] = $productName . ' (' . $quantity . ')';
                        } else {
                            $errorMessages[] = "อุปกรณ์ " . $productName . " มีจำนวนไม่เพียงพอ (มีเพียง " . $product['amount'] . " ชิ้นในสต็อก)";
                            echo '<a href="cart.php">กลับหน้าตะกร้า</a><br>';
                        }
                    } else {
                        $errorMessages[] = "อุปกรณ์ " . $product['sci_name'] . ' มีคนจองวันที่ ' . date("d-m-Y", $checkBookingsDate) . ' ต้องยืมหรือคืนก่อนวันที่ ' . date("d-m-Y", $checkBookingsDate);
                    }
                }
            }

            if (!empty($errorMessages)) {
                foreach ($errorMessages as $message) {
                    echo $message . '<br>';
                }
            } else {
                if (!empty($itemList)) {
                    $itemBorrowed = implode(', ', $itemList);
                    $insert_query = $conn->prepare("INSERT INTO approve_to_use (UDI, FirstName, ItemBorrowed, BorrowDateTime, ReturnDate, ApprovalDateTime, Approver, sn, situation) VALUES (:udi, :firstname, :itemBorrowed, NOW(), :returnDate, NULL, NULL, :random_string, NULL)");
                    $insert_query->bindParam(':udi', $user_id, PDO::PARAM_INT);
                    $insert_query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
                    $insert_query->bindParam(':itemBorrowed', $itemBorrowed, PDO::PARAM_STR);
                    $insert_query->bindParam(':returnDate', $returnDate, PDO::PARAM_STR);
                    $insert_query->bindParam(':random_string', $random_string, PDO::PARAM_STR);
                    $insert_query->execute();

                    unset($_SESSION['cart']);
                    echo 'รออนุมัติจาก Admin นะครับ';
                    echo '<a href="home.php">กลับหน้าหลัก</a>';
                }
            }
        } else {
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
    
</body>

</html>
