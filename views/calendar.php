<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่า user ได้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID    
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == 'n_approved') {
            unset($_SESSION['user_login']);
            header('Location: /sign_in');
            exit();
        } elseif ($userData['status'] == 'w_approved') {
            unset($_SESSION['reserve_cart']);
            header('Location: /');
            exit();
        }
    }
} else {
    header("Location: /sign_in");
    exit();
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
    <title>ตารางการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bookingTable.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="bookingTable">
        <div class="bookingTable_header">
            <a class="historyBACK" href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/calendar') {
                    echo '<a href="/calendar">ตารางการขอใช้ศูนย์วิทยาศาสตร์</a>
                    ';
                }
                ?>
            </div>
        </div>
        <div class="bookingTable_content">
            <div class="navigation">
                <?php
                // คำนวณเดือนก่อนหน้า
                $prev_month = $current_month - 1;
                $prev_year = $current_year;
                if ($prev_month < 1) {
                    $prev_month = 12;
                    $prev_year--;
                }

                // คำนวณเดือนถัดไป
                $next_month = $current_month + 1;
                $next_year = $current_year;
                if ($next_month > 12) {
                    $next_month = 1;
                    $next_year++;
                }
                ?>
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn-prev"><i class="fa-solid fa-angle-left"></i></a>
                <div class="navigationCENTER">
                    <span><?php echo thai_date_time_5("$current_year-$current_month"); ?></span>
                    <button id="reset-to-current"><i class="fa-regular fa-calendar-days"></i></button>
                </div>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn-next"><i class="fa-solid fa-angle-right"></i></a>
            </div>

            <script>
                document.getElementById('reset-to-current').addEventListener('click', function() {
                    var currentDate = new Date();
                    var currentMonth = currentDate.getMonth() + 1; // เดือนใน JavaScript เริ่มต้นที่ 0
                    var currentYear = currentDate.getFullYear();
                    window.location.href = '?month=' + currentMonth + '&year=' + currentYear;
                });
            </script>
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
                                <div class="notification">
                                    <?php foreach ($calendar[$i] as $reservation) : ?>
                                        <?php if (!empty($reservation)) : ?>
                                            <a href="reservation_details/<?php echo $day_date; ?>"><i class="fa-solid fa-circle-exclamation"></i></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </main>
    <footer>
        <?php include_once 'assets/includes/footer.php'; ?>
    </footer>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>