<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

if (!isset($_SESSION['user_login'])) {
    header("Location: /sign_in");
    exit();
}

$user_id = $_SESSION['user_login'];

try {
    // ดึงข้อมูลผู้ใช้และการจองในคำสั่งเดียวเพื่อลดจำนวนคำสั่ง SQL
    $stmt = $conn->prepare("
        SELECT u.*, b.* 
        FROM users_db u 
        LEFT JOIN approve_to_reserve b ON u.userID = b.userID 
        WHERE u.userID = :user_id AND (b.date_return IS NULL OR b.date_return IS NULL)
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$result) {
        unset($_SESSION['user_login']);
        header('Location: /sign_in');
        exit();
    }

    $userData = $result[0];
    $bookings = array_filter($result, function ($row) {
        return isset($row['reservation_date']);
    });

    if ($userData['status'] == 'n_approved') {
        unset($_SESSION['user_login']);
        header('Location: /sign_in');
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>รายการการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
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
                <?php if ($request_uri == '/list-request') echo '<a href="/list-request">รายการการขอใช้</a>'; ?>
            </div>
        </div>
        <?php if (empty($bookings)) : ?>
            <div class="approve_not_found_section">
                <i class="fa-solid fa-xmark"></i>
                <span id="B">ไม่พบข้อมูลการขอใช้</span>
            </div>
        <?php else : ?>
            <form method="POST" action="<?php echo $base_url; ?>/models/cancelReserve.php">
                <div class="list_request">
                    <?php foreach ($bookings as $booking) : ?>
                        <div class="list_request_content">
                            <div class="list_request_header">
                                <div class="list_request_header_1">
                                    <i class="icon fas fa-list"></i>
                                    <div class="serial_number"><span id="B">หมายเลขรายการ </span><?= htmlspecialchars($booking['serial_number']); ?></div>
                                </div>
                                <div class="list_request_header_2">
                                    <?php
                                    $situation = $booking['situation'];
                                    echo $situation === null ? '<div class="status_pending">ยังไม่ได้รับอนุมัติ</div>' : ($situation == 1 ? '<div class="status_approved">ได้รับอนุมัติ</div>' : '');
                                    ?>
                                </div>
                            </div>
                            <div class="list_request_details">
                                <div class="subtext_1">
                                    <?php
                                    $items = explode(',', $booking['list_name']);
                                    foreach ($items as $item) {
                                        list($product_name, $quantity) = explode('(', $item);
                                        echo htmlspecialchars(trim($product_name)) . ' <span id="B"> ' . htmlspecialchars(str_replace(')', '', $quantity)) . ' </span> รายการ<br>';
                                    }
                                    ?>
                                </div>
                                <div class="subtext">
                                    ขอใช้งาน<?= thai_date_time_2($booking['created_at']); ?>
                                    ถึง<?= thai_date_time_2($booking['reservation_date']); ?>
                                </div>
                            </div>
                            <?php if ($booking['reservation_date'] > date("Y-m-d")) : ?>
                                <div class="list_request_footer">
                                    <input type="hidden" name="reserveID" value="<?= htmlspecialchars($booking['ID']); ?>">
                                    <button class="cancel_request" type="submit">ยกเลิกการขอใช้</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>