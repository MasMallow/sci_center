<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
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

// ตั้งค่าการแบ่งหน้า
$limit = 20; // จำนวนรายการต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ
$stmt = $conn->prepare("
    SELECT * FROM approve_to_reserve 
    WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL
    ORDER BY created_at ASC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนข้อมูลทั้งหมดที่ยังไม่ได้รับการอนุมัติ
$totalStmt = $conn->prepare("
    SELECT COUNT(*) FROM approve_to_reserve 
    WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL
");
$totalStmt->execute();
$totalData = $totalStmt->fetchColumn();
$totalPages = ceil($totalData / $limit);

// ตรวจสอบการแสดงผล pagination
$pagination_display = $totalData > $limit;

// ตั้งค่าการแบ่งหน้า สำหรับการอนุมัติแล้ว
$usedLimit = 10;
$usedPage = isset($_GET['usedPage']) ? (int)$_GET['usedPage'] : 1;
$usedOffset = ($usedPage - 1) * $usedLimit;

// ดึงข้อมูลการอนุมัติการจอง
$used = $conn->prepare("
    SELECT * FROM approve_to_reserve 
    ORDER BY ID DESC 
    LIMIT :limit OFFSET :offset
");
$used->bindParam(':limit', $usedLimit, PDO::PARAM_INT);
$used->bindParam(':offset', $usedOffset, PDO::PARAM_INT);
$used->execute();
$dataUsed = $used->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนข้อมูลทั้งหมดที่ได้รับการอนุมัติแล้ว
$totalUsedStmt = $conn->prepare("SELECT COUNT(*) FROM approve_to_reserve");
$totalUsedStmt->execute();
$totalUsedData = $totalUsedStmt->fetchColumn();
$totalUsedPages = ceil($totalUsedData / $usedLimit);

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
    $sql = "SELECT * FROM approve_to_reserve WHERE reservation_date BETWEEN :start_date AND :end_date";
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

try {
    if (isset($_GET['id'])) {
        $reservation_date = $_GET['id']; // รับค่า id ที่เป็นรูปแบบวันที่ YYYY-MM-DD

        // แปลงรูปแบบวันที่เพื่อใช้ในฐานข้อมูล (ถ้าจำเป็น)
        $stmt = $conn->prepare("
            SELECT * FROM approve_to_reserve
            WHERE DATE(reservation_date) = :reservation_date
        ");
        $stmt->bindParam(":reservation_date", $reservation_date, PDO::PARAM_STR); // ใช้ PDO::PARAM_STR สำหรับวันที่
        $stmt->execute();
        $detailsdataUsed = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/approval.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/reservation_details.css">
</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php') ?>
    </header>
    <?php if (isset($_SESSION['approve_success'])) : ?>
        <div class="toast">
            <div class="toast_content">
                <i class="fas fa-solid fa-check check"></i>
                <div class="toast_content_message">
                    <span class="text"><?php echo $_SESSION['approve_success']; ?></span>
                </div>
                <i class="fa-solid fa-xmark close"></i>
            </div>
        </div>
        <?php unset($_SESSION['approve_success']); ?>
    <?php endif ?>
    <div class="approve_section">
        <div class="header_approve_section">
            <a class="historyBACK" href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/approve_request') {
                    echo '<a href="/approve_request">อนุมัติการขอใช้</a>';
                } elseif ($request_uri == '/approve_request/calendar') {
                    echo '<a href="/approve_request/calendar">ปฎิทินการขอใช้</a>';
                } elseif ($request_uri == '/approve_request/viewlog/details') {
                    echo '<a href="/approve_request/calendar">ปฎิทินการขอใช้</a>
        <span>&gt;</span>
        <a href="' . $reservation_date . '">รายละเอียด ' . thai_date_time_3($reservation_date) . '</a>';
                }
                ?>
            </div>
        </div>
        <div class="approve_btn">
            <a href="/approve_request" class="<?= ($request_uri == '/approve_request') ? 'active' : ''; ?> btn_approve_01">อนุมัติการขอใช้</a>
            <a href="/approve_request/calendar" class="<?= ($request_uri == '/approve_request/calendar' || $request_uri == '/approve_request/viewlog/details') ? 'active' : ''; ?> btn_approve_02">ปฎิทินการขอใช้</a>
        </div>
        <?php if ($request_uri == '/approve_request') : ?>
            <div class="approve_table_section">
                <?php if (empty($data)) : ?>
                    <div id="loading">
                        <div class="spinner"></div>
                        <p>กำลังโหลดข้อมูล...</p>
                    </div>
                    <div class="approve_not_found_section">
                        <i class="fa-solid fa-xmark"></i>
                        <span id="B">ไม่พบข้อมูลการขอใช้</span>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const loadingElement = document.getElementById('loading');
                            const notFoundElement = document.querySelector('.approve_not_found_section');

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
                    <div class="approve_table">
                        <div class="approve_header">
                            <span>รายการที่ขอใช้ทั้งหมด <span id="B"><?php echo $totalData; ?></span> รายการ</span>
                        </div>
                        <?php
                        $previousSn = null;
                        foreach ($data as $row) :
                            if ($previousSn != $row['serial_number']) :
                        ?>
                                <div class="approveData">
                                    <div class="approveheader">
                                        <i class="fa-solid fa-clock"></i>
                                        <div class="s_number"><span id="B">หมายเลขรายการ </span><?php echo $row['serial_number']; ?></div>
                                    </div>
                                    <div class="approve_table_row">
                                        <div class="name_use"><?php echo $row['name_user']; ?> ได้ทำการขอใช้<?php echo thai_date_time_2($row['created_at']); ?></div>
                                        <div class="item_name">
                                            <?php
                                            $items = explode(',', $row['list_name']);
                                            foreach ($items as $item) {
                                                list($product_name, $quantity) = explode('(', $item);
                                                $product_name = trim($product_name);
                                                $quantity = str_replace(')', '', trim($quantity));
                                                echo "- " . $product_name . ' <span>(' . $quantity . ' รายการ)</span><br>';
                                            }
                                            ?>
                                        </div>
                                        <div class="return"><span id="B">ขอใช้</span id="B"><?php echo thai_date_time_2($row['reservation_date']); ?>
                                            <span id="B">ถึง</span id="B"><?php echo thai_date_time_2($row['end_date']); ?>
                                        </div>
                                        <div class="approval">
                                            <span class="confirm_approve" data-modal="<?= htmlspecialchars($row['ID']) ?>">
                                                <i class="fa-solid fa-circle-check"></i>
                                                อนุมัติการขอใช้
                                            </span>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $previousSn = $row['serial_number'];
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <div class="confirmApprovePopup" id="modal_<?= htmlspecialchars($row['ID']) ?>">
                        <div class="confirmApprove_content">
                            <div class="confirmApprovePopup_sec01">
                                <i class="fa-solid fa-exclamation"></i>
                                <span id="B">ยืนยันการอนุมัติการขอใช้</span>
                            </div>
                            <div class="confirmApprovePopup_sec02">
                                <form method="POST" action="<?php echo $base_url; ?>/models/approve_request.php">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['ID']) ?>">
                                    <input type="hidden" name="userID" value="<?= htmlspecialchars($row['userID']) ?>">
                                    <button type="submit" name="confirm" class="confirm">ยืนยัน</button>
                                </form>
                                <div class="cancelApprove">
                                    <span id="B">ปิดหน้าต่าง</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- pagination -->
                    <?php if ($pagination_display) : ?>
                        <div class="pagination">
                            <?php if ($page > 1) : ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="prev"><i class="fa-solid fa-arrow-left"></i> หน้าก่อน</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages) : ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="next">หน้าถัดไป <i class="fa-solid fa-arrow-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <!-- /approve_request/calendar -->
        <?php elseif ($request_uri == '/approve_request/calendar') : ?>
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
                                        <?php
                                        $showLink = false; // สถานะการแสดงแท็ก <a>
                                        foreach ($calendar[$i] as $reservation) :
                                            if (!empty($reservation) && !$showLink) :
                                                $showLink = true; // ตั้งค่าสถานะเป็นจริงเมื่อแสดงแท็ก <a>
                                        ?>
                                                <a href="<?php echo $base_url; ?>/approve_request/viewlog/details?id=<?= $day_date; ?>">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                </a>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

        <?php elseif ($request_uri == '/approve_request/viewlog/details') : ?>
            <div id="loading">
                <div class="spinner"></div>
                <p>กำลังโหลดข้อมูล...</p>
            </div>
            <div class="viewLog_request_Details">
                <?php foreach ($detailsdataUsed as $Data) : ?>
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <span id="B">รายละเอียด </span>หมายเลขรายการ <?= htmlspecialchars($Data['serial_number'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="viewLog_request_body">
                            <div class="viewLog_request_content">
                                <div class="viewLog_request_content_1">
                                    <span id="B">ชื่อผู้ขอใช้</span>
                                    <?= htmlspecialchars($Data['name_user'], ENT_QUOTES, 'UTF-8') ?>
                                    <span id="B">ทำการขอใช้เมื่อ</span>
                                    <?= thai_date_time_2(htmlspecialchars($Data['created_at'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                                <div class="viewLog_request_content_2">
                                    <?php
                                    $items = explode(',', $Data['list_name']);
                                    foreach ($items as $item) {
                                        list($product_name, $quantity) = explode('(', $item);
                                        $product_name = trim($product_name);
                                        $quantity = str_replace(')', '', trim($quantity));
                                        echo "- " . $product_name . ' <span id="B">(' . $quantity . ' รายการ)</span><br>';
                                    }
                                    ?>
                                </div>
                                <div class="viewLog_request_content_3">
                                    <span id="B">ขอใช้</span>
                                    <?= thai_date_time_2(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    <span id="B">ถึง</span>
                                    <?= thai_date_time_2(htmlspecialchars($Data['end_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    <span id="B">คืนเมื่อ</span>
                                    <?php if ($Data['date_return'] === NULL) : ?>
                                        --
                                    <?php else : ?>
                                        <?= thai_date_time_2(htmlspecialchars($Data['date_return'], ENT_QUOTES, 'UTF-8')); ?>
                                    <?php endif ?>
                                </div>
                                <div class="viewLog_request_content_4">
                                    <span id="B">ผู้อนุมัติ</span>
                                    <?= htmlspecialchars($Data['approver'], ENT_QUOTES, 'UTF-8') ?>
                                    <?= thai_date_time_2(htmlspecialchars($Data['approvaldatetime'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // ดึงองค์ประกอบการโหลดและเนื้อหาหลัก
                    const loadingElement = document.getElementById('loading');
                    const detailsElement = document.querySelector('.viewLog_request_Details');

                    // หน่วงเวลาในการซ่อนการโหลดและแสดงเนื้อหาหลัก
                    setTimeout(function() {
                        loadingElement.style.display = 'none'; // ซ่อนการโหลด
                        if (detailsElement) {
                            detailsElement.style.display = 'flex'; // แสดงเนื้อหาหลัก (หรือเปลี่ยนเป็น 'block' ตามต้องการ)
                            detailsElement.classList.add('visible'); // เพิ่มคลาส visible เพื่อแสดงอนิเมชัน

                            // แสดงการแจ้งเตือนทีละรายการ
                            const requestDetails = document.querySelectorAll('.viewLog_request_MAIN');
                            let index = 0;

                            function showNextNotification() {
                                if (index < requestDetails.length) {
                                    requestDetails[index].classList.add('visible');
                                    index++;
                                    setTimeout(showNextNotification, 200); // หน่วงเวลาในการแสดงการแจ้งเตือนแต่ละรายการ
                                }
                            }

                            showNextNotification();
                        }
                    }, 1500); // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล
                });
            </script>
        <?php else : ?>
            <div class="viewNotfound">
                <i class="fa-solid fa-database"></i>
                <?php var_dump($reservation_date); ?>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/noti_toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
            const modalOpenButtons = document.querySelectorAll(".confirm_approve");

            // ฟังก์ชันเพื่อเปิด modal
            modalOpenButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    // รับ ID ของ modal ที่จะเปิด
                    const modalId = button.getAttribute('data-modal');
                    const modal = document.getElementById('modal_' + modalId);
                    if (modal) {
                        // แสดง modal โดยตั้งค่า style.display เป็น 'flex'
                        modal.style.display = "flex";
                    }
                });
            });

            // ค้นหาทุกปุ่มปิด modal
            const modalCloseButtons = document.querySelectorAll(".cancelApprove");

            // ฟังก์ชันเพื่อปิด modal
            modalCloseButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    // ปิด modal
                    const modal = button.closest('.confirmApprovePopup');
                    if (modal) {
                        modal.style.display = "none";
                    }
                });
            });

            // ปิด modal เมื่อคลิกที่พื้นหลังของ modal
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('confirmApprovePopup')) {
                    event.target.style.display = 'none';
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ดึงองค์ประกอบการโหลดและเนื้อหาหลัก
            const loadingElement = document.getElementById('loading');
            const detailsElement = document.querySelector('.approve_table');

            // หน่วงเวลาในการซ่อนการโหลดและแสดงเนื้อหาหลัก
            setTimeout(function() {
                loadingElement.style.display = 'none'; // ซ่อนการโหลด
                if (detailsElement) {
                    detailsElement.style.display = 'block'; // แสดงเนื้อหาหลัก (หรือเปลี่ยนเป็น 'block' ตามต้องการ)
                    detailsElement.classList.add('visible'); // เพิ่มคลาส visible เพื่อแสดงอนิเมชัน
                    // แสดงการแจ้งเตือนทีละรายการ
                    const requestDetails = document.querySelectorAll('.approveData');
                    let index = 0;

                    function showNextNotification() {
                        if (index < requestDetails.length) {
                            requestDetails[index].classList.add('visible');
                            index++;
                            setTimeout(showNextNotification, 200); // หน่วงเวลาในการแสดงการแจ้งเตือนแต่ละรายการ
                        }
                    }
                    showNextNotification();
                }
            }, 1500); // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล
        });
    </script>
</body>

</html>