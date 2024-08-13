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

// Fetch user data
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

$searchValue = "";
if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
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
        $query = "SELECT 
                      crud.*, 
                      info_sciname.*, 
                      logs_maintenance.*
                  FROM crud
                  LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number
                  LEFT JOIN (
                      SELECT lm1.*
                      FROM logs_maintenance lm1
                      INNER JOIN (
                          SELECT serial_number, MAX(created_at) AS latest_date
                          FROM logs_maintenance
                          GROUP BY serial_number
                      ) lm2 ON lm1.serial_number = lm2.serial_number AND lm1.created_at = lm2.latest_date
                  ) AS logs_maintenance ON crud.serial_number = logs_maintenance.serial_number
                  WHERE crud.availability != 0";

        if ($searchQuery) {
            $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
        }

        $query .= " GROUP BY crud.sci_name 
                    HAVING SUM(crud.availability = 1) > 0 
                    ORDER BY logs_maintenance.created_at DESC";

        $stmt = $conn->prepare($query);

        if ($searchQuery) {
            $searchParam = "%{$searchQuery}%";
            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        }

        $stmt->execute();
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
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/maintenance.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/pagination.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="maintenance">
        <?php if (isset($_SESSION['maintenanceSuccess'])) : ?>
            <div class="toast">
                <div class="toast_content">
                    <i class="fas fa-solid fa-xmark check"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?php echo $_SESSION['maintenanceSuccess']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
                </div>
            </div>
            <?php unset($_SESSION['maintenanceSuccess']); ?>
        <?php endif ?>
        <?php if (isset($_SESSION['end_maintenanceSuccess'])) : ?>
            <div class="toast">
                <div class="toast_content">
                    <i class="fas fa-solid fa-xmark check"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?php echo $_SESSION['end_maintenanceSuccess']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
                </div>
            </div>
            <?php unset($_SESSION['end_maintenanceSuccess']); ?>
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
            <?php if ($request_uri == '/maintenance_start' || $request_uri == '/maintenance_end') : ?>
                <form class="maintenance_search_header" method="get">
                    <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                    <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            <?php endif ?>
        </div>
        <!-- -------------- /maintenance_dashboard --------------- -->
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
                <?php
                $itemsPerPage = 30;
                $totalItems = count($maintenance);
                $totalPages = ceil($totalItems / $itemsPerPage);
                $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($currentPage < 1) $currentPage = 1;
                if ($currentPage > $totalPages) $currentPage = $totalPages;
                $startIndex = ($currentPage - 1) * $itemsPerPage;
                $endIndex = min($startIndex + $itemsPerPage, $totalItems);
                $currentPageItems = array_slice($maintenance, $startIndex, $itemsPerPage);
                ?>
                <div class="table_maintenance">
                    <?php foreach ($currentPageItems as $row) : ?>
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
                                    <?= $row['last_maintenance_date'] ? htmlspecialchars(thai_date($row['last_maintenance_date']), ENT_QUOTES, 'UTF-8') : '-' ?>
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
                                                <input type="hidden" name="serialNumber" value="<?= htmlspecialchars($row['serial_number']); ?>">
                                                <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <!-- PAGINATION PAGE -->
                <?php if ($totalPages > 1) : ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1) : ?>
                            <a href="?page=1<?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&laquo;</a>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&lsaquo;</a>
                        <?php endif; ?>
                        <?php
                        $totalPages = ceil($totalItems / $itemsPerPage);
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $currentPage) {
                                echo "<a class='active'>$i</a>";
                            } else {
                                echo "<a href='?page=$i" . ($searchValue ? '&search=' . $searchValue : '') . "'>$i</a>";
                            }
                        }
                        ?>
                        <?php if ($currentPage < $totalPages) : ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&rsaquo;</a>
                            <a href="?page=<?php echo $totalPages; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="no_maintenance">
                    <span>ไม่พบข้อมูลการบำรุงรักษา</span>
                </div>
            <?php endif ?>
        <?php endif ?>
        <!-- ---------------- /maintenance_start --------------- -->
        <?php if ($request_uri == '/maintenance_end') : ?>
            <?php if (!empty($maintenance_success)) : ?>
                <?php
                $itemsPerPage = 10;
                $totalItems = count($maintenance_success);
                $totalPages = ceil($totalItems / $itemsPerPage);
                $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

                // Ensure current page is within valid range
                if ($currentPage < 1) $currentPage = 1;
                if ($currentPage > $totalPages) $currentPage = $totalPages;

                $startIndex = ($currentPage - 1) * $itemsPerPage;
                $currentPageItems = array_slice($maintenance_success, $startIndex, $itemsPerPage);
                ?>
                <div class="table_maintenance">
                    <?php foreach ($currentPageItems as $row) : ?>
                        <div class="table_maintenanceContent">
                            <div class="table_maintenanceContent_00">
                                <div class="table_maintenanceContent_1">
                                    <a href="<?php echo $base_url; ?>/maintenance_end/maintenanceDetails?id=<?= htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8') ?>)
                                        <i class="fa-solid fa-square-arrow-up-right"></i>
                                    </a>
                                </div>
                                <div class="table_maintenanceContent_2">
                                    ประเภท <?= htmlspecialchars($row['categories'], ENT_QUOTES, 'UTF-8') ?>
                                    ติดตั้ง ณ <?= htmlspecialchars(thai_date($row['installation_date']), ENT_QUOTES, 'UTF-8') ?>
                                    บำรุงรักษาล่าสุด
                                    <?= $row['last_maintenance_date'] ? htmlspecialchars(thai_date($row['last_maintenance_date']), ENT_QUOTES, 'UTF-8') : '-' ?>
                                </div>
                            </div>
                            <div class="MaintenanceButton">
                                <span class="maintenance_button">
                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                    <input name="selected_ids" value="<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8'); ?>">
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
                                                    <label for="note">รายละเอียดการบำรุงรักษา</label>
                                                    <input type="text" id="note" name="note" placeholder="หมายเหตุ">
                                                </div>
                                                <input type="hidden" name="selected_ids" value="<?= htmlspecialchars($row['serial_number'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- PAGINATION PAGE -->
                <?php if ($totalPages > 1) : ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1) : ?>
                            <a href="?page=1<?= $searchValue ? '&search=' . urlencode($searchValue) : ''; ?>">&laquo;</a>
                            <a href="?page=<?= $currentPage - 1; ?><?= $searchValue ? '&search=' . urlencode($searchValue) : ''; ?>">&lsaquo;</a>
                        <?php endif; ?>
                        <?php
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $currentPage) {
                                echo "<a class='active'>$i</a>";
                            } else {
                                echo "<a href='?page=$i" . ($searchValue ? '&search=' . urlencode($searchValue) : '') . "'>$i</a>";
                            }
                        }
                        ?>
                        <?php if ($currentPage < $totalPages) : ?>
                            <a href="?page=<?= $currentPage + 1; ?><?= $searchValue ? '&search=' . urlencode($searchValue) : ''; ?>">&rsaquo;</a>
                            <a href="?page=<?= $totalPages; ?><?= $searchValue ? '&search=' . urlencode($searchValue) : ''; ?>">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="no_maintenance">
                    <span>ไม่พบข้อมูลการบำรุงรักษา</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url ?>/assets/js/maintenance.js"></script>
</body>

</html>