<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลเมื่อเข้าสู่ระบบแล้ว
$userID = $_SESSION['staff_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$searchTitle = "";
$searchValue = "";
$result = [];

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

// สร้างคำสั่ง SQL ตามตัวกรอง userID และช่วงเวลา
$sql = "SELECT * FROM approve_to_reserve WHERE (situation = 1 OR situation = 3)";
$params = [];

// ตรวจสอบและกำหนดค่า userID
if (!empty($_GET['userID'])) {
    $sql .= " AND userID LIKE :userID";
    $params[':userID'] = "%" . $_GET['userID'] . "%";
}

// ตรวจสอบและกำหนดค่า start_date และ end_date
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $sql .= " AND reservation_date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $_GET['start_date'];
    $params[':end_date'] = $_GET['end_date'];
}

// เตรียมและดำเนินการคำสั่ง SQL
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$viewReport = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    // คิวรีสำหรับวัสดุ อุปกรณ์ และเครื่องมือ พร้อมนับจำนวนการใช้งาน
    $materialQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'วัสดุ' GROUP BY sci_name ORDER BY usage_count DESC";
    $equipmentQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'อุปกรณ์' GROUP BY sci_name ORDER BY usage_count DESC";
    $toolQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'เครื่องมือ' GROUP BY sci_name ORDER BY usage_count DESC";

    // เตรียมและดำเนินการคิวรีสำหรับวัสดุ
    $materialStmt = $conn->prepare($materialQuery);
    $materialStmt->execute();
    $materialResult = $materialStmt->fetchAll(PDO::FETCH_ASSOC);

    // เตรียมและดำเนินการคิวรีสำหรับอุปกรณ์
    $equipmentStmt = $conn->prepare($equipmentQuery);
    $equipmentStmt->execute();
    $equipmentResult = $equipmentStmt->fetchAll(PDO::FETCH_ASSOC);

    // เตรียมและดำเนินการคิวรีสำหรับเครื่องมือ
    $toolStmt = $conn->prepare($toolQuery);
    $toolStmt->execute();
    $toolResult = $toolStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดในการคิวรีฐานข้อมูล
    echo "Query failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงาน และ TOP 10</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/view_report.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <div class="viewReport">
        <div class="viewReport_header">
            <a href="<?php echo $base_url; ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายงาน และ TOP 10</span>
        </div>
        <div class="viewReport_BTN">
            <a href="/view_report" class="<?= ($request_uri == '/view_report') ? 'active' : ''; ?> btn_viewReport_01">
                รายการการขอใช้</a>
            <a href="/view_report/top_10" class="<?= ($request_uri == '/view_report/top_10') ? 'active' : ''; ?> btn_viewReport_02">
                TOP 10</a>
        </div>
        <?php if ($request_uri == '/view_report') : ?>
            <div class="view_report_form">
                <!-- ฟอร์มสำหรับกรองข้อมูล -->
                <form class="form_1" action="<?php echo $base_url; ?>/view_report" method="GET">
                    <div class="view_report_column">
                        <div class="view_report_input">
                            <label id="B" for="userID">UID</label>
                            <input type="text" id="userID" name="userID" placeholder="กรอไอดีผู้ใช้" value="<?= htmlspecialchars($searchValue); ?>">
                        </div>
                        <div class="view_report_input">
                            <label id="B" for="startDate">ช่วงเวลาเริ่มต้น</label>
                            <input type="date" id="startDate" name="start_date" value="<?= htmlspecialchars($searchValue); ?>">
                        </div>
                        <div class="view_report_input">
                            <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                            <div class="view_report_btn">
                                <input type="date" id="endDate" name="end_date" value="<?= htmlspecialchars($searchValue); ?>">
                                <button type="submit" class="search">ค้นหา</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="view_report_table">
                    <!-- ส่วนของการสร้าง PDF -->
                    <div class="view_report_table_header">
                        <div class="view_report_table_header_pdf">
                            <span id="B">ประวัติการขอใช้</span>
                            <form id="pdfForm" action="<?php echo $base_url; ?>/view_report/generate_pdf" method="GET">
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
                        <form class="form_2" action="<?php echo $base_url; ?>/view_report" method="GET">
                            <button type="submit" class="reset_data">แสดงข้อมูลทั้งหมด</button>
                        </form>
                    </div>
                    <!-- ตารางแสดงข้อมูล -->
                    <table class="viewReport_table_data">
                        <thead>
                            <tr>
                                <th class="name" id="B">ชื่อ - นามสกุล</th>
                                <th class="list" id="B">รายการการขอใช้</th>
                                <th class="start_date" id="B">วันเวลาที่ขอใช้</th>
                                <th class="end_date" id="B">วันเวลาสิ้นสุดการขอใช้</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($viewReport) > 0) {
                                foreach ($viewReport as $row) { ?>
                                    <tr>
                                        <td class="name"><?php echo htmlspecialchars($row["name_user"]); ?></td>
                                        <td>
                                            <?php
                                            $items = explode(',', $row['list_name']);
                                            foreach ($items as $item) {
                                                $item_parts = explode('(', $item);
                                                $product_name = trim($item_parts[0]);
                                                $quantity = str_replace(')', '', $item_parts[1]);
                                                echo $product_name . ' <span id="B"> ' . $quantity . ' </span> รายการ<br>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo thai_date_time($row["created_at"]); ?></td>
                                        <td><?php echo thai_date_time($row["reservation_date"]); ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan='5' class="date_not_found"><span id="B">ไม่พบข้อมูล</span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else : ?>
            <div class="top_10_list">
                <div class="top_10_list_content">
                    <div class="top_10_list_header">
                        <span id="B">Top 10 วัสดุ</span>
                    </div>
                    <div class="top_10_list_body">
                        <ul>
                            <?php
                            // แสดงผลวัสดุ 10 อันดับแรก
                            foreach (array_slice($materialResult, 0, 10) as $row) {
                                echo "<li><div class='content'><span class='sciName'>{$row['sci_name']}</span> - <span class='topten' id=\"B\">ใช้งาน {$row['usage_count']} ครั้ง</span></div></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="top_10_list_content">
                    <div class="top_10_list_header">
                        <span id="B">Top 10 อุปกรณ์</span>
                    </div>
                    <div class="top_10_list_body">
                        <ul>
                            <?php
                            // แสดงผลอุปกรณ์ 10 อันดับแรก
                            foreach (array_slice($equipmentResult, 0, 10) as $row) {
                                echo "<li><div class='content'><span class='sciName'>{$row['sci_name']}</span> - <span class='topten' id=\"B\">ใช้งาน {$row['usage_count']} ครั้ง</span></div></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="top_10_list_content">
                    <div class="top_10_list_header">
                        <span id="B">Top 10 เครื่องมือ</span>
                    </div>
                    <div class="top_10_list_body">
                        <ul>
                            <?php
                            // แสดงผลเครื่องมือ 10 อันดับแรก
                            foreach (array_slice($toolResult, 0, 10) as $row) {
                                echo "<li><div class='content'><span class='sciName'>{$row['sci_name']}</span> - <span class='topten' id=\"B\">ใช้งาน {$row['usage_count']} ครั้ง</span></div></li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </div>
</body>

</html>