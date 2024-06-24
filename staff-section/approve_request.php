<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่าพนักงานเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลผู้ใช้หากเข้าสู่ระบบ
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
// ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ
$stmt = $conn->prepare("
        SELECT * FROM approve_to_reserve 
        WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL 
        ORDER BY ID ASC");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$num = count($data); // นับจำนวนรายการ
$previousSn = '';
$previousFirstname = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/approval.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <div class="approve_section">
        <?php if (isset($_SESSION['approve_success'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['approve_success']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['approve_success']); ?>
        <?php endif ?>
        <div class="header_approve">
            <div class="header_approve_section">
                <a href="javascript:history.back();">
                    <i class="fa-solid fa-arrow-left-long"></i>
                </a>
                <span id="B">อนุมัติการขอใช้</span>
            </div>
        </div>
        <div class="approve_table_section">
            <?php if (empty($data)) { ?>
                <div class="approve_not_found_section">
                    <i class="fa-solid fa-xmark"></i>
                    <span id="B">ไม่พบข้อมูลการขอใช้</span>
                </div>
            <?php } ?>
            <?php if (!empty($data)) { ?>
                <table class="approve_table_data">
                    <div class="approve_table_header">
                        <span>รายการที่ขอใช้ทั้งหมด <span id="B"><?php echo $num; ?></span> รายการ</span>
                    </div>
                    <thead>
                        <tr>
                            <th class="s_number"><span id="B">หมายเลขรายการ</span></th>
                            <th class="name_use"><span id="B">ชื่อผู้ขอใช้งาน</span></th>
                            <th class="item_name"><span id="B">รายการที่ขอใช้งาน</span></th>
                            <th class="return"><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                            <th class="approval"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $row) :
                            if ($previousSn != $row['serial_number']) { ?>
                                <tr>
                                    <td class="sn">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                        <?php echo $row['serial_number']; ?>
                                    </td>
                                    <td><?php echo $row['name_user']; ?></td>
                                    <td>
                                        <?php
                                        // แยกข้อมูล Item Borrowed
                                        $items = explode(',', $row['list_name']);
                                        // แสดงข้อมูลรายการที่ยืม
                                        foreach ($items as $item) {
                                            $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                                            $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                                            $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                                            echo $product_name . ' <span id="B"> ( ' . $quantity . ' รายการ )</span><br>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo thai_date_time($row['reservation_date']); ?></td>
                                    <td>
                                        <form class="approve_form" method="POST" action="<?php echo $base_url; ?>/staff-section/processRequest.php">
                                            <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                                            <input type="hidden" name="userID" value="<?php echo $row['userID']; ?>">
                                            <button class="confirm_approve" type="submit" name="confirm">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr style="display: none;">
                                    <td colspan="7">
                                        <div class="expandable_row">
                                            <div>
                                                <span id="B">วันเวลาที่ทำรายการ</span>
                                                <?php echo thai_date_time_2($row['created_at']); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                                $previousSn = $row['serial_number'];
                            }
                        endforeach;
                        ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
    <script>
        function toggleExpandRow(element) {
            var row = element.closest('tr').nextElementSibling;
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';
                element.classList.remove('fa-circle-arrow-right');
                element.classList.add('fa-circle-arrow-down');
            } else {
                row.style.display = 'none';
                element.classList.remove('fa-circle-arrow-down');
                element.classList.add('fa-circle-arrow-right');
            }
        }
    </script>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>