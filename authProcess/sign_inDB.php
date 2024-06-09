<?php
session_start();
require_once '../assets/database/connect.php';

if (isset($_POST['sign_in'])) {
    sleep(2);
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) && empty($password)) {
        $_SESSION['errorLogin'] = '<span id="B">กรุณาเข้าสู่ระบบ</span>';
        header("location: ../auth/sign_in");
        exit();
    } elseif (empty($username)) {
        $_SESSION['errorLogin'] = '<span id="B">กรุณากรอก Username</span>';
        header("location: ../auth/sign_in");
        exit();
    } elseif (empty($password)) {
        $_SESSION['errorLogin'] = '<span id="B">กรุณากรอก Password</span>';
        header("location: ../auth/sign_in");
        exit();
    } else {
        try {
            $check_data = $conn->prepare("SELECT * FROM users WHERE username = :username");
            $check_data->bindParam(":username", $username);
            $check_data->execute();
            $row = $check_data->fetch(PDO::FETCH_ASSOC);

            if ($check_data->rowCount() > 0) {
                if (password_verify($password, $row['password'])) {
                    if ($row['urole'] == 'staff') {
                        $_SESSION['staff_login'] = $row['user_id'];
                    } else {
                        $_SESSION['user_login'] = $row['user_id'];
                    }
                    header("location: ../");
                    exit();
                } else {
                    $_SESSION['errorLogin'] = '<span id="B">รหัสผ่านไม่ถูกต้อง</span>';
                    header("location: ../auth/sign_in");
                    exit();
                }
            } else {
                $_SESSION['errorLogin'] = '<span id="B">ไม่มีข้อมูลในระบบ</span>';
                header("location: ../auth/sign_in");
                exit();
            }
        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        }
    }
}
?>