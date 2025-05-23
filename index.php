<?php
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';

// กำหนด base_path ให้ตรงกับโปรเจกต์ของคุณ (เช่น sci_center)
$base_path = "/"; // ถ้าโปรเจกต์อยู่ที่รากของโดเมน ใช้ "/"

// รับ REQUEST_URI และตัด Query String ออก
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ตัด $base_path ออกจาก $request_uri
if ($base_path !== "/" && strpos($request_uri, $base_path) === 0) {
    $request = substr($request_uri, strlen($base_path));
} else {
    $request = $request_uri;
}

switch ($request) {
    case '/':
    case '/material':
    case '/equipment':
    case '/tools':
    case '/notification':
        require 'views/home.php'; // หน้าแรก
        break;
    case '/sign_in':
        require 'views/sign_in.php'; // หน้าเข้าสู่ระบบ
        break;
    case '/sign_up':
        require 'views/sign_up.php'; // หน้าลงทะเบียน
        break;
    case '/changePassword':
        require 'views/change_password.php'; // หน้าลงทะเบียน
        break;
    case '/cart':
        require 'views/cart.php'; // ระบบตะกร้าสินค้า
        break;
    case '/UsedStart':
        require 'views/UsedStart.php'; // ระบบคืนสินค้า
        break;
    case '/UsedEnd':
        require 'views/UsedEnd.php'; // ระบบคืนสินค้า
        break;
    case '/calendar':
        require 'views/calendar.php'; // บันทึกการจอง
        break;
    case '/list-request':
        require 'views/list_requestUse.php'; // รายการการจอง
        break;
    case '/qrcode':
    case '/qrcode-staff':
        require 'views/qrcode.php'; // รายการการจอง
        break;
    case (preg_match('/^\/reservation_details\/(\d{4}-\d{2}-\d{2})$/', $request, $matches) ? true : false):
        $_GET['day_date'] = $matches[1];
        require 'views/ReservationDetails.php'; // บันทึกการจอง
        break;
    case (preg_match('/^\/details\/(\d+)$/', $request, $matches) ? true : false):
        $_GET['id'] = $matches[1];
        require 'views/details.php'; // หน้ารายละเอียด
        break;
    case '/profile_user':
    case '/profile_user/edit_profile':
        require 'views/profile_user.php'; // โปรไฟล์ผู้ใช้
        break;
    case '/edit_user':
        require 'views/staff-section/editUser.php'; // โปรไฟล์ผู้ใช้
        break;
    case '/approve_request':
    case '/approve_request/calendar':
    case '/approve_request/calendar/details':
        require 'views/staff-section/approve_request.php'; // อนุมัติคำขอ
        break;
    case '/management-website':
    case '/management-website/add':
    case '/management-website/edit':
        require 'views/staff-section/manageWebsite.php'; // อนุมัติคำขอ
        break;
    case (preg_match('/^\/approve_request\/reservation_details\/(\d{4}-\d{2}-\d{2})$/', $request, $matches) ? true : false):
        $_GET['day_date'] = $matches[1];
        require 'views/ReservationDetails.php'; // รายละเอียดการจอง
        break;
    case '/manage_users':
    case '/management_user':
    case '/management_user/details':
    case '/undisapprove_user':
        require 'views/staff-section/manage_users.php'; // จัดการผู้ใช้
        break;
    case '/management':
    case '/management/material':
    case '/management/equipment':
    case '/management/tools':
        require 'views/staff-section/management.php'; // จัดการวัสดุอุปกรณ์
        break;
    case '/management/addData':
    case '/management/viewlog':
    case '/management/viewlog/details':
        require 'views/staff-section/addData.php'; // เพิ่มข้อมูล
        break;
    case '/management/edit':
        require 'views/staff-section/editData.php'; // แก้ไขข้อมูล
        break;
    case '/detailsData':
    case '/management/detailsData':
        require 'views/staff-section/detailsData.php'; // รายละเอียดข้อมูล
        break;
    case '/management/maintenance':
    case '/maintenance/details':
    case '/maintenance_start/details':
    case '/maintenance_end/details':
        require 'views/staff-section/detailsMaintenance.php'; // รายละเอียดข้อมูล
        break;
    case '/maintenance_dashboard':
    case '/maintenance_start':
    case '/maintenance_end':
    case '/list-name':
        require 'views/staff-section/maintenance.php'; // การบำรุงรักษา
        break;
    case '/maintenance/report':
        require 'views/staff-section/maintenanceReport.php'; // การบำรุงรักษา
        break;
    case '/report':
        require 'views/staff-section/view_report.php'; // ดูรายงาน
        break;
    case '/view_report/generate_pdf':
        require 'views/staff-section/generate_pdf.php'; // สร้าง PDF
        break;
    case '/top10':
    case '/history/material':
    case '/top10/equipment':
    case '/top10/tools':
        require 'views/staff-section/view_top10.php'; // ดูบันทึก
        break;
    default:
        http_response_code(404);
        require 'views/error_page.php'; // หน้าข้อผิดพลาด
        break;
}
?>