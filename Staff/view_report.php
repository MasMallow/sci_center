<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
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

// สร้างคำสั่ง SQL ตามตัวกรอง userID และช่วงเวลา
$sql = "SELECT * FROM approve_to_reserve WHERE situation = 1  OR situation = 3";

// สร้างอาร์เรย์เพื่อเก็บพารามิเตอร์
$params = [];

// ตรวจสอบและกำหนดค่า userID
if (isset($_GET['userID']) && $_GET['userID'] !== '') {
    $userID = $_GET['userID'];
    $sql .= " AND userID LIKE :userID";
    $params[':userID'] = "%" . $userID . "%";
}

// ตรวจสอบและกำหนดค่า start_date และ end_date
if (isset($_GET['start_date']) && $_GET['start_date'] !== '' && isset($_GET['end_date']) && $_GET['end_date'] !== '') {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $sql .= " AND (reservation_date BETWEEN :start_date AND :end_date)";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
}

// เตรียมและดำเนินการคำสั่ง SQL
$stmt = $conn->prepare($sql);

// Bind พารามิเตอร์
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการจอง</title>
    <!-- ลิงก์ไปยังไฟล์ CSS -->
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/view_report.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <div class="header_view_report">
        <div class="header_view_report_section">
            <a href="<?php echo $base_url; ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายงานการยืมอุปกรณ์</span>
        </div>
    </div>
    <div class="view_report">
        <div class="view_report_form">
            <form class="form_1" action="view_report_booking" method="GET">
                <div class="view_report_column">
                    <div class="view_report_input">
                        <label id="B" for="userID">UID</label>
                        <input type="text" id="userID" name="userID" placeholder="กรอไอดีผู้ใช้">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="startDate">ช่วงเวลาเริ่มต้น</label>
                        <input type="date" id="startDate" name="start_date">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                        <div class="view_report_btn">
                            <input type="date" id="endDate" name="end_date">
                            <button type="submit" class="search">ค้นหา</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="view_report_table">
            <div class="view_report_table_header">
                <div class="view_report_table_header_pdf">
                    <span id="B">
                        ประวัติการขอใช้
                    </span>
                    <!-- ปุ่มสำหรับสร้างรายงาน PDF -->
                    <form id="pdfForm" action="generate_pdf_bookings" method="GET">
                        <?php if (isset($_GET["userID"]) && $_GET["userID"] != "") : ?>
                            <input type="hidden" name="userID" value="<?= htmlspecialchars($_GET["userID"]) ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET["start_date"]) && $_GET["start_date"] != "" && isset($_GET["end_date"]) && $_GET["end_date"] != "") : ?>
                            <input type="hidden" name="start_date" id="start_date" value="<?= htmlspecialchars($_GET["start_date"]) ?>">
                            <input type="hidden" name="end_date" id="end_date" value="<?= htmlspecialchars($_GET["end_date"]) ?>">
                        <?php endif; ?>
                        <button type="submit" class="create_pdf">สร้างรายงาน</button>
                    </form>
                </div>
                <form class="form_2" action="view_report_booking" method="GET">
                    <button type="submit" class="reset_data">แสดงข้อมูลทั้งหมด</button>
                </form>
            </div>
            <table class="view_report_table_data">
                <thead>
                    <tr>
                        <th class="UID" id="B">รหัสผู้ใช้</th>
                        <th class="name" id="B">ชื่อ - นามสกุล</th>
                        <th class="list" id="B">รายการการขอใช้</th>
                        <th class="start_date" id="B">วันเวลาที่ขอใช้</th>
                        <th class="end_date" id="B">วันเวลาสิ้นสุดการขอใช้</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // แสดงผลลัพธ์ที่ดึงมาได้
                    if (count($data) > 0) {
                        foreach ($data as $row) { ?>
                            <tr>
                                <td class="UID"><?php echo htmlspecialchars($row["userID"]); ?></td>
                                <td><?php echo htmlspecialchars($row["name_user"]); ?></td>
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
</body>

</html>