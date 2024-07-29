<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

$stmt = $conn->prepare("
    SELECT crud.*, info_sciname.*, 
    DATEDIFF(CURDATE(), IFNULL(last_maintenance_date, CURDATE())) AS days_since_last_maintenance
    FROM crud 
    LEFT JOIN info_sciname ON crud.ID = info_sciname.ID
    WHERE last_maintenance_date IS NULL 
    OR last_maintenance_date < DATE_ADD(CURDATE(), INTERVAL 10 DAY) 
    ORDER BY crud.ID ASC");
$stmt->execute();
$maintenance_notify = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("
    SELECT * 
    FROM crud 
    LEFT JOIN logs_maintenance ON crud.serial_number = logs_maintenance.serial_number
    WHERE availability = 1 
    AND end_maintenance > DATE_ADD(CURDATE(), INTERVAL 2 DAY) 
    ORDER BY crud.serial_number ASC");
$stmt->execute();
$end_maintenance_notify = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user data from database
try {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

$searchTitle = "";
$searchValue = "";

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Fetch maintenance data
try {
    $searchQuery = isset($_GET["search"]) && !empty($_GET["search"]) ? "%" . $_GET["search"] . "%" : null;

    if ($request_uri == '/maintenance_start') {
        $query = "SELECT * FROM crud 
                  LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number 
                  WHERE crud.availability = 0";
        if ($searchQuery) {
            $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
        }
        $query .= " ORDER BY crud.ID ASC";

        $stmt = $conn->prepare($query);
        if ($searchQuery) {
            $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
        }
        $stmt->execute();
        $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($request_uri == '/maintenance_end') {
        // สร้างคำสั่ง SQL โดยเลือกข้อมูลที่ใหม่ที่สุดจาก logs_maintenance
        $query = "SELECT crud.*, info_sciname.*, logs_maintenance.* FROM crud
                  LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number
                  LEFT JOIN (
                      SELECT * FROM logs_maintenance AS lm1
                      WHERE lm1.created_at = (
                          SELECT MAX(lm2.created_at) 
                          FROM logs_maintenance AS lm2 
                          WHERE lm2.serial_number = lm1.serial_number
                      )
                  ) AS logs_maintenance ON crud.serial_number = logs_maintenance.serial_number
                  WHERE crud.availability != 0";

        // เพิ่มเงื่อนไขการค้นหา (ถ้ามี)
        if ($searchQuery) {
            $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
        }

        // เรียงลำดับข้อมูลตามวันที่สร้างใน logs_maintenance
        $query .= " ORDER BY logs_maintenance.created_at DESC";

        // เตรียม statement สำหรับการรันคำสั่ง SQL
        $stmt = $conn->prepare($query);

        // ผูกค่าพารามิเตอร์สำหรับการค้นหา (ถ้ามี)
        if ($searchQuery) {
            $searchParam = "%{$searchQuery}%";
            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        }

        // รันคำสั่ง SQL
        $stmt->execute();

        // ดึงข้อมูลทั้งหมด
        $maintenance_success = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การบำรุงรักษา</title>
    <link href="<?php echo $base_url ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/maintenance.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="maintenance">
        <?php if (isset($_SESSION['maintenanceSuccess'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['maintenanceSuccess']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['maintenanceSuccess']); ?>
        <?php endif ?>
        <div class="header_maintenance_section">
            <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/maintenance_dashboard') {
                    echo '<a href="/maintenance_dashboard">แดชบอร์ด</a>';
                }
                if ($request_uri == '/maintenance_start') {
                    echo '<a href="/maintenance_start">เริ่มการบำรุงรักษา</a>';
                }
                if ($request_uri == '/maintenance_end') {
                    echo '<a href="/maintenance_end">สิ้นสุดการบำรุงรักษา</a>';
                }
                ?>
            </div>
        </div>
        <div class="maintenance_section_btn">
            <div class="btn_maintenance_all">
                <a href="/maintenance_dashboard" class="<?= ($request_uri == '/maintenance_dashboard') ? 'active' : ''; ?> btn_maintenance_01">
                    แดชบอร์ด</a>
                <a href="/maintenance_start" class="<?= ($request_uri == '/maintenance_start') ? 'active' : ''; ?> btn_maintenance_02">
                    เริ่มการบำรุงรักษา</a>
                <a href="/maintenance_end" class="<?= ($request_uri == '/maintenance_end') ? 'active' : ''; ?> btn_maintenance_02">
                    สิ้นสุดการบำรุงรักษา</a>
                <a href="/maintenance/report"><i class="fa-solid fa-file-pdf"></i></a>
            </div>
            <?php
            if ($request_uri == '/maintenance_start' || $request_uri == '/maintenance_end') : ?>
                <form class="maintenance_search_header" method="get">
                    <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                    <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            <?php endif ?>
        </div>
        <?php if ($request_uri == '/maintenance_dashboard') : ?>
            <div class="maintenanceDashboard">
                <div class="staff_section_2">
                    <div class="staff_header_maintenance">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span id="B">การบำรุงรักษา</span>
                    </div>
                    <div class="staff_content_row3">
                        <?php if (empty($maintenance_notify)) : ?>
                            <div class="approve_not_found_section">
                                <i class="fa-solid fa-xmark"></i>
                                <span id="B">ไม่พบข้อมูลอุปกรณ์ และเครื่องมือ</span>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($maintenance_notify)) : ?>
                            <div class="approve_container">
                                <?php foreach ($maintenance_notify as $row) : ?>
                                    <div class="approve_row">
                                        <div class="defualt_row">
                                            <div class="serial_number">
                                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                                <?php echo htmlspecialchars($row['serial_number']); ?>
                                            </div>
                                            <div class="items">
                                                <a href="<?php echo $base_url; ?>/maintenance/details?id=<?= $row['ID'] ?>">
                                                    <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            </div>
                                            <div class="reservation_date">
                                                <?php
                                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($row['last_maintenance_date']);
                                                if ($daysSinceMaintenance === "ไม่เคยได้รับการบำรุงรักษา") {
                                                    echo $daysSinceMaintenance;
                                                } else {
                                                    echo "ไม่ได้รับการบำรุงรักษามามากกว่า " . $daysSinceMaintenance . " วัน";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="expand_row">
                                            <div>
                                                <?php
                                                if ($row['last_maintenance_date'] === null) {
                                                    echo "ไม่เคยมีประวัติการบำรุงรักษา";
                                                } else {
                                                    echo thai_date_time_3(htmlspecialchars($row['last_maintenance_date']));
                                                }
                                                ?>
                                            </div>
                                            <div><span id="B">ประเภท</span>
                                                <?php echo htmlspecialchars($row['categories']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
                <!-- -------------- ROW 4 --------------- -->
                <div class="staff_notification_2">
                    <div class="staff_notification_maintenance_header">
                        <i class="fa-solid fa-bell"></i>
                        <span id="B">แจ้งเตือนการบำรุงรักษา</span>
                    </div>
                    <div class="staff_notification_body">
                        <?php if (!empty($end_maintenance_notify)) : ?>
                            <div class="staff_notification_stack">
                                <?php foreach ($end_maintenance_notify as $datas) : ?>
                                    <div class="staff_notification_data">
                                        <span class="staff_notification_data_1">
                                            <?php echo htmlspecialchars($datas['sci_name']); ?>
                                        </span>
                                        <span class="staff_notification_data_2">ใกล้ถึงวันสิ้นสุดการบำรุงรักษา
                                            <?php echo htmlspecialchars(thai_date_time_3($datas['end_maintenance'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="non_notification_stack">
                                <div class="non_notification_stack_1">
                                    <i class="fa-solid fa-envelope"></i>
                                    <span id="B">ไม่มีแจ้งเตือนการบำรุงรักษาที่ใกล้กำหนดการ</span>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endif ?>
        
        <?php if ($request_uri == '/maintenance_start') : ?>
            <?php if (!empty($maintenance)) : ?>
                <div class="table_maintenance">
                    <?php foreach ($maintenance as $row) : ?>
                        <div class="table_maintenanceContent">
                            <div class="table_maintenanceContent_00">
                                <div class="table_maintenanceContent_1">
                                    <a href="<?php echo $base_url; ?>/maintenance_start/details?id=<?= $row['ID'] ?>">
                                        <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8') ?>)
                                        <i class="fa-solid fa-square-arrow-up-right"></i>
                                    </a>
                                </div>
                                <div class="table_maintenanceContent_2">
                                    ประเภท <?= htmlspecialchars($row['categories'], ENT_QUOTES, 'UTF-8') ?>
                                    ติดตั้ง ณ <?= htmlspecialchars(thai_date($row['installation_date']), ENT_QUOTES, 'UTF-8') ?>
                                    บำรุงรักษาล่าสุด
                                    <?php
                                    if ($row['last_maintenance_date'] == NULL) {
                                        echo '-';
                                    } else {
                                        echo htmlspecialchars(thai_date($row['last_maintenance_date']), ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="MaintenanceButton">
                                <span class="maintenance_button">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                </span>
                                <form action="<?php echo $base_url ?>/models/maintenanceProcess.php" method="post" class="maintenance_form">
                                    <div class="maintenance_popup">
                                        <div class="maintenance_popup_content">
                                            <div class="maintenance_section_header">
                                                <span id="B">กรอกข้อมูลการบำรุงรักษา</span>
                                                <div class="modalClose" id="closeMaintenance">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </div>
                                            </div>
                                            <div class="maintenace_popup">
                                                <div class="inputMaintenance">
                                                    <label for="start_maintenance">วันเริ่มต้นการบำรุงรักษา</label>
                                                    <input type="date" id="start_maintenance" name="start_maintenance" required>
                                                </div>
                                                <div class="inputMaintenance">
                                                    <label for="end_maintenance">วันสิ้นสุดการบำรุงรักษา</label>
                                                    <input type="date" id="end_maintenance" name="end_maintenance" required>
                                                </div>
                                                <div class="inputMaintenance">
                                                    <label for="note">หมายเหตุ</label>
                                                    <input type="text" id="note" name="note" placeholder="หมายเหตุ">
                                                </div>
                                                <div class="inputMaintenance">
                                                    <label for="name_staff">ชื่อ - นามสกุล ผู้ดูแล</label>
                                                    <input type="text" id="name_staff" name="name_staff" placeholder="ชื่อ - นามสกุล ผู้ดูแล">
                                                </div>
                                                <input type="hidden" name="selected_ids" value="<?= htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php else : ?>
                <div class="maintenance_not_found">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล!</span>
                </div>
            <?php endif ?>
        <?php endif ?>

        <?php if ($request_uri == '/maintenance_end') : ?>
            <?php if (!empty($maintenance_success)) : ?>
                <div class="table_maintenance">
                    <?php foreach ($maintenance_success as $row) : ?>
                        <div class="table_maintenanceContent">
                            <div class="table_maintenanceContent_00">
                                <div class="table_maintenanceContent_1">
                                    <a href="<?php echo $base_url; ?>/maintenance_end/details?id=<?= $row['ID'] ?>">
                                        <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8') ?>)
                                        <i class="fa-solid fa-square-arrow-up-right"></i>
                                    </a>
                                </div>
                                <div class="table_maintenanceContent_2">
                                    ประเภท<?= htmlspecialchars($row['categories'], ENT_QUOTES, 'UTF-8') ?>
                                    เริ่มการบำรุงรักษา<?= htmlspecialchars(thai_date($row['start_maintenance']), ENT_QUOTES, 'UTF-8') ?>
                                    ถึง
                                    <?php
                                    if ($row['last_maintenance_date'] == NULL) {
                                        echo '-';
                                    } else {
                                        echo htmlspecialchars(thai_date($row['end_maintenance']), ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="MaintenanceButton">
                                <span class="maintenance_button">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                </span>
                                <form action="<?php echo $base_url ?>/models/maintenanceEndprocess.php" method="post" class="maintenance_form">
                                    <div class="maintenance_popup">
                                        <div class="maintenance_popup_content">
                                            <div class="maintenance_section_header">
                                                <span id="B">กรอกข้อมูลการบำรุงรักษา</span>
                                                <div class="modalClose" id="closeMaintenance">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </div>
                                            </div>
                                            <div class="maintenace_popup">
                                                <div class="inputMaintenance">
                                                    <label for="end_maintenance">วันสิ้นสุดการบำรุงรักษา</label>
                                                    <input type="date" id="end_maintenance" name="end_maintenance" required>
                                                </div>
                                                <div class="inputMaintenance">
                                                    <label for="details_maintenance">รายละเอียดการบำรุงรักษา</label>
                                                    <textarea class="details_maintenance" name="details_maintenance" id="details_maintenance" placeholder="รายละเอียดการบำรุงรักษา"></textarea>
                                                </div>
                                                <input type="hidden" name="serial_ids" value="<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="selected_ids" value="<?= htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="confirm_maintenance" name="complete_maintenance">ยืนยัน</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php else : ?>
                <div class="maintenance_not_found">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif ?>
        <?php endif ?>
    </div>
    <script src="<?php echo $base_url ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url ?>/assets/js/noti_toast.js"></script>
    <script src="<?php echo $base_url ?>/assets/js/maintenance.js"></script>
</body>

</html>