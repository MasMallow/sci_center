<?php
session_start();
require_once '../assets/database/connect.php';

if (isset($_POST['signup'])) {
    $Username = $_POST['Username'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];
    $pre = $_POST['pre'];
    $firstname = $_POST['Firstname'];
    $lastname = $_POST['Lastname'];
    $role = $_POST['role'];
    $Lineid = $_POST['Lineid'];
    $Numberphone = $_POST['Numberphone'];
    $Agency = $_POST['agency'];
    $urole = 'user';

    // สร้าง user_id ที่ไม่ซ้ำกัน
    $user_id = uniqid(10);

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_username = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $check_username->bindParam(":username", $Username);
    $check_username->execute();
    $username_exists = $check_username->fetch(PDO::FETCH_ASSOC);

    if ($username_exists) {
        $_SESSION['error1'] = "Username นี้มีอยู่ในระบบแล้ว";
        header("location:../auth/sign_up.php");
        exit; // หยุดการทำงานเพื่อป้องกันการทำงานเพิ่มเติม
    }

    // ตรวจสอบข้อผิดพลาดและดำเนินการต่อ
    if (empty($Username)) {
        $_SESSION['error1'] = 'กรุณากรอก Username';
        header("location:../auth/sign_up.php");
    } elseif (strlen($Username) < 6) {
        $_SESSION['error1'] = 'Username ต้องมีความยาวระหว่าง 6';
        header("location:../auth/sign_up.php");
    } elseif (empty($Password)) {
        $_SESSION['error1'] = 'กรุณากรอกรหัสผ่าน';
        header("location:../auth/sign_up.php");
    } elseif (strlen($Password) > 12 || strlen($Password) < 8) {
        $_SESSION['error1'] = 'รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร';
        header("location:../auth/sign_up.php");
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/', $Password) || !preg_match('/[a-zA-Z\d]/', $Password)) {
        $_SESSION['error1'] = 'รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว';
        header("location:../auth/sign_up.php");
    } elseif (empty($ConfirmPassword)) {
        $_SESSION['error1'] = 'กรุณายืนยันรหัสผ่าน';
        header("location:../auth/sign_up.php");
    } elseif ($Password != $ConfirmPassword) {
        $_SESSION['error1'] = 'รหัสผ่านไม่ตรงกัน';
        header("location:../auth/sign_up.php");
    } elseif (empty($role)) {
        $_SESSION['error1'] = 'กรุณาเลือกตำแหน่งของคุณ';
        header("location:../auth/sign_up.php");
    } elseif (empty($firstname)) {
        $_SESSION['error1'] = 'กรุณากรอกชื่อ';
        header("location:../auth/sign_up.php");
    } elseif (empty($lastname)) {
        $_SESSION['error1'] = 'กรุณากรอกนามสกุล';
        header("location:../auth/sign_up.php");
    } elseif (empty($Numberphone)) {
        $_SESSION['error1'] = 'กรุณาใส่เบอร์โทรของคุณ';
        header("location:../auth/sign_up.php");
    } elseif (!is_numeric($Numberphone)) {
        $_SESSION['error1'] = 'กรุณาใส่เบอร์โทรให้เป็นตัวเลขเท่านั้น';
        header("location:../auth/sign_up.php");
    } elseif (empty($Lineid)) {
        $_SESSION['error1'] = 'กรุณาใส่ไอดี Line ของคุณ';
        header("location:../auth/sign_up.php");
    } elseif (empty($agency)) {
        $_SESSION['error1'] = 'กรุณาใส่หน่วยงาน';
        header("location:../auth/sign_up.php");
    } else {
        try {
            $check_lineid = $conn->prepare("SELECT lineid FROM users WHERE lineid  = :lineid");
            $check_lineid->bindParam(":lineid", $Lineid);
            $check_lineid->execute();
            $row = $check_lineid->fetch(PDO::FETCH_ASSOC);

            if (isset($row['lineid']) && $row['lineid'] == $Lineid) {
                $_SESSION['warning'] = "ไอดี Line นี้มีอยู่ในระบบแล้ว";
                header("location:../auth/sign_up.php");
            } else {
                $passwordHash = password_hash($Password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_id, username, password, pre, firstname, lastname, phone, lineid, role, agency, urole)
                VALUES (:user_id, :Username,:Password, :pre, :Firstname, :Lastname, :Phone, :Lineid, :Role, :Agency,:Urole)");
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":Username", $Username);
                $stmt->bindParam(":Password", $passwordHash);
                $stmt->bindParam(":pre", $pre);
                $stmt->bindParam(":Firstname", $firstname);
                $stmt->bindParam(":Lastname", $lastname);
                $stmt->bindParam(":Phone", $Numberphone);
                $stmt->bindParam(":Lineid", $Lineid);
                $stmt->bindParam(":Role", $role);
                $stmt->bindParam(":Agency", $Agency);
                $stmt->bindParam(":Urole", $urole);
                $stmt->execute();
                $_SESSION['success'] = "สมัครสมาชิกเรียบร้อยแล้ว <a href='sign_in.php' class='alert-link'>คลิกที่นี่</a> เพื่อเข้าสู่ระบบ";
                header("location:../auth/sign_up.php");
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
?>