<?php
session_start();
require_once '../assets/database/dbConfig.php';

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

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_username = $conn->prepare("SELECT username FROM users_db WHERE username = :username");
    $check_username->bindParam(":username", $username);
    $check_username->execute();
    if ($check_username->rowCount() > 0) {
        $_SESSION['errorSign_up'] = "Username นี้มีอยู่ในระบบแล้ว";
        header("location:../auth/sign_up");
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
        $_SESSION['errorSign_up'] = 'กรุณากรอก username';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($password)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกรหัสผ่าน';
        header("location:../auth/sign_up");
        exit;
    } elseif (strlen($password) < 8) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านต้องมีความยาวระหว่าง 8';
        header("location:../auth/sign_up");
        exit;
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/', $password) || !preg_match('/[a-zA-Z\d]/', $password)) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($confirmpassword)) {
        $_SESSION['errorSign_up'] = 'กรุณายืนยันรหัสผ่าน';
        header("location:../auth/sign_up");
        exit;
    } elseif ($password != $confirmpassword) {
        $_SESSION['errorSign_up'] = 'รหัสผ่านไม่ตรงกัน';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($role)) {
        $_SESSION['errorSign_up'] = 'กรุณาเลือกตำแหน่งของคุณ';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($surname)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกชื่อ';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($lastname)) {
        $_SESSION['errorSign_up'] = 'กรุณากรอกนามสกุล';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($phone_number)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่เบอร์โทรของคุณ';
        header("location:../auth/sign_up");
        exit;
    } elseif (!is_numeric($phone_number)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่เบอร์โทรให้เป็นตัวเลขเท่านั้น';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($email)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่ไอดี Line ของคุณ';
        header("location:../auth/sign_up");
        exit;
    } elseif (empty($agency)) {
        $_SESSION['errorSign_up'] = 'กรุณาใส่หน่วยงาน';
        header("location:../auth/sign_up");
        exit;
    } else {
        try {
            $check_lineid = $conn->prepare("SELECT lineid FROM users WHERE lineid  = :line_id");
            $check_lineid->bindParam(":email", $email);
            $check_lineid->execute();
            $row = $check_lineid->fetch(PDO::FETCH_ASSOC);

            if (isset($row['email']) && $row['email'] == $email) {
                $_SESSION['warning'] = "E-Mail มีในระบบ";
                header("location:../auth/sign_up");
                exit;
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, pre, surname, lastname, phone_number, email, role, agency, urole, status)
                    VALUES (:user_id, :username,:password, :pre, :surname, :lastname, :phone_number, :email, :role, :agency,:urole,:status)");
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":password", $passwordHash);
                $stmt->bindParam(":pre", $pre);
                $stmt->bindParam(":surname", $surname);
                $stmt->bindParam(":lastname", $lastname);
                $stmt->bindParam(":phone_number", $phone_number);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":role", $role);
                $stmt->bindParam(":agency", $agency);
                $stmt->bindParam(":urole", $urole);
                $stmt->bindParam(":status", $status);
                $stmt->execute();
                $_SESSION['successSign_up'] = "สมัครสมาชิกเรียบร้อยแล้ว";
                header("location:../auth/sign_in");
                exit;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
