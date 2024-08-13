<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
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

    // ดึงข้อมูลการขอใช้งานที่ยังไม่ได้ใช้งานและตรงกับวันที่ปัจจุบัน
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve 
    WHERE userID = :user_id 
    AND Usage_item = 0 
    AND date_return IS NULL 
    AND situation = 1 
    AND CURDATE() <= DATE(reservation_date)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num = count($dataList); // นับจำนวนรายการ

} catch (Exception $e) {
    // บันทึกข้อผิดพลาดลงใน log และเปลี่ยนเส้นทางไปยังหน้าข้อผิดพลาด
    error_log($e->getMessage());
    header("Location: /error_page");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เริ่มต้นการใช้งาน</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/Used.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="UsedPage">
        <!-- แสดงข้อความแจ้งเตือนการเริ่มต้นใช้งานสำเร็จ -->
        <?php if (isset($_SESSION['USEDSTART_success'])) : ?>
            <div class="toast">
                <div class="toast_content">
                    <i class="fas fa-solid fa-check check"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?php echo $_SESSION['USEDSTART_success']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
                </div>
            </div>
            <?php unset($_SESSION['USEDSTART_success']); ?>
        <?php endif ?>
        <div class="UsedPage_header">
            <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/UsedStart') {
                    echo '<a href="/UsedStart">เริ่มต้นการใช้งาน</a>';
                }
                ?>
            </div>
        </div>
        <!-- แสดงข้อความเมื่อไม่พบข้อมูล -->
        <?php if (empty($dataList)) : ?>
            <div id="loading">
                <div class="spinner"></div>
                <p>กำลังโหลดข้อมูล...</p>
            </div>
            <div class="UsedPage_not_found">
                <i class="fa-solid fa-database"></i>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const loadingElement = document.getElementById('loading');
                    const notFoundElement = document.querySelector('.UsedPage_not_found');

                    setTimeout(function() {
                        loadingElement.style.display = 'none';

                        if (notFoundElement) {
                            notFoundElement.classList.add('visible');
                        }
                    }, 1500); // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล
                });
            </script>
        <?php else : ?>
            <div id="loading">
                <div class="spinner"></div>
                <p>กำลังโหลดข้อมูล...</p>
            </div>
            <div class="UsedPage_content">
                <div class="UsedPage_tableHeader">
                    <span>รายการที่ขอใช้ทั้งหมด <span id="B">(<?php echo count($dataList); ?>)</span> รายการ</span>
                </div>
                <div class="UsedPage_Table">
                    <!-- แสดงรายการการขอใช้งาน -->
                    <?php foreach ($dataList as $data) : ?>
                        <div class="UsedPage_row">
                            <div class="UsedPage_serial_number">
                                <span id="B">หมายเลขรายการ </span><?php echo htmlspecialchars($data['serial_number']); ?>
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
                                        <span id="B">ขอใช้ </span><?php echo thai_date_time_2($data['reservation_date']); ?>
                                        <span id="B">ถึง </span>
                                        <?php echo thai_date_time_2($data['end_date']); ?>
                                        <div>
                                            ผู้อนุมัติ
                                            <?php echo htmlspecialchars($data['approver']); ?>
                                            เมื่อ<?php echo thai_date_time_2(htmlspecialchars($data['approvaldatetime'])); ?>
                                        </div>
                                    </div>
                                    <form method="POST" action="<?= $base_url; ?>/models/used_process.php">
                                        <input type="hidden" name="reserve_id" value="<?= htmlspecialchars($data['ID']); ?>">
                                        <input type="hidden" name="list_name" value="<?= htmlspecialchars($data['list_name']); ?>">
                                        <input type="hidden" name="sn_list" value="<?= htmlspecialchars($data['sn_list']); ?>">
                                        <div class="list_item">
                                            <button class="submitUSED" type="submit">
                                                <i class="fa-solid fa-circle-play"></i>
                                                เริ่มใช้งาน
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const loadingElement = document.getElementById('loading');
                    const contentElement = document.querySelector('.UsedPage_content');

                    // หน่วงเวลาในการซ่อนการโหลดและแสดงเนื้อหาหลัก
                    setTimeout(function() {
                        loadingElement.style.display = 'none';

                        if (contentElement) {
                            contentElement.classList.add('visible'); // แสดงเนื้อหาหลัก

                            const requestDetails = document.querySelectorAll('.UsedPage_row');
                            let index = 0;

                            function showNextRow() {
                                if (index < requestDetails.length) {
                                    requestDetails[index].classList.add('visible');
                                    index++;
                                    setTimeout(showNextRow, 200); // หน่วงเวลาในการแสดงแต่ละรายการ
                                }
                            }

                            showNextRow();
                        }
                    }, 1500); // หน่วงเวลา 1.5 วินาทีก่อนแสดงเนื้อหา
                });
            </script>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/loading.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/noti_toast.js"></script>
</body>

</html>