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

$searchValue = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$searchQuery = $searchValue ? "%" . $searchValue . "%" : '';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$results_per_page = 48;
$offset = ($page - 1) * $results_per_page;

$sql = "SELECT * FROM logs_maintenance WHERE 1=1";
$params = [];

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND start_maintenance BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
}

if ($searchValue) {
    $sql .= " AND (sci_name LIKE :search OR serial_number LIKE :search)";
    $params[':search'] = $searchQuery;
}

// Remove LIMIT and OFFSET from SQL here
$sql .= " ORDER BY start_maintenance DESC LIMIT $offset, $results_per_page";

// Fetch data from database
try {
    $historyStmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $historyStmt->bindValue($key, $value);
    }
    $historyStmt->execute();
    $historyData = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Count total records
$total_records_query = "SELECT COUNT(*) AS total FROM logs_maintenance WHERE 1=1";
$params_count = [];

if (!empty($start_date) && !empty($end_date)) {
    $total_records_query .= " AND start_maintenance BETWEEN :start_date AND :end_date";
    $params_count[':start_date'] = $start_date;
    $params_count[':end_date'] = $end_date;
}

if ($searchValue) {
    $total_records_query .= " AND (sci_name LIKE :search OR serial_number LIKE :search)";
    $params_count[':search'] = $searchQuery;
}

try {
    $stmt_count = $conn->prepare($total_records_query);
    foreach ($params_count as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_records = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

$total_pages = ceil($total_records / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการบำรุงรักษา</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/maintenance.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/maintenance/report') {
                    echo '<a href="/maintenance/report">รายงานการบำรุงรักษา</a>';
                }
                ?>
            </div>
        </div>
        <div class="report-button">
            <form class="form_1" action="<?php echo $base_url; ?>/maintenance/report" method="get">
                <div class="view_maintenance_column">
                    <div class="view_maintenance_input">
                        <label id="B" for="startDate">ช่วงเวลาเริ่มต้น</label>
                        <input type="date" id="startDate" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="view_maintenance_input">
                        <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                        <div class="view_Maintenance_btn">
                            <input type="date" id="endDate" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
                            <button type="submit" class="searchReport"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- ส่วนของการสร้าง PDF -->
        <div class="view_report_table_header">
            <div class="view_report_table_header_pdf">
                <span id="B">ประวัติการบำรุงรักษา</span>
                <form id="pdfForm" action="<?php echo $base_url; ?>/models/PDF_maintenance_report.php" method="GET">
                    <?php if (!empty($start_date) && !empty($end_date)) : ?>
                        <input type="hidden" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>">
                        <input type="hidden" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    <?php endif; ?>
                    <button type="submit" class="create_pdf">สร้างรายงาน</button>
                </form>
            </div>
            <!-- ปุ่มสำหรับรีเซ็ตการค้นหาและแสดงข้อมูลทั้งหมด -->
            <form class="form_2" action="<?php echo $base_url; ?>/maintenance/report" method="GET">
                <button type="submit" class="reset_data">แสดงข้อมูลทั้งหมด</button>
            </form>
        </div>
        <div id="loading">
            <div class="spinner"></div>
            <p>กำลังโหลดข้อมูล...</p>
        </div>
        <div class="maintenanceReport">
            <?php if ($total_records > 0) : ?>
                <?php if (count($historyData) > 0) : ?>
                    <?php foreach ($historyData as $row) : ?>
                        <div class="maintenanceReport_ROW">
                            <div class="history-item_1">
                                <?php echo htmlspecialchars($row["sci_name"]); ?>
                                (<?php echo htmlspecialchars($row["serial_number"]); ?>)
                            </div>
                            <div class="history-item_2">
                                บำรุงรักษาตั้งแต่<?php echo thai_date_time_4($row["start_maintenance"]); ?>
                                ถึง <?php echo thai_date_time_4($row["end_maintenance"]); ?>
                            </div>
                            <div class="history-item_2">
                                หมายเหตุ:
                                <?php echo htmlspecialchars($row["note"] ?: '--'); ?>
                            </div>
                            <div class="history-item_2">
                                รายละเอียดการบำรุงรักษา:
                                <?php echo htmlspecialchars($row["details_maintenance"] ?: '--'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="history_rowNOTFOUND">
                        <i class="fa-solid fa-database"></i>
                        ไม่พบประวัติการบำรุงรักษา
                    </div>
                <?php endif; ?>

                <!-- PAGINATION PAGE -->
                <?php if ($total_pages > 1) : ?>
                    <div class="pagination">
                        <?php if ($page > 1) : ?>
                            <a href="?page=1<?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&laquo;</a>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&lsaquo;</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <?php if ($i == $page) : ?>
                                <a class='active'><?php echo $i; ?></a>
                            <?php else : ?>
                                <a href="?page=<?php echo $i . ($searchValue ? '&search=' . $searchValue : ''); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages) : ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&rsaquo;</a>
                            <a href="?page=<?php echo $total_pages; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="history_rowNOTFOUND">
                    <i class="fa-solid fa-database"></i>
                    ไม่พบข้อมูลในช่วงเวลาที่กำหนด
                </div>
            <?php endif; ?>
        </div>
        <footer>
            <?php include_once 'assets/includes/footer_2.php'; ?>
        </footer>

        <!-- JavaScript -->
        <script src="<?= $base_url; ?>/assets/js/ajax.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loading = document.getElementById('loading');
                const content = document.querySelector('.maintenanceReport');

                // หน่วงเวลาในการซ่อนการโหลดและแสดงเนื้อหาหลัก
                setTimeout(function() {
                    loading.style.display = 'none'; // ซ่อนการโหลด
                    if (content) {
                        content.style.display = 'flex'; // แสดงเนื้อหาหลัก (หรือเปลี่ยนเป็น 'block' ตามต้องการ)
                        content.classList.add('visible'); // เพิ่มคลาส visible เพื่อแสดงอนิเมชัน
                    }

                    // แสดงการแจ้งเตือนทีละรายการ
                    const viewReportTableContent = document.querySelectorAll('.maintenanceReport_ROW');
                    let index = 0;

                    function showNextNotification() {
                        if (index < viewReportTableContent.length) {
                            viewReportTableContent[index].classList.add('visible');
                            index++;
                            setTimeout(showNextNotification, 200); // หน่วงเวลาในการแสดงการแจ้งเตือนแต่ละรายการ
                        }
                    }

                    showNextNotification();
                }, 1500); // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล
            });
        </script>
</body>

</html>