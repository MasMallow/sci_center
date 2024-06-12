<?php
session_start();
require_once 'assets/database/dbConfig.php';
include 'includes/thai_date_time.php';

try {
    // ตรวจสอบการล็อกอินของผู้ใช้
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            if ($userData['status'] == 'not_approved') {
                unset($_SESSION['user_login']);
                header('Location: auth/sign_in.php');
                exit();
            }
        }
    }

    // ตรวจสอบการล็อกอินของเจ้าหน้าที่
    if (isset($_SESSION['staff_login'])) {
        $user_id = $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูลการจอง
    if (isset($user_id)) {
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE user_ID = :user_id AND reservation_date >= CURDATE()");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $bookings = [];
    }
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดที่เกิดจากการเชื่อมต่อฐานข้อมูล
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ยกเลิกการจอง</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/bookings_list.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายการจอง</span>
        </div>
    </div>
    <?php if (empty($bookings)) : ?>
        <div class="approve_not_found_section">
            <i class="fa-solid fa-xmark"></i>
            <span id="B">ไม่พบข้อมูลการจอง</span>
        </div>
    <?php else : ?>
        <form method="POST" action="cancel_booking.php">
            <div class="maintenance_section">
                <div class="table_maintenace_section">
                    <table class="table_maintenace">
                        <thead>
                            <tr>
                                <th class="serial_number"><span id="B">Serial Number</span></th>
                                <th><span id="B">รายการ</span></th>
                                <th><span id="B">วัน เวลาที่ทำรายการ</span></th>
                                <th><span id="B">วัน เวลาที่จอง</span></th>
                                <th>
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($bookings[0]['user_id']); ?>">
                                    <button type="submit"><span id="B">ยกเลิกการจอง</span></button>
                                </th>
                                <th><span id="B">สถานะ</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking) : ?>
                                <tr>
                                    <td class="serial_number"><?= htmlspecialchars($booking['serial_number']); ?></td>
                                    <td>
                                        <?php
                                        // แยกรายการสินค้า
                                        $items = explode(',', $booking['list_name']);
                                        foreach ($items as $item) {
                                            $item_parts = explode('(', $item);
                                            $product_name = trim($item_parts[0]);
                                            $quantity = str_replace(')', '', $item_parts[1]);
                                            echo htmlspecialchars($product_name) . ' <span id="B"> ' . htmlspecialchars($quantity) . ' </span> รายการ<br>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo thai_date_time($booking['created_at']); ?></td>
                                    <td><?php echo thai_date_time($booking['reservation_date']); ?></td>
                                    <td>
                                        <input type="checkbox" name="booking_ids[]" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                    </td>
                                    <td>
                                        <?php
                                        if ($booking['situation'] === null) {
                                            echo 'ยังไม่ได้รับอนุมัติ';
                                        } elseif ($booking['situation'] == 1) {
                                            echo 'ได้รับการอนุมัติ';
                                        } elseif ($booking['situation'] == 3) {
                                            echo 'ได้ทำการขอใช้แล้ว';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    <?php endif; ?>
</body>

</html>