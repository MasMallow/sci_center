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

$searchTitle = "";
$searchValue = "";
$params = [];
$result = [];

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

// สร้างคำสั่ง SQL ตามตัวกรอง userID และช่วงเวลา
$sql = "SELECT * FROM logs_maintenance WHERE 1=1";

// ตรวจสอบและกำหนดค่า start_date และ end_date
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $sql .= " AND start_maintenance BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $_GET['start_date'];
    $params[':end_date'] = $_GET['end_date'];
}

// เพิ่มคำสั่ง ORDER BY
$sql .= " ORDER BY start_maintenance DESC";

try {
    $historyStmt = $conn->prepare($sql);

    // กำหนดค่าให้กับ parameter
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $historyStmt->bindValue($key, $value);
        }
    }

    $historyStmt->execute();
    $historyData = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>ประวัติการบำรุงรักษา</title>
    <link href="<?php echo $base_url ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
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
                        <input type="date" id="startDate" name="start_date" value="<?= htmlspecialchars($searchValue); ?>">
                    </div>
                    <div class="view_maintenance_input">
                        <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                        <div class="view_Maintenance_btn">
                            <input type="date" id="endDate" name="end_date" value="<?= htmlspecialchars($searchValue); ?>">
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
                <form id="pdfForm" action="<?php echo $base_url; ?>/staff-section/generate_report_maintenance.php" method="GET">
                    <?php if (!empty($_GET["userID"])) : ?>
                        <input type="hidden" name="userID" value="<?= htmlspecialchars($_GET["userID"]) ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET["start_date"]) && !empty($_GET["end_date"])) : ?>
                        <input type="hidden" name="start_date" id="start_date" value="<?= htmlspecialchars($_GET["start_date"]) ?>">
                        <input type="hidden" name="end_date" id="end_date" value="<?= htmlspecialchars($_GET["end_date"]) ?>">
                    <?php endif; ?>
                    <button type="submit" class="create_pdf">สร้างรายงาน</button>
                </form>
            </div>
            <!-- ปุ่มสำหรับรีเซ็ตการค้นหาและแสดงข้อมูลทั้งหมด -->
            <form class="form_2" action="<?php echo $base_url; ?>/maintenance/report" method="GET">
                <button type="submit" class="reset_data">แสดงข้อมูลทั้งหมด</button>
            </form>
        </div>
        <div class="maintenanceReport">
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
                            หมายเหตุ :
                            <?php if (($row["note"]) == NULL) : ?>
                                --
                            <?php else : ?>
                                <?php echo htmlspecialchars($row["note"]); ?>
                            <?php endif; ?>
                        </div>
                        <div class="history-item_2">
                            รายละเอียดการบำรุงรักษา :
                            <?php if (($row["details_maintenance"]) == NULL) : ?>
                                --
                            <?php else : ?>
                                <?php echo htmlspecialchars($row["details_maintenance"]); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="history-row no-data">
                    ไม่พบประวัติการบำรุงรักษา
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <?php include_once 'assets/includes/footer_2.php'; ?>
    </footer>
</body>

</html>