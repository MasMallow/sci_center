<?php
session_start();
require_once '../../assets/database/connect.php'; // เรียกใช้งานไฟล์ที่เชื่อมต่อฐานข้อมูลด้วย PDO

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // รับค่าที่แก้ไขจากฟอร์ม
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $pre = $_POST['pre'];
    $surname = $_POST['surname'];
    $lastname = $_POST['lastname'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];
    $agency = $_POST['agency'];

    // ตรวจสอบว่ารหัสผ่านและการยืนยันรหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        echo "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
        exit(); // หยุดการทำงานต่อ
    }

    // ตรวจสอบว่ามีการเปลี่ยนแปลงรหัสผ่านหรือไม่
    $passwordUpdateQuery = '';
    $params = [
        'pre' => $pre,
        'surname' => $surname,
        'lastname' => $lastname,
        'phone_number' => $phone_number,
        'role' => $role,
        'agency' => $agency,
        'user_id' => $user_id
    ];

    if (!empty($password)) {
        // ถ้ามีการเปลี่ยนแปลงรหัสผ่าน
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $passwordUpdateQuery = ", password=:password";
        $params['password'] = $hashedPassword;
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE users SET pre=:pre, surname=:surname, lastname=:lastname, phone_number=:phone_number, role=:role, agency=:agency $passwordUpdateQuery WHERE user_id=:user_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        $_SESSION['edit_profile_success'] = "แก้ไขชื่อผู้ใช้สำเร็จ";
        header("Location: ../home");
    } else {
        $_SESSION['edit_profile_error'] = "!! เกิดข้อผิดพลาด ไม่สามารถแก้ไขผู้ใช้ได้";
        header("Location: ../home");
    }
}
