<?php
// เริ่มเซสชันและรวมไฟล์ที่จำเป็น
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID    
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == 'n_approved') {
            unset($_SESSION['user_login']);
            header('Location: /sign_in');
            exit();
        } elseif ($userData['status'] == 'w_approved') {
            unset($_SESSION['reserve_cart']);
            header('Location: /');
            exit();
        }
    }
} else {
    header("Location: /sign_in");
    exit();
}

try {
    // ดึงวันที่จาก URL
    $request_uri = $_SERVER['REQUEST_URI'];
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bookingTable.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="bookingTable">
        <div class="bookingTable_header">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">รายละเอียดการขอใช้<?php echo thai_date_time_3($reservation_date); ?></span>
        </div>
        <div class="bookingTable_content">
            <div class="reservation-details">
                <?php if (!empty($reservations)) : ?>
                    <?php foreach ($reservations as $reservation) : ?>
                        <div class="reservationIcon">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div class="reservetionDetails">
                            <div class="reservetionDetails_Name">
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
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>ไม่มีการขอใช้ในวันที่เลือก</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <?php include_once 'assets/includes/footer.php'; ?>
    </footer>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>