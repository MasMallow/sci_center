<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

try {
    // ตรวจสอบการเข้าสู่ระบบของผู้ใช้
    if (!isset($_SESSION['staff_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: /sign_in');
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
        $searchValue = htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8'); // ป้องกัน XSS
        $searchTitle = "ค้นหา \"$searchValue\" | ";
    }

    // สร้างคำสั่ง SQL ตามตัวกรอง userID และช่วงเวลา
    $sql = "SELECT * FROM approve_to_reserve WHERE (situation = 1 OR situation = 3)";
    $params = [];

    // ตรวจสอบและกำหนดค่า name_user
    if (!empty($_GET['name_user'])) {
        $sql .= " AND name_user LIKE :name_user";
        $params[':name_user'] = "%" . htmlspecialchars($_GET['name_user'], ENT_QUOTES, 'UTF-8') . "%"; // ป้องกัน XSS
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
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดที่เกี่ยวข้องกับฐานข้อมูล
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage();
    header('Location: /error_page'); // เปลี่ยนเส้นทางไปยังหน้าแสดงข้อผิดพลาด
    exit;
} catch (Exception $e) {
    // จัดการข้อผิดพลาดทั่วไป
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    header('Location: /error_page'); // เปลี่ยนเส้นทางไปยังหน้าแสดงข้อผิดพลาด
    exit;
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
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/view_report.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <main class="viewReport">
        <nav class="viewReport_header">
            <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <a href="/view_report">รายงาน</a>
            </div>
        </nav>
        <div class="view_report_form">
            <!-- ฟอร์มสำหรับกรองข้อมูล -->
            <form class="form_1" action="<?php echo $base_url; ?>/view_report" method="GET">
                <div class="view_report_column">
                    <div class="view_report_input">
                        <label id="B" for="name_user">ชื่อผู้ใช้</label>
                        <input type="text" id="name_user" name="name_user" placeholder="ชื่อผู้ใช้" value="<?= htmlspecialchars($searchValue); ?>">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="startDate">ช่วงเวลาเริ่มต้น</label>
                        <input type="date" id="startDate" name="start_date" value="<?= htmlspecialchars($searchValue); ?>">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                        <div class="view_report_btn">
                            <input type="date" id="endDate" name="end_date" value="<?= htmlspecialchars($searchValue); ?>">
                            <button type="submit" class="search"><i class="fa-solid fa-magnifying-glass"></i></button>
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
                            <button type="submit" class="create_pdf"><i class="fa-solid fa-file-pdf"></i></button>
                        </form>
                    </div>
                    <!-- ปุ่มสำหรับรีเซ็ตการค้นหาและแสดงข้อมูลทั้งหมด -->
                    <form class="form_2" action="<?php echo $base_url; ?>/report" method="GET">
                        <button type="submit" class="reset_data">แสดงข้อมูลทั้งหมด</button>
                    </form>
                </div>
                <div class="viewReport_table_data">
                    <?php
                    if (count($viewReport) > 0) : ?>
                        <?php foreach ($viewReport as $row) : ?>
                            <div class="viewReport_table_content">
                                <div class="history-item_1"><?php echo htmlspecialchars($row["name_user"]); ?>
                                    ได้ทำการขอใช้ ณ <?php echo thai_date_time_2(htmlspecialchars($row["created_at"])); ?></div>
                                <div class="history-item_2">
                                    <?php
                                    $items = explode(',', $row['list_name']);
                                    foreach ($items as $item) {
                                        $item_parts = explode('(', $item);
                                        $product_name = trim($item_parts[0]);
                                        $quantity = str_replace(')', '', $item_parts[1]);
                                        echo $product_name . ' <span id="B"> ' . $quantity . ' </span> รายการ<br>';
                                    }
                                    ?>
                                </div>
                                <div class="history-item_2"><span id="B">ขอใช้</span><?php echo thai_date_time_2($row["created_at"]); ?>
                                    <span id="B">ถึง</span><?php echo thai_date_time_2($row["reservation_date"]); ?>
                                </div>
                                <div class="history-item_2 reportStatus">
                                    <div class="usageStatus">
                                        <i class="fa <?php echo ($row["Usage_item"] == 0 || $row["Usage_item"] == NULL) ? 'fa-hourglass-start' : 'fa-check-circle'; ?>"></i>
                                        <?php
                                        if ($row["Usage_item"] == 0 || $row["Usage_item"] == NULL) {
                                            echo 'ยังไม่ได้เริ่มกระบวนการใช้งาน';
                                        } else {
                                            echo 'เริ่มกระบวนการใช้งานแล้ว';
                                        }
                                        ?>
                                    </div>
                                    <div class="returnStatus">
                                        <i class="fa <?php echo ($row["date_return"] == NULL) ? 'fa-times-circle' : 'fa-calendar-check'; ?>"></i>
                                        <?php
                                        if ($row["date_return"] == NULL) {
                                            echo 'ยังไม่ได้ทำการคืนอุปกรณ์ หรือเครื่องมือ';
                                        } else {
                                            echo thai_date_time_2($row["date_return"]);
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="row">
                            <div colspan='5' class="date_not_found"><span id="B">ไม่พบข้อมูล</span></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- ------------- FOOTER --------------- -->
    <footer>
        <?php include 'assets/includes/footer_2.php'; ?>
    </footer>
</body>

</html>