<?php
session_start();
require_once 'connect.php';
if (isset($_SESSION['user_login']) || isset($_SESSION['admin_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['admin_login'];

    // รับค่าที่แก้ไขจากฟอร์ม
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $urole = $_POST['urole'];
    $newUsername = $_POST['newUsername'];
    $newPassword = $_POST['newPassword']; // เพิ่มตัวแปรรหัสผ่านใหม่

    // ตรวจสอบว่ามีการเปลี่ยนแปลงรหัสผ่านหรือไม่
    $passwordUpdateQuery = '';
    if (!empty($newPassword)) {
        // ถ้ามีการเปลี่ยนแปลงรหัสผ่าน
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordUpdateQuery = ", password='$hashedPassword'";
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', urole='$urole', username='$newUsername' $passwordUpdateQuery WHERE id=$user_id";

    if ($db->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $db->error;
    }

    $db->close();
} else {
    echo "You are not logged in!";
}
?>