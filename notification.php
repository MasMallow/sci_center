<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit();
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    $_SESSION['error'] = 'ผู้ใช้ไม่พบ!';
    header('Location: /sign_in');
    exit();
}

if ($userData['status'] !== 'approved') {
    header("Location: /");
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
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/nofitication.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="notification_page">
        <div class="notification_header">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">แจ้งเตือนการขอใช้</span>
        </div>
        <div class="notification_section">
            <?php if (!empty($data)) : ?>
                <?php foreach ($data as $row) : ?>
                    <div class="notification">
                        <div class="icon_notification">
                            <i class="icon fas fa-bell"></i>
                        </div>
                        <div class="notification_details">
                            <div class="serial_number"><span id="B">หมายเลขรายการ </span><?= htmlspecialchars($row['serial_number'] ?? $row['serial_number']); ?></div>
                            <div class="subtext">
                                <?php
                                $items = explode(',', $row['list_name'] ?? $row['list_name']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : 'ไม่ระบุ';
                                    echo htmlspecialchars($product_name) . ' <span id="B">( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                }
                                ?>
                            </div>
                            <div class="subtext"><span id="B">วันเวลาที่ขอใช้งาน </span><?= thai_date_time_2($row['created_at']); ?></div>
                            <div class="subtext"><span id="B">วันเวลาที่สิ้นสุดขอใช้งาน </span> <?= thai_date_time_2($row['reservation_date']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class=" user_approve_not_found">
                    <i class="icon fa-solid fa-address-book"></i>
                    <span id="B">ไม่มีแจ้งเตือนการขอใช้งาน</span>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <?php include_once 'assets/includes/footer.php' ?>
    </footer>
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/details.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/datetime.js"></script>
</body>

</html>