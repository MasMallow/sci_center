<?php
require_once 'assets/database/dbConfig.php';
require_once 'assets/includes/thai_date_time.php';

$bookings = $conn->prepare("SELECT * FROM approve_to_reserve WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL OR situation = 0 ORDER BY serial_number");
$bookings->execute();
$data = $bookings->fetchAll(PDO::FETCH_ASSOC);
$numbookings = count($data); // นับจำนวนรายการ
$user = $conn->prepare("SELECT * FROM users_db WHERE status = 'wait_approved' AND urole = 'user'");;
$user->execute();
$datauser = $user->fetchAll(PDO::FETCH_ASSOC);
$numuser = count($datauser); // นับจำนวนรายการ

// ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ
$stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL ORDER BY serial_number");
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
    <title>SCICENTER Management || Staff</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/staff.css">
    <script>
        function toggleExpandRow(element) {
            const expandRow = element.closest('.approve_row').querySelector('.expand_row');
            if (expandRow.style.display === 'none' || expandRow.style.display === '') {
                expandRow.style.display = 'flex';
            } else {
                expandRow.style.display = 'none';
            }
        }

        // ใช้การตั้งค่าเริ่มต้นในการซ่อนแถว expand_row
        document.addEventListener('DOMContentLoaded', function() {
            const expandRows = document.querySelectorAll('.expand_row');
            expandRows.forEach(row => {
                row.style.display = 'none';
            });
        });
    </script>
</head>

