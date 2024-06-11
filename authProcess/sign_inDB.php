<?php
session_start();
require_once '../assets/database/dbConfig.php';

function getIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // In case there are multiple IPs, take the first one
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

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
            $check_data = $conn->prepare("SELECT * FROM users_db WHERE username = :username");
            $check_data->bindParam(":username", $username);
            $check_data->execute();
            $row = $check_data->fetch(PDO::FETCH_ASSOC);

            if ($check_data->rowCount() > 0) {
                if (password_verify($password, $row['password'])) {
                    if ($row['urole'] == 'staff') {
                        $_SESSION['staff_login'] = $row['user_ID'];
                    } else {
                        $_SESSION['user_login'] = $row['user_ID'];
                    }

                    // Log the login attempt
                    $authID = $row['user_ID'];
                    $log_Name = $row['pre']  . $row['firstname'] . ' ' . $row['lastname'];
                    $log_Date = date('Y-m-d H:i:s');
                    $log_IP = getIP();

                    $log_query = $conn->prepare("INSERT INTO logs_user (authID, log_Name, log_Date, log_IP) VALUES (:authID, :log_Name, :log_Date, :log_IP)");
                    $log_query->bindParam(':authID', $authID);
                    $log_query->bindParam(':log_Name', $log_Name);
                    $log_query->bindParam(':log_Date', $log_Date);
                    $log_query->bindParam(':log_IP', $log_IP);
                    $log_query->execute();

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
