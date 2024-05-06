<?php 
session_start();
include_once 'assets/database/connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: auth/sign_in.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update'])) {
            $returnDate = $_POST['return_date'];
            $items = $_POST['amount'];
            $returnDates = $_POST['return_date'];
        }
        if (isset($_SESSION['user_login'])) {
            $user_id = $_SESSION['user_login'];
        } elseif (isset($_SESSION['admin_login'])) {
            $user_id = $_SESSION['admin_login'];
        }
        $user_query = $conn->prepare("SELECT firstname FROM users WHERE user_id = :user_id");
        $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $firstname = $user['firstname'];
        echo "ชื่อผู้ขอยืมอุปกรณ์////", $firstname;
        foreach ($_SESSION['cart'] as $item) {
            // Retrieve product details from the database based on the item
            $query = $conn->prepare("SELECT * FROM crud WHERE file_name = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);
            $productName = $product['product_name'];

            // Retrieve the quantity of the item
            $quantity = isset($items[$item]) ? $items[$item] : 0;

            // Append product details to sMessage
            echo "/////",$productName, "////จำนวน/////", $quantity;
        }
    }
    ?>

</body>

</html>