<body>
    <div class="staff">
        <div class="staff_page">
            <div class="staff_section">
                <div class="staff_header">
                    <div class="section_1">
                        <i class="fa-solid fa-user-tie"></i>
                        <span id="B">สำหรับผู้ดูแล</span>
                    </div>
                    <div class="section_2">
                        <div class="date" id="date"></div>
                        <div class="time" id="time"></div>
                    </div>
                </div>
                <div class="staff_content">
                    <div class="staff_content_div">
                        <div class="staff_item">
                            <a href="approve_request" class="<?php if ($numbookings == '0') {
                                                                    echo 'staff_item_btn';
                                                                } elseif ($numbookings > 0) {
                                                                    echo 'staff_item_request';
                                                                } ?>">
                                <i class="icon fa-solid fa-square-check"></i>
                                <span class="text">อนุมัติการขอใช้</span>
                                <span id="B"><?php echo "(" . $numbookings . ")"; ?></span>
                            </a>
                            <a href="manage_users" class="<?php if ($numuser == '0') {
                                                                echo 'staff_item_btn';
                                                            } elseif ($numuser > 0) {
                                                                echo 'staff_item_request';
                                                            } ?>">
                                <i class="fa-solid fa-address-book"></i>
                                <span class="text">จัดการบัญชีผู้ใช้</span>
                                <span id="B"><?php echo "(" . $numuser . ")"; ?></span>
                            </a>
                        </div>
                        <div class="staff_item">
                            <a href="<?php echo $base_url; ?>/management" class="staff_item_btn">
                                <i class="fa-solid fa-plus-minus"></i>
                                <span class="text">จัดการระบบข้อมูล</span>
                            </a>
                            <a href="maintenance" class="staff_item_btn">
                                <i class="icon fa-solid fa-screwdriver-wrench"></i>
                                <span class="text">การบำรุงรักษา</span>
                            </a>
                        </div>
                        <div class="staff_item">
                            <a href="view_report" class="staff_item_btn">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="text">ประวัติการจอง</span>
                            </a>
                            <a href="top_10_list" class="staff_item_btn">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>ดูสถิติ 10 รายการ</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="staff_notification">
                <div class="staff_notification_header">
                    <span id="B">แจ้งเตือน</span>
                </div>
                <div class="staff_notification_body">
                    <?php if (!empty($data)) : ?>
                        <div class="staff_notification_stack">
                            <?php foreach ($data as $datas) : ?>
                                <div class="staff_notification_data">
                                    <span>
                                        <?php echo htmlspecialchars($datas['serial_number']); ?>
                                    </span>
                                    <span>
                                        <?php echo htmlspecialchars(thai_date_time_2($datas['reservation_date'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="non_notification_stack">
                            <div class="non_notification_stack_1">
                                <i class="fa-solid fa-envelope"></i>
                                <span id="B">ไม่มีแจ้งเตือนการขอใช้</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="staff_page">
            <div class="staff_section_approve">
                <div class="staff_approved_header">
                    <div class="section_1">
                        <i class="fa-solid fa-user-tie"></i>
                        <span id="B">อนุมัติการขอใช้</span>
                    </div>
                    <div class="section_2">
                        <div class="approve_table_header">
                            <span>รายการที่ขอใช้ทั้งหมด <span id="B">(<?php echo $num; ?>)</span> รายการ</span>
                        </div>
                    </div>
                </div>
                <div class="staff_approved_content">
                    <div class="staff_content_table">
                        <?php if (empty($data)) { ?>
                            <div class="approve_not_found_section">
                                <i class="fa-solid fa-xmark"></i>
                                <span id="B">ไม่พบข้อมูลการขอใช้</span>
                            </div>
                        <?php } ?>
                        <?php if (!empty($data)) { ?>
                            <div class="approve_container">
                                <?php
                                foreach ($data as $row) :
                                    if ($previousSn != $row['serial_number']) { ?>
                                        <div class="approve_row">
                                            <div class="defualt_row">
                                                <div class="serial_number">
                                                    <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                                    <?php echo $row['serial_number']; ?>
                                                </div>
                                                <div class="items">
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
                                                </div>
                                                <div class="reservation_date">
                                                    <span id="B">วัน เวลาที่ทำการขอใช้</span><br>
                                                    <?php echo thai_date_time_2($row['reservation_date']); ?>
                                                </div>
                                                <div class="approve_actions">
                                                    <form class="approve_form" method="POST" action="process_reserve.php">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <input type="hidden" name="userId" value="<?php echo $row['user_id']; ?>">
                                                        <button class="confirm_approve" type="submit" name="confirm"><i class="fa-solid fa-circle-check"></i></button>
                                                        <button class="cancel_approve" type="submit" name="cancel"><i class="fa-solid fa-circle-xmark"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="expand_row">
                                                <div>
                                                    <span id="B">ชื่อผู้ใช้ และวัน เวลาที่ทำรายการ :</span>
                                                    <?php echo htmlspecialchars($row['name_user']); ?>
                                                </div>
                                                <div>
                                                    <?php echo thai_date_time_2(htmlspecialchars($row['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                        $previousSn = $row['serial_number'];
                                    }
                                endforeach;
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="staff_page">
            <div class="staff_section">
                <div class="staff_header">
                    <div class="section_1">
                        <i class="fa-solid fa-user-tie"></i>
                        <span id="B">การบำรุงรักษา</span>
                    </div>
                    <div class="section_2">
                        <div class="approve_table_header">
                            <a href="maintenance">ไปที่หน้าบำรุงการรักษา</a>
                        </div>
                    </div>
                </div>
                <div class="staff_content">
                    <div class="staff_content_table">
                        <?php if (empty($maintenance)) { ?>
                            <div class="approve_not_found_section">
                                <i class="fa-solid fa-xmark"></i>
                                <span id="B">ไม่พบข้อมูลอุปกรณ์ และเครื่องมือ</span>
                            </div>
                        <?php } ?>
                        <?php if (!empty($maintenance)) { ?>
                            <div class="approve_container">
                                <?php
                                foreach ($maintenance as $row) : ?>
                                    <div class="approve_row">
                                        <div class="defualt_row">
                                            <div class="serial_number">
                                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                                <?php echo $row['serial_number']; ?>
                                            </div>
                                            <div class="items">
                                                <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="reservation_date">
                                                <?php echo thai_date_time($row['installation_date']); ?>
                                            </div>
                                            <div class="approve_actions">
                                                <form class="approve_form" method="POST" action="process_reserve.php">
                                                    <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                                                    <button class="confirm_approve" type="submit" name="confirm"><i class="fa-solid fa-circle-check"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="expand_row">
                                            <div>
                                                <?php echo thai_date_time_2(htmlspecialchars($row['last_maintenance_date'])); ?>
                                            </div>
                                            <div>
                                                <?php echo (htmlspecialchars($row['categories'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="staff_notification">
                <div class="staff_notification_header">
                    <span id="B">แจ้งเตือนการบำรุงรักษา</span>
                </div>
                <div class="staff_notification_body">
                    <?php if (!empty($data)) : ?>
                        <div class="staff_notification_stack">
                            <?php foreach ($data as $datas) : ?>
                                <div class="staff_notification_data">
                                    <span>
                                        <?php echo htmlspecialchars($datas['serial_number']); ?>
                                    </span>
                                    <span>
                                        <?php echo htmlspecialchars(thai_date_time_2($datas['reservation_date'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="non_notification_stack">
                            <div class="non_notification_stack_1">
                                <i class="fa-solid fa-envelope"></i>
                                <span id="B">ไม่มีแจ้งเตือนการขอใช้</span>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>