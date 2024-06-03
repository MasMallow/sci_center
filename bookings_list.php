<?php
session_start();
require_once 'assets/database/connect.php';
include 'includes/thai_date_time.php';
if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
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
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = :user_id AND reservation_date >= CURDATE()");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User's Bookings</title>
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
    <?php
    if (empty($bookings)) {
        echo "ไม่มีรายการจอง";
    } else {
    ?>
        <form method="POST" action="cancel_booking">
            <div class="maintenance_section">
                <div class="table_maintenace_section">
                    <table class="table_maintenace">
                        <thead>
                            <tr>
                                <th class="serial_number"><span id="B">Serial Number</span></th>
                                <th><span id="B">รายการ</span></th>
                                <th><span id="B">วัน เวลาที่ทำรายการ</span></th>
                                <th><span id="B">วัน เวลาจอง</span></th>
                                <th>
                                    <span id="B">ยกเลิกการจอง</span>
                                    <input type="hidden" name="user_id" value="<?php echo $bookings[0]['user_id']; ?>">
                                    <button type="submit">ยกเลิกการจอง</button>
                                </th>
                                <th><span id="B">สถานะ</span></th>
                            </tr>
                        </thead>
                        <?php foreach ($bookings as $booking) : ?>
                            <tbody>
                                <tr>
                                    <td class="serial_number"><?= $booking['serial_number'] ?></td>
                                    <td>
                                        <?php
                                        // แยกข้อมูล Item Borrowed
                                        $items = explode(',', $booking['list_name']);

                                        // แสดงข้อมูลรายการที่ยืม
                                        foreach ($items as $item) {
                                            $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                                            $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                                            $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                                            echo $product_name . ' <span id="B"> ' . $quantity . ' </span> รายการ<br>';
                                        }
                                        ?>
                                    <td><?php echo thai_date_time($booking['reservation_date']); ?></td>
                                    <td><?php echo thai_date_time($booking['created_at']); ?></td>
                                    <td>
                                        <input type="checkbox" name="booking_ids[]" value="<?php echo $booking['id']; ?>">
                                    </td>
                                    <td>
                                        <?php
                                        $checkBookingsDate = strtotime($booking['reservation_date']); // แปลงวันที่ check_bookings เป็น timestamp Unix
                                        $currentDate = time();

                                        if ($booking['situation'] == null) {
                                            echo 'ยังไม่ได้รับอนุมัติ';
                                        }  elseif (date('Y-m-d', $checkBookingsDate) == date('Y-m-d', $currentDate) && $booking['situation'] == 1) {
                                        ?>
                                            <button onclick="location.href='cart.php?action=add&item=<?php echo 0; ?>'" class="use-it">
                                                <i class="icon fa-solid fa-arrow-up"></i>
                                                <span>ขอใช้</span>
                                            </button>
                                        <?php
                                        }elseif ($booking['situation'] == 1) {
                                            echo 'ได้รับการอนุมัติ';
                                        }
                                        ?>
                                    </td>


                                </tr>
                            </tbody>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </form>
    <?php } ?>

</body>

</html>