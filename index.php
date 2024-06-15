<?php
require_once 'assets/database/dbConfig.php';

$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ตรวจสอบว่า $request_uri เริ่มต้นด้วย $base_url หรือไม่
if (strpos($request_uri, $base_url) === 0) {
    $request = substr($request_uri, strlen($base_url));
} else {
    $request = $request_uri; // ถ้าไม่เริ่มต้นด้วย $base_url ให้ใช้ $request_uri เดิม
}

$request = rtrim($request, '/');

switch ($request) {
    case '':
        require 'home.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/cart_systems':
        require 'cart_systems.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/returned_system':
        require 'returned_system.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/booking_log':
        require 'booking_log.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/bookings_list':
        require 'bookings_list.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/notification':
        require 'notification.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    default:
        require 'error_page.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
}
