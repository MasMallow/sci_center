<?php
session_start();
require_once 'db.php';

if (isset($_POST['signup'])) {
    $Username = $_POST['Username'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];
    $firstname = $_POST['Firstname'];
    $lastname = $_POST['Lastname'];
    $role = $_POST['role'];
    $Lineid = $_POST['Lineid'];
    $Numberphone = $_POST['Numberphone'];
    $urole = 'user';

    if (empty($Username)) {
        $_SESSION['error'] = 'กรุณากรอกUsername';
        header("location:Register.php");
    } elseif (strlen($Username) < 6 || strlen($Username) > 12) {
        $_SESSION['error'] = 'Username ต้องมีความยาวระหว่าง 6 ถึง 12 ตัวอักษร';
        header("location:Register.php");
    } elseif (empty($Password)) {
        $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
        header("location:Register.php");
    } elseif (strlen($Password) > 12 || strlen($Password) < 8) {
        $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร';
        header("location:Register.php");
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/', $Password) || !preg_match('/[a-zA-Z\d]/', $Password)) {
        $_SESSION['error'] = 'รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว';
        header("location:Register.php");
    } elseif (empty($ConfirmPassword)) {
        $_SESSION['error'] = 'กรุณายืนยันรหัสผ่าน';
        header("location:Register.php");
    } elseif ($Password != $ConfirmPassword) {
        $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
        header("location:Register.php");
    } elseif (empty($role)) {
        $_SESSION['error'] = 'กรุณาเลือกตำแหน่งของคุณ';
        header("location:Register.php");
    } elseif (empty($firstname)) {
        $_SESSION['error'] = 'กรุณากรอกชื่อ';
        header("location:Register.php");
    } elseif (empty($lastname)) {
        $_SESSION['error'] = 'กรุณากรอกนามสกุล';
        header("location:Register.php");
    } elseif (empty($Numberphone)) {
        $_SESSION['error'] = 'กรุณาใส่เบอร์โทรของคุณ';
        header("location:Register.php");
    } elseif (empty($Lineid)) {
        $_SESSION['error'] = 'กรุณาใส่ไอดี Line ของคุณ';
        header("location:Register.php");
    } else {
        try {
            $check_lineid = $conn->prepare("SELECT lineid FROM users WHERE lineid  = :lineid");
            $check_lineid->bindParam(":lineid", $Lineid);
            $check_lineid->execute();
            $row = $check_lineid->fetch(PDO::FETCH_ASSOC);

            if (isset($row['lineid']) && $row['lineid'] == $Lineid) {
                $_SESSION['warning'] = "ไอดี Line นี้มีอยู่ในระบบแล้ว";
                header("location:Register.php");
            } elseif (isset($row['username']) && $row['username'] == $Username) {
                $_SESSION['warning'] = "Username นี้มีอยู่ในระบบแล้ว <a href='../login.php'>คลิกที่นี่</a>เพื่อเข้าสู่ระบบ";
                header("location:Register.php");
            } elseif (!isset($_SESSION['error'])) {
                $passwordHash = password_hash($Password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, firstname, lastname, lineid, password, urole, role, phone)
                VALUES (:Username, :Firstname, :Lastname, :Lineid, :Password, :Urole, :Role, :Phone)");
                $stmt->bindParam(":Username", $Username);
                $stmt->bindParam(":Firstname", $firstname);
                $stmt->bindParam(":Lastname", $lastname);
                $stmt->bindParam(":Lineid", $Lineid);
                $stmt->bindParam(":Password", $passwordHash);
                $stmt->bindParam(":Urole", $urole);
                $stmt->bindParam(":Role", $role);
                $stmt->bindParam(":Phone", $Numberphone);
                $stmt->execute();
                $_SESSION['success'] = "สมัครสมาชิกเรียบร้อยแล้ว <a href='login.html' class='alert-link'>คลิกที่นี่</a> เพื่อเข้าสู่ระบบ";
                header("location:Register.php");
            } else {
                $_SESSION['error'] = "มีบางอย่างผิดผลาด";
                header("location:Register.php");
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
