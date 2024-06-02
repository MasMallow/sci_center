<?php
session_start();
require_once 'assets/database/connect.php';

if (!isset($conn)) {
    die('Database connection failed.');
}

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
    exit();
}

$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$random_string = '';
for ($i = 0; $i < 7; $i++) {
    $random_index = mt_rand(0, strlen($characters) - 1);
    $random_string .= $characters[$random_index];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reservation'])) {
        $reservationdate = $_POST['reservation_date'];
        $items = $_POST['amount'] ?? [];

        $user_query = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $firstname = $user['pre'] . $user['surname'] . ' ' . $user['lastname'];

        $itemList = [];
        foreach ($_SESSION['reserve_cart'] as $item) {
            $query = $conn->prepare("SELECT * FROM crud WHERE img = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $productName = $product['sci_name'];
                $quantity = isset($items[$item]) ? (int)$items[$item] : 0;
                $itemList[] = htmlspecialchars($productName) . ' (' . $quantity . ')';
            }
        }

        $itemBorrowed = implode(', ', $itemList);

        $insert_query = $conn->prepare("INSERT INTO bookings 
            (user_id, firstname, list_name, created_at, reservation_date, approvaldatetime, approver, serial_number, situation) VALUES 
            (:user_id, :firstname, :itemBorrowed, NOW(), :reservationdate, NULL, NULL, :random_string, NULL)");
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
