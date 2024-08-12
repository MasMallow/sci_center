<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

try {
    // ดึงข้อมูลผู้ใช้งานจาก session
    $user_id = $_SESSION['user_login'];

    // เตรียมคำสั่ง SQL และดึงข้อมูลผู้ใช้งาน
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีข้อมูลผู้ใช้งานหรือไม่ ถ้าไม่มีให้กลับไปหน้า sign in
    if (!$userData) {
        header("Location: /sign_in");
        exit();
    }

    // ตรวจสอบสถานะผู้ใช้งาน ถ้าไม่ใช่ 'approved' ให้ลบข้อมูล cart ออกจาก session และกลับไปหน้าแรก
    if ($userData['status'] !== 'approved') {
        unset($_SESSION['cart']);
        header("Location: $base_url;");
        exit();
    }

    // รับค่าจาก query parameter 'return_id' หรือกำหนดค่า default เป็น 'used'
    $returned = $_GET['return_id'] ?? 'used';

    // เตรียมคำสั่ง SQL เพื่อดึงข้อมูลการขอใช้วัสดุ อุปกรณ์ เครื่องมือ
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE userID = :user_id AND situation = 1 AND date_return IS NULL AND Usage_item = 1");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC); // เก็บข้อมูลที่ได้จากการ query ลงในตัวแปร $dataList
    $num = count($dataList); // นับจำนวนรายการ
} catch (Exception $e) {
    // บันทึกข้อผิดพลาดลงในไฟล์ log และหยุดการทำงาน
    error_log($e->getMessage());
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คืนรายการที่ขอใช้งาน</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/Used.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/notification_popup.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="UsedPage">
        <!-- แสดงข้อความแจ้งเตือนการเริ่มต้นใช้งานสำเร็จ -->
        <?php if (isset($_SESSION['USEDEND_success'])) : ?>
            <div class="toast">
                <div class="toast_content">
                    <i class="fas fa-solid fa-check check"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?= $_SESSION['USEDEND_success']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
                </div>
            </div>
            <?php unset($_SESSION['USEDEND_success']); ?>
        <?php endif ?>
        <div class="UsedPage_header">
            <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/UsedEnd') {
                    echo '<a href="/UsedEnd">คืนอุปกรณ์ หรือเครื่องมือ</a>';
                }
                ?>
            </div>
        </div>
        <?php if (empty($dataList)) : ?>
            <div class="UsedPage_not_found">
                <i class="fa-solid fa-database"></i>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
        <?php else : ?>
            <div class="UsedPage_content">
                <div class="UsedPage_tableHeader">
                    <span>รายการที่ขอใช้งานทั้งหมด <span id="B">(<?= $num; ?>)</span> รายการ</span>
                </div>
                <div class="UsedPage_Table">
                    <?php foreach ($dataList as $data) : ?>
                        <div class="UsedPage_row">
                            <div class="UsedPage_serial_number">
                                <span id="B">หมายเลขรายการ </span><?= htmlspecialchars($data['serial_number']); ?>
                            </div>
                            <div class="UsedPage_list">
                                <?php
                                $items = explode(',', $data['list_name']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = rtrim($item_parts[1], ')');
                                    echo htmlspecialchars($product_name) . ' <span id="B"> ( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                }
                                ?>
                                <div class="UsedPage_return_list">
                                    <div class="notification">
                                        <span id="B">ขอใช้ </span><?= thai_date_time_2($data['reservation_date']); ?>
                                        <span id="B">ถึง </span><?= thai_date_time_2($data['end_date']); ?>
                                        <div>ผู้อนุมัติ
                                            <?= htmlspecialchars($data['approver']); ?>
                                            เมื่อ<?= thai_date_time_2(htmlspecialchars($data['approvaldatetime'])); ?>
                                        </div>
                                    </div>
                                    <form method="POST" action="<?= $base_url; ?>/models/used_process.php">
                                        <input type="hidden" name="return_id" value="<?= htmlspecialchars($data['ID']); ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['userID']); ?>">
                                        <div class="list_item">
                                            <button class="submitUSED" type="submit">
                                                <i class="fa-solid fa-hourglass-end"></i>
                                                ยืนยันการคืน</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/loading.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/noti_toast.js"></script>
</body>

</html>