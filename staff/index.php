<?php
require_once 'assets/database/connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management || Staff</title>

    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/css/staff.css">
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
                            <a href="crud/management.php">
                                <i class="fa-solid fa-plus-minus"></i>
                                <span class="text">จัดการระบบข้อมูล</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <?php 
                        $user = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved' AND urole = 'user'");;
                        $user->execute();
                        $datauser = $user->fetchAll(PDO::FETCH_ASSOC);
                        $numuser = count($datauser); // นับจำนวนรายการ
                        ?>
                        <div class="staff_menu">
                            <a href="user_approval">
                                <i class="fa-solid fa-address-book"></i>
                                <span>อนุมัติผู้สร้างบัญชี<?php echo "(" . $numuser . ")"; ?></span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="manage_users">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>อนุมัติผู้สร้างบัญชี</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="maintenance">
                                <i class="icon fa-solid fa-screwdriver-wrench"></i>
                                <span class="text">การบำรุงรักษา</span>
                            </a>
                        </div>
                    </li>
                </ul>
                <ul class="staff_content_ul">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL ORDER BY sn");
                    $stmt->execute();
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $num = count($data); // นับจำนวนรายการ

                    $bookings = $conn->prepare("SELECT * FROM bookings WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL ORDER BY serial_number");
                    $bookings->execute();
                    $data = $bookings->fetchAll(PDO::FETCH_ASSOC);
                    $numbookings = count($data); // นับจำนวนรายการ
                    ?>
                    <li>
                        <div class="staff_menu">
                            <a href="approve_for_use">
                                <i class="icon fa-solid fa-square-check"></i>
                                <span class="text">การอนุมัติการยืม <?php echo "(" . $num . ")"; ?></span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="approve_for_booking">
                                <i class="icon fa-solid fa-square-check"></i>
                                <span class="text">การอนุมัติการจอง <?php echo "(" . $numbookings . ")"; ?></span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="view_report">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="text">ประวัติการขอใช้</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="staff_menu">
                            <a href="view_report_booking">
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