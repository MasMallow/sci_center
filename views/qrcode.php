<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['user_login']) && !isset($_SESSION['staff_login'])) {
    header("Location: /sign_in");
    exit();
}

try {
    // ใช้ `user_login` หรือ `staff_login` จากเซสชัน
    $userID = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        // ไม่มีข้อมูลผู้ใช้ในฐานข้อมูล
        unset($_SESSION['user_login']);
        unset($_SESSION['staff_login']);
        header('Location: /sign_in');
        exit();
    }

    // ตรวจสอบสถานะของผู้ใช้ถ้าจำเป็น
    if ($userData['status'] === 'n_approved') {
        unset($_SESSION['user_login']);
        unset($_SESSION['staff_login']);
        header('Location: /sign_in');
        exit();
    }
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>รายการการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/qrcode.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/list-requestUSE.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <div class="bookingList">
        <div class="bookingList_header">
            <a class="historyBACK" href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php if ($request_uri == '/qrcode') : ?>
                    <a href="/qrcode">QR CODE</a>
                <?php elseif ($request_uri == '/qrcode-staff') : ?>
                    <a href="/qrcode">QR CODE</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($request_uri == '/qrcode') : ?>
            <div class="centered-image">
                <span>QR-CODE ไลน์บอท SCI-Center</span>
                <img src="<?php echo $base_url ?>/assets/img/qr_code_user/qrcode.png" alt="">
            </div>
        <?php elseif ($request_uri == '/qrcode-staff') : ?>
            <div class="centered-image">
                <span>QR-CODE ไลน์กลุ่มแจ้งเตือน</span>
                <img src="<?php echo $base_url ?>/assets/img/qr_code_staff/qrcode.jpg" alt="">
            </div>
        <?php endif; ?>
    </div>
</body>

</html>