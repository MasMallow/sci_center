<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

try {
    // ตรวจสอบการล็อกอินของผู้ใช้
    if (!isset($_SESSION['user_login'])) {
        header("Location: /sign_in");
        exit();
    }

    $user_id = $_SESSION['user_login'];

    // ดึงข้อมูลผู้ใช้
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData || $userData['status'] == 'n_approved') {
        unset($_SESSION['user_login']);
        header('Location: /sign_in');
        exit();
    }

    // ดึงข้อมูลการจอง
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE userID = :user_id AND reservation_date >= CURDATE() AND date_return IS NULL");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดที่เกิดจากการเชื่อมต่อฐานข้อมูล
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ยกเลิกการขอใช้งาน</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bookings_list.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <div class="bookingList">
        <div class="bookingList_header">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">รายการการขอใช้</span>
        </div>
        <?php if (empty($bookings)) : ?>
            <div class="approve_not_found_section">
                <i class="fa-solid fa-xmark"></i>
                <span id="B">ไม่พบข้อมูลการขอใช้</span>
            </div>
        <?php else : ?>
            <form method="POST" action="<?php echo $base_url; ?>/backend/cancelReserve.php">
                <div class="bookingList_Content">
                    <?php if (!empty($bookings)) : ?>
                        <?php foreach ($bookings as $booking) : ?>
                            <div class="bookingList_Item">
                                <div class="bookingList_body">
                                    <div class="icon_bookingList">
                                        <i class="icon fas fa-list"></i>
                                    </div>
                                    <div class="bookingList_Details">
                                        <div class="serial_number"><span id="B">หมายเลขรายการ </span>
                                            <?= htmlspecialchars($booking['serial_number']); ?>
                                        </div>
                                        <div class="subtext">
                                            <?php
                                            $items = explode(',', $booking['list_name']);
                                            foreach ($items as $item) {
                                                $item_parts = explode('(', $item);
                                                $product_name = trim($item_parts[0]);
                                                $quantity = str_replace(')', '', $item_parts[1]);
                                                echo htmlspecialchars($product_name) . ' <span id="B"> ' . htmlspecialchars($quantity) . ' </span> รายการ<br>';
                                            }
                                            ?>
                                        </div>
                                        <div class="subtext"><span id="B">ขอใช้งาน </span><?= thai_date_time_2($booking['created_at']); ?></div>
                                        <div class="subtext"><span id="B">ถึง </span> <?= thai_date_time_2($booking['reservation_date']); ?></div>
                                        <div class="status">
                                            <?php
                                            $situation = $booking['situation'];
                                            if ($situation === null) {
                                                echo '<div class="status_pending">ยังไม่ได้รับอนุมัติ</div>';
                                            } elseif ($situation == 1) {
                                                echo '<div class="status_approved">ได้รับอนุมัติ</div>';
                                            }
                                            ?>
                                        </div>
                                        <div class="checkbox">
                                            <input type="hidden" name="reserveID" value="<?php echo htmlspecialchars($booking['ID']); ?>">
                                            <button type="submit">ยกเลิกการจอง</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>