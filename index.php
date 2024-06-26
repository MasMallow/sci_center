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
    case '/sign_in':
        require 'auth/sign_in.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/sign_up':
        require 'auth/sign_up.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
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
    case '/manage_users/management_user/edit_user':
        require 'profile_user.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/approve_request':
        require 'staff-section/approve_request.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/manage_users':
    case '/manage_users/management_user':
    case '/manage_users/undisapprove_user':
        require 'staff-section/manage_users.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/management':
    case '/management/material':
    case '/management/equipment':
    case '/management/tools':
        require 'staff-section/management.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/management/addData':
        require 'staff-section/addData.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/management/editData':
        require 'staff-section/editData.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/management/detailsData':
    case '/maintenance/detailsData':
        require 'staff-section/detailsData.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/maintenance':
    case '/maintenance/end_maintenance':
        require 'staff-section/maintenance.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/view_report':
    case '/view_report/userID/startDate/endDate':
        require 'staff-section/view_report.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/view_report/generate_pdf':
        require 'staff-section/generate_pdf.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    case '/view_log':
    case '/view_log/approve_request':
    case '/view_log/approve_users':
    case '/view_log/management':
    case '/view_log/maintenance':
    case '/view_log/view_report':
        require 'staff-section/view_log.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
    default:
        require 'error_page.php'; // ตรวจสอบว่าไฟล์นี้มีอยู่และไม่มีข้อผิดพลาด
        break;
}
