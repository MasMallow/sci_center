<?php
// เริ่ม session
session_start();
// รวมการเชื่อมต่อฐานข้อมูล (ถ้ายังไม่ได้รวม)
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

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
// ถ้าไม่มีการเข้าสู่ระบบ ให้กลับไปที่หน้าเข้าสู่ระบบ
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

$searchTitle = "";
$searchValue = "";
$result = [];

// ตรวจสอบการค้นหา
if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

$action = $_GET['action'] ?? 'top_10';

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
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/top10list.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="header_management">
        <div class="header_management_section">
            <div class="header_name_section">
                <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">จัดการระบบ</span>
            </div>
            <!-- <div class="header_num_section">
                <span id="B">
                    <?php
                    if ($action === 'all') {
                        echo 'วัสดุ อุปกรณ์ และเครื่องมือทั้งหมด';
                    } elseif ($action === 'material') {
                        echo 'วัสดุทั้งหมด';
                    } elseif ($action === 'equipment') {
                        echo 'อุปกรณ์ทั้งหมด';
                    } elseif ($action === 'tools') {
                        echo 'เครื่องมือทั้งหมด';
                    }
                    echo " $nums รายการ";
                    ?>
                </span>
            </div> -->
        </div>
        <div class="management_section_btn">
            <form class="btn_management_all" method="get">
                <button type="submit" class="<?= ($action === 'top_10') ? 'active' : ''; ?> btn_management_01" name="action" value="top_10">ทั้งหมด</button>
                <button type="submit" class="<?= ($action === 'top_10_material') ? 'active' : ''; ?> btn_management_02" name="action" value="top_10_material">วัสดุ</button>
                <button type="submit" class="<?= ($action === 'top_10_equipment') ? 'active' : ''; ?> btn_management_02" name="action" value="top_10_equipment">อุปกรณ์</button>
                <button type="submit" class="<?= ($action === 'top_10_tools') ? 'active' : ''; ?> btn_management_03" name="action" value="top_10_tools">เครื่องมือ</button>
            </form>
        </div>
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
                            echo "<li>{$row['sci_name']} - ใช้งาน {$row['usage_count']} ครั้ง</li>";
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
                            echo "<li>{$row['sci_name']} - ใช้งาน {$row['usage_count']} ครั้ง</li>";
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
                            echo "<li>{$row['sci_name']} - ใช้งาน {$row['usage_count']} ครั้ง</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
</body>

</html>