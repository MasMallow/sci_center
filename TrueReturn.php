<?php
session_start();
include_once 'connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
} elseif (isset($_SESSION['admin_login'])) {
    $user_id = $_SESSION['admin_login'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['return_item']) && is_array($_POST['return_item'])) {
        $selectedItems = $_POST['return_item'];

        foreach ($selectedItems as $item) {
            // ดึงข้อมูลจาก borrow_history ที่ต้องการไปใช้
            $stmt_borrow = $conn->prepare("SELECT return_date FROM borrow_history WHERE user_id = :user_id AND product_name = :product_name");
            $stmt_borrow->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_borrow->bindParam(':product_name', $item, PDO::PARAM_STR);
            $stmt_borrow->execute();
            $borrow_row = $stmt_borrow->fetch(PDO::FETCH_ASSOC);

            // เพิ่มข้อมูลลงใน return_history โดยใช้ข้อมูลจาก borrow_history
            $return_date = $borrow_row['return_date'];

            $stmt_insert = $conn->prepare("INSERT INTO return_history (user_id, product_name, return_date, In_return_date) VALUES (:user_id, :product_name, :return_date, NOW())");
            $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':product_name', $item, PDO::PARAM_STR);
            $stmt_insert->bindParam(':return_date', $return_date, PDO::PARAM_STR);
            $stmt_insert->execute();
        }

        header("Location: ajax.php");
        exit();
    }
}
?>