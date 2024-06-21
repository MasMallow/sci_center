<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
require_once '../../assets/database/dbConfig.php';

if (isset($_POST['signup'])) {
    $userID = rand(10000, 99999);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $pre = $_POST['pre'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $agency = $_POST['agency'];
    $urole = 'user';
    $status = '0';

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_username = $conn->prepare("SELECT username FROM users_db WHERE username = :username");
    $check_username->bindParam(":username", $username);
    $check_username->execute();
    if ($check_username->rowCount() > 0) {
        $_SESSION['errorSign_up'] = "Username นี้มีอยู่ในระบบแล้ว";
        $_SESSION['form_values'] = $_POST;
        header("location: /sign_up");
        exit;
    }

    // ตรวจสอบข้อผิดพลาดและดำเนินการต่อ
    if (empty($username)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอก username';
    } elseif (empty($password)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกรหัสผ่าน';
    } elseif (strlen($password) < 8) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านต้องมีความยาวระหว่าง 8 ตัวอักษร';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/', $password)) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว';
    } elseif (empty($confirm_password)) {
        $_SESSION['errorSign_up'] = 'กรุณายืนยันรหัสผ่าน';
    } elseif ($password != $confirm_password) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านไม่ตรงกัน';
    } elseif (empty($role)) {
        $_SESSION['errorSign_up'] = 'กรุณาเลือกตำแหน่งของคุณ';
    } elseif (empty($firstname)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกชื่อ';
    } elseif (empty($lastname)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกนามสกุล';
    } elseif (empty($phone_number)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่เบอร์โทรของคุณ';
    } elseif (!is_numeric($phone_number)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่เบอร์โทรให้เป็นตัวเลขเท่านั้น';
    } elseif (empty($email)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่อีเมล์ของคุณ';
    } elseif (empty($agency)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่หน่วยงาน';
    } else {
        try {
            // ตรวจสอบ email ซ้ำ
            $check_email = $conn->prepare("SELECT email FROM users_db WHERE email = :email");
            $check_email->bindParam(":email", $email);
            $check_email->execute();
            $row = $check_email->fetch(PDO::FETCH_ASSOC);

            if (isset($row['email']) && $row['email'] == $email) {
                $_SESSION['errorSign_up'] = "E-Mail มีในระบบ";
                header("location: /sign_up");
                exit;
            } else {
                // แฮชรหัสผ่าน
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
                // เพิ่มข้อมูลใน users_info_db
                $stmt = $conn->prepare("INSERT INTO users_db (userID, username, password, created_at, pre, firstname, lastname, phone_number, email, role, agency, status)
                    VALUES (:userID, :username, :password, NOW(), :pre, :firstname, :lastname, :phone_number, :email, :role, :agency, :status)");
                $stmt->bindParam(":userID", $userID);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":password", $passwordHash);
                $stmt->bindParam(":pre", $pre);
                $stmt->bindParam(":firstname", $firstname);
                $stmt->bindParam(":lastname", $lastname);
                $stmt->bindParam(":phone_number", $phone_number);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":role", $role);
                $stmt->bindParam(":agency", $agency);
                $stmt->bindParam(":status", $status);
                $stmt->execute();

                $_SESSION['successSign_up'] = "สมัครสมาชิกเรียบร้อยแล้ว";
                header("location:/sign_in");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['errorSign_up'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            header("location: /sign_up");
            exit;
        }
    }
    
    // ถ้ามีข้อผิดพลาดให้เก็บค่า form เพื่อแสดงผลใหม่
    $_SESSION['form_values'] = $_POST;
    header("location: /sign_up");
    exit;
}
?>
