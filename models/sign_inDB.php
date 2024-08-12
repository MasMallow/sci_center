<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
require_once '../assets/config/Database.php';

if (isset($_POST['sign_in'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['errorLogin'] = '<span id="B">กรุณากรอก Username และ Password</span>';
        header("Location: /sign_in");
        exit();
    }

    try {
        $check_data = $conn->prepare("SELECT * FROM users_db WHERE username = :username");
        $check_data->bindParam(":username", $username);
        $check_data->execute();
        $row = $check_data->fetch(PDO::FETCH_ASSOC);

        if ($check_data->rowCount() > 0) {
            if (password_verify($password, $row['password'])) {
                // Log the login attempt
                $authID = $row['userID'];
                $log_Name = $row['pre'] . $row['firstname'] . ' ' . $row['lastname'];
                $log_Date = date('Y-m-d H:i:s');

                $log_query = $conn->prepare("INSERT INTO logs_user (authID, log_Name, log_Date) VALUES (:authID, :log_Name, :log_Date)");
                $log_query->bindParam(':authID', $authID);
                $log_query->bindParam(':log_Name', $log_Name);
                $log_query->bindParam(':log_Date', $log_Date);
                $log_query->execute();

                // Set session and redirect based on user role
                if ($row['urole'] == 'staff') {
                    $_SESSION['staff_login'] = $row['userID'];
                    header("Location: /");
                } else {
                    $_SESSION['user_login'] = $row['userID'];
                    header("Location: /");
                }
                exit(); // Exit after redirection
            } else {
                $_SESSION['errorLogin'] = '<span id="B">รหัสผ่านไม่ถูกต้อง</span>';
            }
        } else {
            $_SESSION['errorLogin'] = '<span id="B">ไม่มีข้อมูลในระบบ</span>';
        }
    } catch (PDOException $e) {
        $_SESSION['errorLogin'] = '<span id="B">Database error: ' . $e->getMessage() . '</span>';
    }

    header("Location: /sign_in");
    exit();
}
?>
