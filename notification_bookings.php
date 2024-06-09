<?php
session_start();
require_once 'assets/database/connect.php';
include_once 'includes/thai_date_time.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            header("Location: home");
            exit();
        }
    } else {
        $_SESSION['error'] = 'ผู้ใช้ไม่พบ!';
        header('Location: auth/sign_in');
        exit();
    }
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in');
    exit();
}

// ตรวจสอบว่ามีข้อมูลผู้ใช้หรือไม่
$firstname = $userData['pre'] . $userData['surname'] . ' ' . $userData['lastname'];
$stmt = $conn->prepare("SELECT * FROM approve_to_bookings WHERE firstname = :firstname");
$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเตือนการจอง</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/nofitication.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">แจ้งเตือนการจอง</span>
        </div>
    </div>
    <div class="notification_section">
        <?php if (!empty($data)) : ?>
            <table class="table_notification">
                <thead>
                    <tr>
                        <th class="serial_number"><span id="B">หมายเลขรายการ</span></th>
                        <th><span id="B">รายการที่ขอจอง</span></th>
                        <th><span id="B">วันเวลาที่ทำรายการ</span></th>
                        <th><span id="B">วันเวลาที่ขอจอง</span></th>
                        <th><span id="B">สถานะ</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row) : ?>
                        <tr>
                            <td class="serial_number"><?php echo htmlspecialchars($row['serial_number']); ?></td>
                            <td>
                                <?php
                                $items = explode(',', $row['list_name']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : 'ไม่ระบุ';
                                    echo htmlspecialchars($product_name) . ' <span id="B">( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                }
                                ?>
                            </td>
                            <td><?php echo thai_date_time($row['created_at']); ?></td>
                            <td><?php echo thai_date_time($row['reservation_date']); ?></td>
                            <td>
                                <?php
                                $checkBookingsDate = strtotime($row['reservation_date']);
                                $currentDate = time();

                                if ($row['situation'] === null) {
                                    echo 'ยังไม่ได้รับอนุมัติ';
                                } elseif (date('Y-m-d', $checkBookingsDate) == date('Y-m-d', $currentDate) && $row['situation'] == 1) {
                                ?>
                                    <button type="button" onclick="location.href='process_booking.php?action=add&item=<?php echo $row['id']; ?>'" class="use-it">
                                        <i class="icon fa-solid fa-arrow-up"></i>
                                        <span>ขอใช้</span>
                                    </button>
                                <?php
                                } elseif ($row['situation'] == 1) {
                                    echo 'ได้รับการอนุมัติ';
                                }
                                elseif ($row['situation'] == 3) {
                                    echo 'ได้ทำการขอใช้แล้ว';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="user_approve_not_found">
                <i class="fa-solid fa-address-book"></i>
                <span id="B">ไม่มีแจ้งเตือนการจอง</span>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>