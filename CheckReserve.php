<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

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

$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Adjust the month and year for month navigation
if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

try {
    $start_date = "$current_year-$current_month-01";
    $end_date = date("Y-m-t", strtotime($start_date));
    
    $sql = "SELECT * FROM approve_to_reserve WHERE reservation_date BETWEEN :start_date AND :end_date AND situation = 1 AND date_return IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

function generate_calendar($reservations, $current_month, $current_year) {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
    $calendar = array_fill(1, $days_in_month, []);

    foreach ($reservations as $reservation) {
        $day = date('j', strtotime($reservation['reservation_date']));
        $calendar[$day][] = $reservation;
    }

    return $calendar;
}

$calendar = generate_calendar($reservations, $current_month, $current_year);

$first_day_of_month = date('w', strtotime("$current_year-$current_month-01"));
$days_of_week = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bookingTable.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .calendar .day, .calendar .day-name {
            border: 1px solid #ddd;
            padding: 10px;
            position: relative;
        }
        .calendar .day-name {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .calendar .day .date {
            position: absolute;
            top: 5px;
            right: 5px;
            font-weight: bold;
        }
        .calendar .day .reservation {
            margin-top: 20px;
        }
        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="bookingTable">
        <div class="bookingTable_header">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">ตารางการขอใช้ศูนย์วิทยาศาสตร์</span>
        </div>
        <div class="bookingTable_content">
            <div class="navigation">
                <a href="?month=<?php echo $current_month - 1; ?>&year=<?php echo $current_year; ?>" class="btn btn-prev">เดือนก่อนหน้า</a>
                <span><?php echo thai_date_time("$current_year-$current_month-01", 'F Y'); ?></span>
                <a href="?month=<?php echo $current_month + 1; ?>&year=<?php echo $current_year; ?>" class="btn btn-next">เดือนถัดไป</a>
            </div>
            <div class="calendar">
                <?php foreach ($days_of_week as $day) : ?>
                    <div class="day-name"><?php echo $day; ?></div>
                <?php endforeach; ?>
                
                <?php for ($i = 0; $i < $first_day_of_month; $i++) : ?>
                    <div class="day"></div>
                <?php endfor; ?>

                <?php for ($i = 1; $i <= date('t', strtotime("$current_year-$current_month-01")); $i++) : ?>
                    <div class="day">
                        <div class="date"><?php echo $i; ?></div>
                        <?php if (isset($calendar[$i])) : ?>
                            <div class="reservation">
                                <?php foreach ($calendar[$i] as $reservation) : ?>
                                    <div class="cell">
                                        <?php
                                        $items = explode(',', $reservation['list_name']);
                                        foreach ($items as $item) {
                                            $item_parts = explode('(', $item);
                                            $product_name = trim($item_parts[0]);
                                            $quantity = str_replace(')', '', $item_parts[1]);
                                            echo $product_name . " " . $quantity . " รายการ ";
                                        }
                                        ?>
                                    </div>
                                    <div class="cell">
                                        <?php echo thai_date_time($reservation['reservation_date']); ?>
                                    </div>
                                <?php endforeach; ?>
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
