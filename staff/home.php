<?php
require_once 'assets/database/dbConfig.php';
include_once 'route/routeStaff.php';
$bookings = $conn->prepare("SELECT * FROM approve_to_reserve WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL OR situation = 0 ORDER BY serial_number");
$bookings->execute();
$data = $bookings->fetchAll(PDO::FETCH_ASSOC);
$numbookings = count($data); // นับจำนวนรายการ
$user = $conn->prepare("SELECT * FROM users_db WHERE status = 'wait_approved' AND urole = 'user'");;
$user->execute();
$datauser = $user->fetchAll(PDO::FETCH_ASSOC);
$numuser = count($datauser); // นับจำนวนรายการ
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management || Staff</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/staff.css">
</head>

<body>
    <div class="staff">
        <div class="staff_section">
            <div class="staff_header">
                <i class="fa-solid fa-user-tie"></i></i>
                <span id="B">สำหรับผู้ดูแล</span>
            </div>
            <div class="staff_content">
                <ul class="staff_content_ul">
                    <li>
                        <div class="staff_menu">
                            <a href="<?php echo $base_url; ?>/management" class="user_approval_btn">
                                <i class="fa-solid fa-plus-minus"></i>
                                <span class="text">จัดการระบบข้อมูล</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="manage_users" class="<?php if ($numuser == '0') {
                                                                echo 'user_approval_btn';
                                                            } elseif ($numuser > 0) {
                                                                echo 'user_approval_have';
                                                            } ?>">
                                <i class="fa-solid fa-address-book"></i>
                                <span class="text">การจัดการบัญชีผู้ใช้</span>
                                <span id="B">
                                    <?php echo "(" . $numuser . ")"; ?>
                                </span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="top_10_list" class="user_approval_btn">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>ดูสถิติ 10 รายการ</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="maintenance" class="user_approval_btn">
                                <i class="icon fa-solid fa-screwdriver-wrench"></i>
                                <span class="text">การบำรุงรักษา</span>
                            </a>
                        </div>
                    </li>
                </ul>
                <ul class="staff_content_ul">
                    <li>
                        <div class="staff_menu">
                            <a href="approve_for_booking" class="<?php if ($numbookings == 0) {
                                                                        echo 'user_approval_btn';
                                                                    } elseif ($numbookings > 0) {
                                                                        echo 'user_approval_have';
                                                                    } ?>">
                                <i class="icon fa-solid fa-square-check"></i>
                                <span class="text">การอนุมัติการจอง</span>
                                <span id="B"><?php echo "(" . $numbookings . ")"; ?></span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="view_report_booking" class="user_approval_btn">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="text">ประวัติการจอง</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>