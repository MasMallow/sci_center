<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "science_center_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // เพิ่มคำสั่งตั้งค่าการใช้งาน UTF-8 เพื่อป้องกันปัญหาการแสดงผลข้อมูล
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>