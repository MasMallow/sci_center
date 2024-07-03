<?php
require_once 'assets/database/config.php';

// รับ URI ของคำขอปัจจุบัน
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ตรวจสอบว่า $request_uri เริ่มต้นด้วย $base_url หรือไม่
$request = (strpos($request_uri, $base_url) === 0) ? substr($request_uri, strlen($base_url)) : $request_uri;

$request = rtrim($request, '/');

// กำหนดเส้นทางคำขอตาม URI
switch ($request) {
    case '':
    case '/material':
    case '/equipment':
    case '/tools':
        require 'Home.php'; // หน้าแรก
        break;
    case '/details':
        require 'details.php'; // หน้ารายละเอียด
        break;
    case '/sign_in':
        require 'auth/sign_in.php'; // หน้าเข้าสู่ระบบ
        break;
    case '/sign_up':
        require 'auth/sign_up.php'; // หน้าลงทะเบียน
        break;
    case '/changePassword':
        require 'auth/change_password.php'; // หน้าลงทะเบียน
        break;
    case '/Cart':
        require 'Cart.php'; // ระบบตะกร้าสินค้า
        break;
    case '/StartProcess':
        require 'StartProcess.php'; // ระบบคืนสินค้า
        break;
    case '/EndProcess':
        require 'EndProcess.php'; // ระบบคืนสินค้า
        break;
    case '/CheckReserve':
        require 'CheckReserve.php'; // บันทึกการจอง
        break;
    case '/TrackingReserve':
        require 'TrackingReserve.php'; // รายการการจอง
        break;
    case '/notification':
        require 'notification.php'; // การแจ้งเตือน
        break;
    case '/profile_user':
    case '/edit_user':
    case '/profile_user/edit_profile':
        require 'profile_user.php'; // โปรไฟล์ผู้ใช้
        break;
    case '/approve_request':
    case '/approve_request/viewlog':
    case '/approve_request/viewlog/details':
        require 'staff-section/approve_request.php'; // อนุมัติคำขอ
        break;
    case '/manage_users':
    case '/management_user':
    case '/management_user/details':
    case '/undisapprove_user':
        require 'staff-section/manage_users.php'; // จัดการผู้ใช้
        break;
    case '/management':
    case '/management/material':
    case '/management/equipment':
    case '/management/tools':
        require 'staff-section/management.php'; // จัดการวัสดุอุปกรณ์
        break;
    case '/management/addData':
    case '/management/viewlog':
    case '/management/viewlog/details':
        require 'staff-section/addData.php'; // เพิ่มข้อมูล
        break;
    case '/management/editData':
        require 'staff-section/editData.php'; // แก้ไขข้อมูล
        break;
    case '/detailsData':
    case '/management/detailsData':
    case '/maintenance/detailsData':
        require 'staff-section/detailsData.php'; // รายละเอียดข้อมูล
        break;
    case '/maintenance':
    case '/maintenance/end_maintenance':
        require 'staff-section/maintenance.php'; // การบำรุงรักษา
        break;
    case '/maintenance/report_maintenance':
        require 'staff-section/report_maintenance.php'; // การบำรุงรักษา
        break;
    case '/view_report':
    case '/view_report/userID/startDate/endDate':
        require 'staff-section/view_report.php'; // ดูรายงาน
        break;
    case '/view_report/generate_pdf':
        require 'staff-section/generate_pdf.php'; // สร้าง PDF
        break;
    case '/view_top10':
        require 'staff-section/view_top10.php'; // ดูบันทึก
        break;
    default:
        require 'error_page.php'; // หน้าข้อผิดพลาด
        break;
}
