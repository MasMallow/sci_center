<?php
session_start();
require_once '../assets/config/Database.php'; // เรียกใช้งานไฟล์ที่เชื่อมต่อฐานข้อมูลด้วย PDO

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // รับค่าที่แก้ไขจากฟอร์ม
    $userID = $_POST['userID'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $pre = $_POST['pre'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];
    $agency = $_POST['agency'];

    // ตรวจสอบว่ารหัสผ่านและการยืนยันรหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        $_SESSION['edit_profile_error'] = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
        header("Location: /edit_user?id=" . urlencode($userID));
        exit();
    }

    // ตรวจสอบว่ามีการเปลี่ยนแปลงรหัสผ่านหรือไม่
    $passwordUpdateQuery = '';
    $params = [
        'pre' => $pre,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'phone_number' => $phone_number,
        'role' => $role,
        'agency' => $agency,
        'user_id' => $userID
    ];

    if (!empty($password)) {
        // ถ้ามีการเปลี่ยนแปลงรหัสผ่าน
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $passwordUpdateQuery = ", password=:password";
        $params['password'] = $hashedPassword;
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE users_db 
            SET pre=:pre, firstname=:firstname, lastname=:lastname, phone_number=:phone_number, role=:role, agency=:agency $passwordUpdateQuery 
            WHERE userID=:user_id";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $_SESSION['edit_profile_success'] = "แก้ไขชื่อผู้ใช้สำเร็จ";
            header("Location: /edit_user?id=" . urlencode($userID));
            exit();
        } else {
            $_SESSION['edit_profile_error'] = "!! เกิดข้อผิดพลาด ไม่สามารถแก้ไขผู้ใช้ได้";
            header("Location: /edit_user?id=" . urlencode($userID));
            exit();
        }
    } catch (PDOException $e) {
        // จัดการข้อผิดพลาดที่เกิดจากการเชื่อมต่อฐานข้อมูล
        $_SESSION['edit_profile_error'] = 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: /edit_user?id=" . urlencode($userID));
        exit();
    }
} else {
    echo "การเข้าสู่ระบบหมดอายุ กรุณาลงชื่อเข้าใช้ใหม่";
    exit();
}
