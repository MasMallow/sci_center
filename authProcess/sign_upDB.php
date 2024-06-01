<?php
session_start();
require_once '../assets/database/connect.php';

if (isset($_POST['signup'])) {
    $user_id = rand(10000, 99999);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    $pre = $_POST['pre'];
    $surname = $_POST['surname'];
    $lastname = $_POST['lastname'];
    $role = $_POST['role'];
    $line_id = $_POST['line_id'];
    $phone_number = $_POST['phone_number'];
    $agency = $_POST['agency'];
    $urole = 'user';
    $status = 'wait_approved';

    // $_SESSION['form_values'] = array(
    //     'username' => $username,
    //     'password' => $password,
    //     'confirmpassword' => $confirmpassword,
    //     'pre' => $pre,
    //     'surname' => $surname,
    //     'lastname' => $lastname,
    //     'role' => $role,
    //     'line_id' => $line_id,
    //     'phone_number' => $phone_number,
    //     'agency' => $agency
    // );


    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_username = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $check_username->bindParam(":username", $username);
    $check_username->execute();
    if ($check_username->rowCount() > 0) {
        $_SESSION['error1'] = "Username นี้มีอยู่ในระบบแล้ว";
        header("location:../auth/sign_up.php");
        $_SESSION['form_values'] = array(
            'password' => $password,
            'confirmpassword' => $confirmpassword,
            'pre' => $pre,
            'surname' => $surname,
            'lastname' => $lastname,
            'role' => $role,
            'line_id' => $line_id,
            'phone_number' => $phone_number,
            'agency' => $agency
        );
        exit;
    }
    // ตรวจสอบข้อผิดพลาดและดำเนินการต่อ
    if (empty($username)) {
        $_SESSION['error1'] = 'กรุณากรอก username';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($password)) {
        $_SESSION['error1'] = 'กรุณากรอกรหัสผ่าน';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (strlen($password) < 8) {
        $_SESSION['error1'] = 'รหัสผ่านต้องมีความยาวระหว่าง 8';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/', $password) || !preg_match('/[a-zA-Z\d]/', $password)) {
        $_SESSION['error1'] = 'รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($confirmpassword)) {
        $_SESSION['error1'] = 'กรุณายืนยันรหัสผ่าน';
        header("location:../auth/sign_up.php");
        exit;
    } elseif ($password != $confirmpassword) {
        $_SESSION['error1'] = 'รหัสผ่านไม่ตรงกัน';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($role)) {
        $_SESSION['error1'] = 'กรุณาเลือกตำแหน่งของคุณ';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($surname)) {
        $_SESSION['error1'] = 'กรุณากรอกชื่อ';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($lastname)) {
        $_SESSION['error1'] = 'กรุณากรอกนามสกุล';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($phone_number)) {
        $_SESSION['error1'] = 'กรุณาใส่เบอร์โทรของคุณ';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (!is_numeric($phone_number)) {
        $_SESSION['error1'] = 'กรุณาใส่เบอร์โทรให้เป็นตัวเลขเท่านั้น';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($line_id)) {
        $_SESSION['error1'] = 'กรุณาใส่ไอดี Line ของคุณ';
        header("location:../auth/sign_up.php");
        exit;
    } elseif (empty($agency)) {
        $_SESSION['error1'] = 'กรุณาใส่หน่วยงาน';
        header("location:../auth/sign_up.php");
        exit;
    } else {
        try {
            $check_lineid = $conn->prepare("SELECT lineid FROM users WHERE lineid  = :line_id");
            $check_lineid->bindParam(":line_id", $line_id);
            $check_lineid->execute();
            $row = $check_lineid->fetch(PDO::FETCH_ASSOC);

            if (isset($row['lineid']) && $row['lineid'] == $line_id) {
                $_SESSION['warning'] = "ไอดี Line นี้มีอยู่ในระบบแล้ว";
                header("location:../auth/sign_up.php");
                exit;
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, pre, surname, lastname, phone_number, lineid, role, agency, urole, status)
                    VALUES (:user_id, :username,:password, :pre, :surname, :lastname, :phone_number, :line_id, :role, :agency,:urole,:status)");
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":password", $passwordHash);
                $stmt->bindParam(":pre", $pre);
                $stmt->bindParam(":surname", $surname);
                $stmt->bindParam(":lastname", $lastname);
                $stmt->bindParam(":phone_number", $phone_number);
                $stmt->bindParam(":line_id", $line_id);
                $stmt->bindParam(":role", $role);
                $stmt->bindParam(":agency", $agency);
                $stmt->bindParam(":urole", $urole);
                $stmt->bindParam(":status", $status);
                $stmt->execute();
                $_SESSION['success'] = "สมัครสมาชิกเรียบร้อยแล้ว <a href='sign_in.php' class='alert-link'>คลิกที่นี่</a> เพื่อเข้าสู่ระบบ";
                header("location:../auth/sign_up.php");
                exit;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
