<?php
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

$bookings = $conn->prepare("
        SELECT * FROM approve_to_reserve 
        WHERE approvaldatetime IS NULL 
        AND approver IS NULL AND situation IS NULL OR situation = 0 
        ");
$bookings->execute();
$data = $bookings->fetchAll(PDO::FETCH_ASSOC);
$numbookings = count($data); // นับจำนวนรายการ

$user = $conn->prepare("
        SELECT * FROM users_db 
        WHERE status = 'w_approved' AND urole = 'user'");;
$user->execute();
$datauser = $user->fetchAll(PDO::FETCH_ASSOC);
$numuser = count($datauser); // นับจำนวนรายการ

$stmt = $conn->prepare("SELECT * FROM crud");
$stmt->execute();
$CRUD = $stmt->fetchAll(PDO::FETCH_ASSOC);
$numCRUD = count($CRUD); // นับจำนวนรายการ

try {
    $userID = $_SESSION['staff_login'];

    // ดึงข้อมูลผู้ใช้
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // ดึงข้อมูลการใช้งาน
    $users = $conn->prepare("SELECT * FROM users_db");
    $users->execute();
    $UserData = $users->fetchAll(PDO::FETCH_ASSOC);
    $userCount = count($UserData); // นับจำนวนรายการ

    // ดึงข้อมูลการอนุมัติการจอง
    $used = $conn->prepare("SELECT * FROM approve_to_reserve");
    $used->execute();
    $dataUsed = $used->fetchAll(PDO::FETCH_ASSOC);
    $usedCount = count($dataUsed); // นับจำนวนรายการ

    // ดึงข้อมูลการอนุมัติการจอง
    $stmt = $conn->prepare("SELECT * FROM logs_management");
    $stmt->execute();
    $Management = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ManagementCount = count($Management); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (วัสดุ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'วัสดุ' ORDER BY serial_number");
    $stmt->execute();
    $material = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $materialCount = count($material); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (อุปกรณ์)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'อุปกรณ์' ORDER BY serial_number");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $equipmentCount = count($equipment); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (เครื่องมือ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'เครื่องมือ' ORDER BY serial_number");
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $toolsCount = count($tools); // นับจำนวนรายการ

    // ดึงข้อมูลการบำรุงรักษา
    $stmt = $conn->prepare("SELECT ID FROM logs_maintenance");
    $stmt->execute();
    $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $maintenanceCount = count($maintenance); // นับจำนวนรายการ

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
// ตรวจสอบค่าของ month และ year จาก GET parameters
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

$today = date('Y-m-d');

try {
    // กำหนดช่วงวันที่ของเดือนที่เลือก
    $start_date = "$current_year-$current_month-01";
    $end_date = date("Y-m-t", strtotime($start_date));

    // ดึงข้อมูลการจองที่อยู่ในช่วงวันที่ที่กำหนด
    $sql = "SELECT * FROM approve_to_reserve WHERE reservation_date BETWEEN :start_date AND :end_date AND situation = 1 AND date_return IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

// ฟังก์ชันสำหรับสร้างปฏิทินจากข้อมูลการจอง
function generate_calendar($reservations, $current_month, $current_year)
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
    $calendar = array_fill(1, $days_in_month, []);

    foreach ($reservations as $reservation) {
        $day = date('j', strtotime($reservation['reservation_date']));
        $calendar[$day][] = $reservation;
    }

    return $calendar;
}

// สร้างปฏิทินจากข้อมูลการจอง
$calendar = generate_calendar($reservations, $current_month, $current_year);

// กำหนดวันที่เริ่มต้นของเดือน
$first_day_of_month = date('w', strtotime("$current_year-$current_month-01"));
$days_of_week = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management || Staff</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/staff.css">
</head>

<body>
    <div class="staff">
        <div class="staff_page">
            <!-- -----------------SIDEBAR --------------- -->
            <sidebar class="menu_navigator">
                <ul class="sb_ul">
                    <li>
                        <a class="link <?= ($request_uri == '/') ? 'active' : ''; ?>" href="<?= $base_url; ?>">
                            <i class="icon fa-solid fa-house"></i>
                            <span class="text">หน้าหลัก</span>
                        </a>
                    </li>
                    <li class="group_li">
                        <span class="group_title">การจัดการ / อนุมัติ</span>
                        <a href="approve_request" class="group_li_01 <?php if ($numbookings > 0) {
                                                                            echo 'warning';
                                                                        } ?>">
                            <i class="icon fa-solid fa-square-check"></i>
                            <span class="text">อนุมัติการขอใช้</span>
                            <span id="B"><?php echo "(" . $numbookings . ")"; ?></span>
                        </a>
                        <a href="manage_users" class="group_li_02 <?php if ($numuser > 0) {
                                                                        echo 'warning';
                                                                    } ?>">
                            <i class="fa-solid fa-address-book"></i>
                            <span class="text">จัดการบัญชีผู้ใช้</span>
                            <span id="B"><?php echo "(" . $numuser . ")"; ?></span>
                        </a>
                    </li>
                    <li class="group_li">
                        <span class="group_title">การจัดการระบบ / บำรุงรักษา</span>
                        <a href="<?php echo $base_url; ?>/management" class="group_li_01">
                            <i class="fa-solid fa-plus-minus"></i>
                            <span class="text">จัดการระบบข้อมูล</span>
                        </a>
                        <a href="maintenance_dashboard" class="group_li_02">
                            <i class="icon fa-solid fa-screwdriver-wrench"></i>
                            <span class="text">การบำรุงรักษา</span>
                        </a>
                    </li>
                    <li class="group_li">
                        <span class="group_title">รายงาน / สถิติ</span>
                        <a href="report" class="group_li_01">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="text">รายงาน</span>
                        </a>
                        <a href="top10" class="group_li_02">
                            <i class="fa-solid fa-thumbs-up"></i>
                            <span>TOP 10</span>
                        </a>
                    </li>
                    <li class="group_li">
                        <span class="group_title">QR-CODE</span>
                        <a class="group_li_01" href="<?= $base_url; ?>/qrcode-staff">
                            <i class="fa-solid fa-qrcode"></i>
                            <span class="text">QR CODE</span>
                        </a>
                    </li>
                </ul>
            </sidebar>
            <div class="contentSTAFF_area">
                <!-- --------------- ROW 1 --------------- -->
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
                    <div class="viewLog_content_MAIN">
                        <div class="viewLog_content_1">
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนบัญชีทั้งหมดในระบบ</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $userCount; ?></span>บัญชี
                                </div>
                            </div>
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนการขอใช้ทั้งหมด</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $usedCount; ?></span>ครั้ง
                                </div>
                            </div>
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนการบำรุงรักษาทั้งหมด</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $maintenanceCount; ?></span>ครั้ง
                                </div>
                            </div>
                        </div>
                        <!-- ------------------- ROW 3 ------------------ -->
                        <div class="viewLog_content_1">
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนวัสดุทั้งหมด</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $userCount; ?></span>จำนวน
                                </div>
                            </div>
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนอุปกรณ์ทั้งหมด</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $usedCount; ?></span>จำนวน
                                </div>
                            </div>
                            <div class="Content_1">
                                <div class="Content_1_header">
                                    <span id="B">จำนวนเครื่องมือทั้งหมด</span>
                                </div>
                                <div class="Content_1_body">
                                    <span id="B"><?php echo $maintenanceCount; ?></span>จำนวน
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- -------------- ROW 2 --------------- -->
                <div class="bookingTable_content">
                    <div class="navigation">
                        <a href="<?php echo $base_url; ?>/approve_request/calendar" class="btn-prev"><i class="fa-solid fa-angle-left"></i></a>
                        <div class="navigationCENTER">
                            <span><?php echo thai_date_time_5("$current_year-$current_month"); ?></span>
                        </div>
                        <a href="<?php echo $base_url; ?>/approve_request/calendar" class="btn-next"><i class="fa-solid fa-angle-right"></i></a>
                    </div>
                    <div class="calendar">
                        <?php foreach ($days_of_week as $day) : ?>
                            <div class="day-name"><?php echo $day; ?></div>
                        <?php endforeach; ?>

                        <?php for ($i = 0; $i < $first_day_of_month; $i++) : ?>
                            <div class="day"></div>
                        <?php endfor; ?>

                        <?php
                        $days_in_month = date('t', strtotime("$current_year-$current_month-01"));
                        for ($i = 1; $i <= $days_in_month; $i++) :
                            // Set class for the current day
                            $day_date = date('Y-m-d', strtotime("$current_year-$current_month-$i"));
                            $day_class = ($day_date == $today) ? 'day today' : 'day';
                        ?>
                            <div class="<?php echo $day_class; ?>">
                                <div class="date"><?php echo $i; ?></div>
                                <?php if (isset($calendar[$i])) : ?>
                                    <div class="reservation">
                                        <div class="notification_reservation">
                                            <?php foreach ($calendar[$i] as $reservation) : ?>
                                                <?php if (!empty($reservation)) : ?>
                                                    <a class="icon_reservation" href="reservation_details/<?php echo $day_date; ?>">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include_once 'assets/includes/footer_2.php'; ?>
    </footer>
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>

</body>

</html>