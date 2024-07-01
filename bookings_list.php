<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

try {
    // ตรวจสอบการล็อกอินของผู้ใช้
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            if ($userData['status'] == 'n_approved') {
                unset($_SESSION['user_login']);
                header('Location: /sign_in');
                exit();
            }
        }
    }

    // ตรวจสอบการล็อกอินของเจ้าหน้าที่
    if (isset($_SESSION['staff_login'])) {
        $user_id = $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูลการจอง
    if (isset($user_id)) {
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE userID = :user_id AND reservation_date >= CURDATE() AND date_return = NULL");
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
            <span id="B">รายการจอง</span>
        </div>
        <?php if (empty($bookings)) : ?>
            <div class="approve_not_found_section">
                <i class="fa-solid fa-xmark"></i>
                <span id="B">ไม่พบข้อมูลการจอง</span>
            </div>
        <?php else : ?>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }

                .bookingList_section {
                    width: 80%;
                    max-width: 800px;
                    margin: 20px auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                .booking_header,
                .booking_item {
                    display: flex;
                    align-items: center;
                    padding: 10px 0;
                    border-bottom: 1px solid #ddd;
                }

                .booking_header {
                    font-weight: bold;
                    background-color: #f9f9f9;
                }

                .booking_item:last-child {
                    border-bottom: none;
                }

                .booking_header div,
                .booking_item div {
                    flex: 1;
                    padding: 5px 10px;
                }

                .booking_item .checkbox,
                .booking_header .checkbox {
                    flex: 0 0 40px;
                    text-align: center;
                }

                .checkbox input[type="checkbox"] {
                    margin-right: 10px;
                }

                .booking_item button {
                    padding: 5px 10px;
                    color: #fff;
                    background-color: #dc3545;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }

                .booking_item button:hover {
                    background-color: #c82333;
                }

                .status {
                    padding: 5px 10px;
                    border-radius: 5px;
                    text-align: center;
                }

                .status_pending {
                    background-color: #ffc107;
                    color: #fff;
                }

                .status_approved {
                    background-color: #28a745;
                    color: #fff;
                }

                .status_used {
                    background-color: #17a2b8;
                    color: #fff;
                }
            </style>
            <form method="POST" action="<?php echo $base_url; ?>/SystemsUser/cancel_booking.php">
                <div class="bookingList_section">
                    <div class="booking_header">
                        <div class="serial_number">Serial Number</div>
                        <div>รายการ</div>
                        <div>วัน เวลาที่ทำรายการ</div>
                        <div>วัน เวลาที่จอง</div>
                        <div class="checkbox">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($bookings[0]['userID']); ?>">
                            <button type="submit">ยกเลิกการจอง</button>
                        </div>
                        <div>สถานะ</div>
                    </div>
                    <?php foreach ($bookings as $booking) : ?>
                        <div class="booking_item">
                            <div class="serial_number"><?= htmlspecialchars($booking['serial_number']); ?></div>
                            <div>
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
                            <div><?php echo thai_date_time($booking['created_at']); ?></div>
                            <div><?php echo thai_date_time($booking['reservation_date']); ?></div>
                            <div class="checkbox">
                                <input type="checkbox" name="booking_ids[]" value="<?php echo htmlspecialchars($booking['ID']); ?>">
                            </div>
                            <div>
                                <div class="status <?php
                                                    if ($booking['situation'] === null) {
                                                        echo 'status_pending">ยังไม่ได้รับอนุมัติ';
                                                    } elseif ($booking['situation'] == 1) {
                                                        echo 'status_approved">ได้รับการอนุมัติ';
                                                    } elseif ($booking['situation'] == 3) {
                                                        echo 'status_used">ได้ทำการขอใช้แล้ว';
                                                    }
                                                    ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
        <?php endif; ?>
    </div>
</body>

</html>