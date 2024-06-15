<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in');
    exit();
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    $_SESSION['error'] = 'ผู้ใช้ไม่พบ!';
    header('Location: auth/sign_in');
    exit();
}

if ($userData['status'] !== 'approved') {
    header("Location: home");
    exit();
}

$firstname = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
$stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE name_user = :firstname ORDER BY created_at DESC");
$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเตือน</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/nofitication.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/header.php'; ?>
    </header>
    <div class="notification_page">
        <div class="maintenance">
            <div class="header_maintenance_section">
                <a href="<?php echo $base_url; ?>/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">แจ้งเตือนการขอใช้</span>
            </div>
        </div>
        <div class="notification_section">
            <?php if (!empty($data)) : ?>
                <table class="table_notification">
                    <thead>
                        <tr>
                            <th class="serial_number"><span id="B">หมายเลขรายการ</span></th>
                            <th><span id="B">รายการที่ขอใช้งาน</span></th>
                            <th><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                            <th><span id="B">วันเวลาที่สิ้นสุดขอใช้งาน</span></th>
                            <th><span id="B">สถานะ</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row) : ?>
                            <tr>
                                <td class="serial_number"><?= htmlspecialchars($row['serial_number'] ?? $row['serial_number']); ?></td>
                                <td>
                                    <?php
                                    $items = explode(',', $row['list_name'] ?? $row['list_name']);
                                    foreach ($items as $item) {
                                        $item_parts = explode('(', $item);
                                        $product_name = trim($item_parts[0]);
                                        $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : 'ไม่ระบุ';
                                        echo htmlspecialchars($product_name) . ' <span id="B">( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                    }
                                    ?>
                                </td>
                                <td><?= thai_date_time($row['borrowdatetime'] ?? $row['created_at']); ?></td>
                                <td><?= thai_date_time($row['returndate'] ?? $row['reservation_date']); ?></td>
                                <td>
                                    <?php
                                    $situation = $row['situation'];
                                    if ($situation === null) {
                                        echo 'ยังไม่ได้รับอนุมัติ';
                                    } elseif ($notification === 'used' && $situation == 1) {
                                        echo 'ได้รับอนุมัติ';
                                    } elseif ($notification === 'reserve') {
                                        $checkBookingsDate = strtotime($row['reservation_date']);
                                        $currentDate = time();
                                        if (date('Y-m-d', $checkBookingsDate) == date('Y-m-d', $currentDate) && $situation == 1) {
                                            echo '<button type="button" onclick="location.href=\'process_booking.php?action=add&item=' . $row['id'] . '\'" class="use-it"><i class="icon fa-solid fa-arrow-up"></i><span>ขอใช้</span></button>';
                                        } elseif ($situation == 1) {
                                            echo 'ได้รับการอนุมัติ';
                                        } elseif ($situation == 3) {
                                            echo 'ได้ทำการขอใช้แล้ว';
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="user_approve_not_found">
                    <i class="icon fa-solid fa-address-book"></i>
                    <span id="B">ไม่มีแจ้งเตือนการขอใช้งาน</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/details.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/datetime.js"></script>
</body>

</html>