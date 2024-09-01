<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';
include_once 'models/UserCheck.php';

try {
    // ดึงวันที่จาก URL
    $uri_segments = explode('/', $request_uri);
    $reservation_date = end($uri_segments); // ดึงส่วนสุดท้ายของ URI

    // ตรวจสอบรูปแบบวันที่
    $date = DateTime::createFromFormat('Y-m-d', $reservation_date);
    if (!$date) {
        $date = DateTime::createFromFormat('Y-n-j', $reservation_date);
    }

    if ($date) {
        $reservation_date = $date->format('Y-m-d'); // ทำให้แน่ใจว่ารูปแบบเป็นมาตรฐาน

        // ดึงข้อมูลการจองสำหรับวันที่ที่ระบุ
        $sql = "SELECT * FROM approve_to_reserve WHERE DATE(reservation_date) = :reservation_date AND situation = 1 AND date_return IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':reservation_date', $reservation_date);
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        throw new Exception("รูปแบบวันที่ไม่ถูกต้อง.");
    }
} catch (PDOException $e) {
    error_log("ข้อผิดพลาด PDO: " . $e->getMessage());
    echo "เกิดข้อผิดพลาด: กรุณาลองใหม่อีกครั้ง";
} catch (Exception $e) {
    error_log("ข้อผิดพลาดทั่วไป: " . $e->getMessage());
    echo "เกิดข้อผิดพลาด: กรุณาลองใหม่อีกครั้ง";
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/reservation_details.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <!-- -------------- HEADER -------------- -->
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>

    <!-- -------------- CALENDAR -------------- -->
    <main class="reservation_details">
        <div class="reservation_details_header">
            <a class="historyBACK" href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if (strpos($request_uri, '/approve_request/reservation_details/') !== false) : ?>
                    <a href="/approve_request/calendar">ปฎิทินการขอใช้</a>
                    <span>&gt;</span>
                <?php endif; ?>
                <a href="<?php echo ($reservation_date); ?>">ขอใช้<?php echo thai_date_time_3($reservation_date); ?></a>
            </div>
        </div>
        <div class="bookingTable_content">
            <?php if (!empty($reservations)) : ?>
                <?php foreach ($reservations as $reservation) : ?>
                    <div class="bookingDetails_content">
                        <div class="reservation-details">
                            <div class="reservationIcon">
                                <i class="fa-solid fa-address-book"></i>
                                <?php echo ($reservation['serial_number']); ?>
                            </div>
                            <div class="reservetionDetails">
                                <?php
                                $items = explode(',', $reservation['list_name']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = str_replace(')', '', $item_parts[1]);
                                    echo $product_name . " " . $quantity . " รายการ <br>";
                                }
                                ?>
                            </div>
                            <div class="reservetionDetails_Date">
                                <span id="B">ตั้งแต่</span>
                                <?php echo thai_date_time_2($reservation['reservation_date']); ?>
                                <span id="B">ถึง</span>
                                <?php echo thai_date_time_2($reservation['end_date']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="nFound">ไม่มีการขอใช้ในวันที่เลือก</div>
    <?php endif; ?>
    </main>

    <!-- -------------- FOOTER -------------- -->
    <footer>
        <?php include_once 'assets/includes/footer.php'; ?>
    </footer>

    <!-- -------------- JavaScript -------------- -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>