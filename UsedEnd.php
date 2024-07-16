<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

try {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        header("Location: /sign_in");
        exit();
    }

    if ($userData['status'] !== 'approved') {
        unset($_SESSION['cart']);
        header("Location: . $base_url; .");
        exit();
    }

    $returned = $_GET['return_id'] ?? 'used'; // ตรวจสอบค่าที่ถูกส่งมาจาก query parameter 'returned'

    // ตรวจสอบและเลือกคำสั่ง SQL ตามค่า 'returned' ที่รับมาelse {
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE userID = :user_id AND situation = 1 AND date_return IS NULL AND Usage_item = 1");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC); // เก็บข้อมูลที่ได้จากการ query ลงในตัวแปร $dataList
    $num = count($dataList); // นับจำนวนรายการ

    $_SESSION['USEDEND_success'] = 'สื้นสุดการขอใช้ ' . $returned;
} catch (Exception $e) {
    error_log($e->getMessage()); // บันทึกข้อผิดพลาดลงในไฟล์ log
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คืนรายการที่ขอใช้งาน</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/Used.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="UsedPage">
        <!-- แสดงข้อความแจ้งเตือนการเริ่มต้นใช้งานสำเร็จ -->
        <?php if (isset($_SESSION['USEDEND_success'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-check check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['USEDEND_success']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['USEDEND_success']); ?>
        <?php endif ?>

        <div class="UsedPage_header">
            <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">คืนใช้งานวัสดุ อุปกรณ์ เครื่องมือที่ทำการขอใช้งาน</span>
        </div>
        <?php if (empty($dataList)) : ?>
            <div class="UsedPage_not_found">
                <i class="fa-solid fa-database"></i>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
        <?php else : ?>
            <div class="UsedPage_content">
                <div class="UsedPage_tableHeader">
                    <span>รายการที่ขอใช้งานทั้งหมด <span id="B">(<?php echo $num; ?>)</span> รายการ</span>
                </div>
                <div class="UsedPage_Table">
                    <?php foreach ($dataList as $data) : ?>
                        <div class="UsedPage_row">
                            <div class="UsedPage_serial_number">
                                <?php echo htmlspecialchars($data['serial_number']); ?>
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
                            </div>
                            <div class="UsedPage_return_list">
                                <div class="notification">
                                    <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                    <span id="B">ขอใช้ </span><?php echo thai_date_time_2($data['reservation_date']); ?>
                                    <span id="B">ถึง </span>
                                    <?php echo thai_date_time_2($data['end_date']); ?>
                                </div>
                                <form method="POST" action="<?php echo $base_url; ?>/backend/returnedUsed.php">
                                    <input type="hidden" name="return_id" value="<?= htmlspecialchars($data['ID']); ?>">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['userID']); ?>">
                                    <div class="list_item">
                                        <button class="submitUSED" type="submit">ยืนยันการคืน</button>
                                    </div>
                                </form>
                            </div>
                            <div class="expandable_row" style="display: none;">
                                <div>ผู้อนุมัติ
                                    <?php echo htmlspecialchars($data['approver']); ?>
                                </div>
                                <div>เมื่อ
                                    <?php echo thai_date_time_2(htmlspecialchars($data['approvaldatetime'])); ?>
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
    <script>
        function toggleExpandRow(element) {
            var row = element.closest('.return_row').nextElementSibling;
            if (row && row.classList.contains('expandable_row')) {
                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'flex';
                    element.classList.remove('fa-circle-arrow-right');
                    element.classList.add('fa-circle-arrow-down');
                } else {
                    row.style.display = 'none';
                    element.classList.remove('fa-circle-arrow-down');
                    element.classList.add('fa-circle-arrow-right');
                }
            }
        }
    </script>
</body>

</html>