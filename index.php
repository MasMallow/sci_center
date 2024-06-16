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
    case '/profile_user':
    case '/profile_user/edit_profile':
        require 'profile_user.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/approve_request':
        require 'Staff/approve_request.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/manage_users':
        require 'Staff/manage_users.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/management':
        require 'Staff/management.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/maintenance':
        require 'Staff/maintenance.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/view_report':
        require 'Staff/view_report.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/top_10_list':
        require 'Staff/top_10_list.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    default:
        require 'error_page.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
}
