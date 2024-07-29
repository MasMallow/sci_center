<?php
// var URL
$base_url = 'http://localhost:8080';

// var database
$servername = "localhost";
$username = "root";
$password = "";
$connname = "science_center_management";

// var connection database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$connname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // เพิ่มคำสั่งตั้งค่าการใช้งาน UTF-8 เพื่อป้องกันปัญหาการแสดงผลข้อมูล
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>