<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';
include_once 'models/UserCheck.php';
//----------------------------------------------------------
try {
    $searchTitle = "";
    $searchValue = "";
    $results = [];
    $results_per_page = 20; // เปลี่ยนค่าตามความต้องการ

    // รับค่าหน้าปัจจุบัน
    $page = intval($_GET['page'] ?? 1);

    // รับค่าการค้นหาถ้ามี
    if (!empty($_GET['search'])) {
        $searchValue = htmlspecialchars($_GET['search']);
        $searchTitle = "ค้นหา \"$searchValue\" | ";
        $searchQuery = "%" . $searchValue . "%";

        // เก็บผลการค้นหาไว้ใน session
        $_SESSION['search_results'] = $searchQuery;
        $_SESSION['search_value'] = $searchValue;
    } else {
        // ใช้ผลการค้นหาจาก session ถ้ามี
        $searchQuery = $_SESSION['search_results'] ?? null;
        $searchValue = $_SESSION['search_value'] ?? null;
    }

    // คำนวณ offset สำหรับคำสั่ง SQL LIMIT
    $offset = ($page - 1) * $results_per_page;

    // ตรวจสอบ URI ปัจจุบันเพื่อกำหนด category
    $request_uri = $_SERVER['REQUEST_URI'];

    // คำสั่ง SQL เพื่อดึงข้อมูล
    $query = "SELECT * FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number WHERE 1=1";

    // เพิ่มเงื่อนไข categories
    if (strpos($request_uri, '/material') !== false) {
        $query .= " AND crud.categories = 'วัสดุ'";
    } elseif (strpos($request_uri, '/equipment') !== false) {
        $query .= " AND crud.categories = 'อุปกรณ์'";
    } elseif (strpos($request_uri, '/tools') !== false) {
        $query .= " AND crud.categories = 'เครื่องมือ'";
    }

    // เพิ่มเงื่อนไขการค้นหา
    if ($searchQuery) {
        $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
    }

    $query .= " ORDER BY RAND() ASC LIMIT :offset, :results_per_page";

    // ดึงข้อมูลจากฐานข้อมูล
    $stmt = $conn->prepare($query);

    // bind parameter การค้นหา
    if ($searchQuery) {
        $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
    }

    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':results_per_page', $results_per_page, PDO::PARAM_INT);

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // นับจำนวนรายการทั้งหมด
    $total_records_query = "SELECT COUNT(*) AS total FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number WHERE 1=1";

    // เพิ่มเงื่อนไข categories สำหรับนับจำนวน
    if (strpos($request_uri, '/material') !== false) {
        $total_records_query .= " AND crud.categories = 'วัสดุ'";
    } elseif (strpos($request_uri, '/equipment') !== false) {
        $total_records_query .= " AND crud.categories = 'อุปกรณ์'";
    } elseif (strpos($request_uri, '/tools') !== false) {
        $total_records_query .= " AND crud.categories = 'เครื่องมือ'";
    }

    // เพิ่มเงื่อนไขการค้นหาสำหรับนับจำนวน
    if ($searchQuery) {
        $total_records_query .= " AND (crud.sci_name LIKE :search_count OR crud.serial_number LIKE :search_count)";
    }

    $stmt_count = $conn->prepare($total_records_query);

    // bind parameter การค้นหาสำหรับนับจำนวน
    if ($searchQuery) {
        $stmt_count->bindParam(':search_count', $searchQuery, PDO::PARAM_STR);
    }

    $stmt_count->execute();
    $total_records = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

    // ถ้าไม่มีข้อมูลมากกว่าหรือเท่ากับ จำนวนที่กำหนดต่อหน้า ก็ไม่ต้องแสดง pagination
    $pagination_display = $total_records > $results_per_page;

    // ลบตัวแปร search_results และ search_value ใน session
    unset($_SESSION['search_results'], $_SESSION['search_value']);

    // สำหรับ Notification
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        $firstname = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE name_user = :firstname ORDER BY created_at DESC LIMIT 10");
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/nofitication.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <!-- -------------- HEADER -------------- -->
    <header>
        <?php include_once('assets/includes/navigator.php'); ?>
    </header>
    <!-- ตรวจสอบสิทธิ์ของผู้ใช้งาน -->
    <?php if (isset($userData['urole']) && ($userData['urole'] == 'user') || empty($userData)) : ?>
        <main class="content">
            <div class="content_FLEX">
                <!-- ------------------ SIDEBAR ------------------ -->
                <sidebar class="menu_navigator">
                    <ul class="sb_ul">
                        <?php
                        // ตรวจสอบ URL ของหน้าเว็บปัจจุบัน
                        $request_uri = $_SERVER['REQUEST_URI'];
                        $base_uri = '/';

                        // ตรวจสอบค่าของ $request_uri และพารามิเตอร์ GET
                        $is_material = strpos($request_uri, '/material') !== false;
                        $is_equipment = strpos($request_uri, '/equipment') !== false;
                        $is_tools = strpos($request_uri, '/tools') !== false;
                        $is_notification = $request_uri === '/notification';

                        // ตรวจสอบเงื่อนไขที่ต้องการ
                        $active_home = ($request_uri === $base_uri || !$is_material && !$is_equipment && !$is_tools && !$is_notification);
                        $active_material = $is_material;
                        $active_equipment = $is_equipment;
                        $active_tools = $is_tools;
                        ?>

                        <li class="group_li">
                            <a class="link <?= $active_home ? 'active' : ''; ?>" href="<?= $base_url; ?>">
                                <i class="icon fa-solid fa-house"></i>
                                <span class="text">หน้าหลัก</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">ประเภท</span>
                            <a class="group_li_01 <?= $active_material ? 'active' : ''; ?>" href="/material<?= !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>">
                                <span class="text">ประเภทวัสดุ</span>
                            </a>
                            <a class="group_li_02 <?= $active_equipment ? 'active' : ''; ?>" href="/equipment<?= !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>">
                                <span class="text">ประเภทอุปกรณ์</span>
                            </a>
                            <a class="group_li_03 <?= $active_tools ? 'active' : ''; ?>" href="/tools<?= !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>">
                                <span class="text">ประเภทเครื่องมือ</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">การขอใช้งาน</span>
                            <a class="group_li_01" href="<?= $base_url; ?>/UsedStart">
                                <i class="fa-solid fa-hourglass-start"></i>
                                <span class="text">เริ่มต้นการใช้งาน</span>
                            </a>
                            <a class="group_li_01" href="<?= $base_url; ?>/UsedEnd">
                                <i class="fa-solid fa-hourglass-end"></i>
                                <span class="text">สิ้นสุดการใช้งาน</span>
                            </a>
                            <a class="group_li_02" href="<?= $base_url; ?>/calendar">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span class="text">ตรวจสอบการขอใช้งาน</span>
                            </a>
                            <a class="group_li_03" href="<?= $base_url; ?>/list-request">
                                <i class="fa-solid fa-list"></i>
                                <span class="text">รายการการขอใช้</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">แจ้งเตือน</span>
                            <a class="group_li_01 <?= $is_notification ? 'active' : ''; ?>" href="<?= $base_url; ?>/notification">
                                <i class="fa-solid fa-envelope"></i>
                                <span class="text">แจ้งเตือน</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">รายการที่ขอใช้</span>
                            <a class="group_li_01" href="<?= $base_url; ?>/cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span class="text">รายการที่ขอใช้</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">QR-CODE</span>
                            <a class="group_li_01" href="<?= $base_url; ?>/qrcode">
                                <i class="fa-solid fa-qrcode"></i>
                                <span class="text">QR CODE</span>
                            </a>
                        </li>
                    </ul>
                </sidebar>
                <!-- ------------------ MAIN CONTENT ------------------ -->
                <?php
                // ตรวจสอบว่า URL เป็นหน้า notification หรือไม่
                if (strpos($request_uri, '/notification') !== false) {
                    include('notification.php');
                } elseif (
                    strpos($request_uri, '/') !== false ||
                    strpos($request_uri, '/material') !== false ||
                    strpos($request_uri, '/equipment') !== false ||
                    strpos($request_uri, '/tools') !== false
                ) {
                    include('Data.php');
                }
                ?>
            </div>
        </main>
    <?php endif; ?>
    <?php if (isset($userData['urole']) && $userData['urole'] == 'staff') : ?>
        <?php include('staff-section/homeStaff.php'); ?>
    <?php endif; ?>

    <!-- ---------------- FOOTER ------------------ -->
    <footer><?php include "assets/includes/footer.php"; ?></footer>

    <!-- JavaScript -->
    <script src="<?= $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?= $base_url; ?>/assets/js/loading.js"></script>
    <script src="<?= $base_url; ?>/assets/js/datetime.js"></script>
</body>


</html>