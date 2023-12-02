<?php
session_start();
require_once 'db.php';

if (isset($_POST['sign-in'])) {
    $Username = $_POST['Username'];
    $Password = $_POST['Password'];

    if (empty($Username) && empty($Password)) {
        $_SESSION['error1'] = 'กรุณาเข้าสู่ระบบ';
        header("location:login.php");
    } elseif (empty($Username)) {
        $_SESSION['error2'] = 'กรุณากรอก Username';
        header("location:login.php");
    } elseif (empty($Password)) {
        $_SESSION['error3'] = 'กรุณากรอก Password';
        header("location:login.php");
    } else {
        try {
            $check_data = $conn->prepare("SELECT * FROM users WHERE username  = :username");
            $check_data->bindParam(":username", $Username);
            $check_data->execute();
            $row = $check_data->fetch(PDO::FETCH_ASSOC);
            var_dump($Username, $row['username']);
            if ($check_data->rowCount() > 0) {
                if ($Username == $row['username']) {
                    if (password_verify($Password, $row['password'])) {
                        if ($row['urole'] == 'admin') {
                            $_SESSION['admin_login'] = $row['id'];
                            header("location: ajax.php");
                        } else {
                            $_SESSION['user_login'] = $row['id'];
                            header("location: ajax.php");
                        }
                    } else {
                        $_SESSION['error1'] = 'รหัสผ่านไม่ถูกต้อง';
                        header("location: login.php");
                    }
                } else {
                    $_SESSION['error1'] = 'Username ไม่ถูกต้อง';
                    header("location: login.php");
                }
            } else {
                $_SESSION['error1'] = "ไม่มีข้อมูลในระบบ";
                header("location: login.php");
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
