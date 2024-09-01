<?php
session_start();
require_once 'assets/config/Database.php';
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
    <title>สถิติ 10 รายการ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/view_report.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="viewReport">
        <nav class="viewReport_header">
            <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <a href="/view_top10">รายงาน Top 10</a>
            </div>
        </nav>
        <?php if ($request_uri == '/top10') : ?>
            <div id="loading">
                <div class="spinner"></div>
                <p>กำลังโหลดข้อมูล...</p>
            </div>
            <div id="content" style="display: none;">
                <div class="top_10_list">
                    <div class="top_10_list_content">
                        <div class="top_10_list_header">
                            <span id="B">Top 10 วัสดุ</span>
                        </div>
                        <div class="top_10_list_body">
                            <ul>
                                <?php
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
                                foreach (array_slice($toolResult, 0, 10) as $row) {
                                    echo "<li><div class='content'><span class='sciName'>{$row['sci_name']}</span> - <span class='topten' id=\"B\">ใช้งาน {$row['usage_count']} ครั้ง</span></div></li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </main>
    <footer>
        <?php include_once 'assets/includes/footer_2.php'; ?>
    </footer>

    <!-- JavaScript -->
    <script src="<?= $base_url; ?>/assets/js/ajax.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                // ซ่อนการโหลดข้อมูล
                document.getElementById('loading').style.display = 'none';
                // แสดงเนื้อหาหลัก
                document.getElementById('content').style.display = 'block';

                // ทำให้ grid items แสดงผลทีละรายการด้วยแอนิเมชัน
                const gridItems = document.querySelectorAll('.top_10_list_content');

                gridItems.forEach((item, index) => {
                    const delay = index * 150;
                    setTimeout(() => {
                        item.classList.add('show');
                    }, delay);
                });
            }, 1500);
        });
    </script>
</body>

</html>