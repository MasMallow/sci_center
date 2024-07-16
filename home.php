<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

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

try {
    $searchTitle = "";
    $searchValue = "";
    $result = [];

    if (!isset($_GET['page'])) {
        $page = 1;
    }

    // ตรวจสอบและกำหนดค่าการค้นหาและหน้าปัจจุบัน
    if (!empty($_GET['search'])) {
        $searchValue = htmlspecialchars($_GET['search']);
        $searchTitle = "ค้นหา \"$searchValue\" | ";
        $searchQuery = "%" . $_GET["search"] . "%";
        $page = intval($_GET['page'] ?? 1);

        // เก็บผลการค้นหาไว้ใน session
        $_SESSION['search_results'] = $searchQuery;
        $_SESSION['search_value'] = $searchValue;
    } else {
        // ใช้ผลการค้นหาจาก session ถ้ามี
        $searchQuery = $_SESSION['search_results'] ?? null;
        $searchValue = $_SESSION['search_value'] ?? null;
        $page = intval($_GET['page'] ?? 1);
    }

    $results_per_page = 20; // เปลี่ยนค่าตามความต้องการ

    // คำนวณ offset สำหรับคำสั่ง SQL LIMIT
    $offset = ($page - 1) * $results_per_page;

    // ตรวจสอบ URI ปัจจุบันเพื่อกำหนด category
    $request_uri = $_SERVER['REQUEST_URI'];

    // คำสั่ง SQL เพื่อดึงข้อมูล
    $query = "SELECT * FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

    // เพิ่มเงื่อนไข categories
    if (strpos($request_uri, '/material') !== false) {
        $query .= " WHERE crud.categories = 'วัสดุ'";
    } elseif (strpos($request_uri, '/equipment') !== false) {
        $query .= " WHERE crud.categories = 'อุปกรณ์'";
    } elseif (strpos($request_uri, '/tools') !== false) {
        $query .= " WHERE crud.categories = 'เครื่องมือ'";
    } else {
        $query .= " WHERE 1=1"; // เพิ่มเงื่อนไขให้เป็นจริงเสมอเพื่อให้สามารถเพิ่ม AND ต่อไปได้
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
    $total_records_query = "SELECT COUNT(*) AS total FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

    // เพิ่มเงื่อนไข categories สำหรับนับจำนวน
    if (strpos($request_uri, '/material') !== false) {
        $total_records_query .= " WHERE crud.categories = 'วัสดุ'";
    } elseif (strpos($request_uri, '/equipment') !== false) {
        $total_records_query .= " WHERE crud.categories = 'อุปกรณ์'";
    } elseif (strpos($request_uri, '/tools') !== false) {
        $total_records_query .= " WHERE crud.categories = 'เครื่องมือ'";
    } else {
        $total_records_query .= " WHERE 1=1";
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
    if ($total_records <= $results_per_page) {
        $pagination_display = false;
    } else {
        $pagination_display = true;
    }


    // ลบตัวแปร search_results
    unset($_SESSION['search_results']);
    // ลบค่าใน session ที่ชื่อ search_value
    unset($_SESSION['search_value']);
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

// สำหรับ Notification

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/nofitication.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once('assets/includes/navigator.php'); ?>
    </header>

    <!-- ตรวจสอบสิทธิ์ของผู้ใช้งาน -->
    <?php if (isset($userData['urole']) && $userData['urole'] == 'user' || empty($userData['urole'])) : ?>
        <main class="content">
            <div class="content_FLEX">
                <!-- ------------------ SIDEBAR ------------------ -->
                <sidebar class="menu_navigator">
                    <ul class="sb_ul">
                        <li>
                            <a class="link <?= ($request_uri == '/') ? 'active' : ''; ?>" href="<?= $base_url; ?>">
                                <i class="icon fa-solid fa-house"></i>
                                <span class="text">หน้าหลัก</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">ประเภท</span>
                            <a class="group_li_01 <?= ($request_uri == '/material') ? 'active' : ''; ?>" href="/material">
                                <span class="text">ประเภทวัสดุ</span>
                            </a>
                            <a class="group_li_02 <?= ($request_uri == '/equipment') ? 'active' : ''; ?>" href="/equipment">
                                <span class="text">ประเภทอุปกรณ์</span>
                            </a>
                            <a class="group_li_03 <?= ($request_uri == '/tools') ? 'active' : ''; ?>" href="/tools">
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
                            <a class="group_li_02" href="<?= $base_url; ?>/CheckReserve">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span class="text">ตรวจสอบการขอใช้งาน</span>
                            </a>
                            <a class="group_li_03" href="<?= $base_url; ?>/TrackingReserve">
                                <i class="fa-solid fa-list"></i>
                                <span class="text">ติดตามการขอใช้งาน</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">แจ้งเตือน</span>
                            <a class="group_li_01 <?= ($request_uri == '/notification') ? 'active' : ''; ?>" " href=" <?= $base_url; ?>/notification">
                                <i class="fa-solid fa-envelope"></i>
                                <span class="text">แจ้งเตือน</span>
                            </a>
                        </li>
                        <li class="group_li">
                            <span class="group_title">รายการที่ขอใช้</span>
                            <a class="group_li_01" href="<?= $base_url; ?>/Cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span class="text">รายการที่ขอใช้</span>
                            </a>
                        </li>
                    </ul>
                </sidebar>
                <!-- ------------------ MAIN CONTENT ------------------ -->
                <?php if ($request_uri == '/' || $request_uri == '/material' || $request_uri == '/equipment' || $request_uri == '/tools') : ?>
                    <div class="content_area">
                        <!-- ----------------- SEARCH SECTION ----------------- -->
                        <div class="content_area_header">
                            <form class="contentSearch" method="get">
                                <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
                                <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                                <button class="search_btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </form>
                            <div class="content_area_nav">
                                <div class="date" id="date"></div>
                                <div class="time" id="time"></div>
                            </div>
                        </div>
                        <!-- ----------------- CONTENT ------------------ -->
                        <div class="content_area_all">
                            <?php if (empty($results)) : ?>
                                <div class="grid_content_not_found">
                                    <span id="B">ไม่พบข้อมูลที่ค้นหา</span>
                                </div>
                            <?php else : ?>
                                <div class="content_area_grid">
                                    <?php foreach ($results as $data) : ?>
                                        <div class="grid_content">
                                            <div class="grid_content_header">
                                                <div class="content_img">
                                                    <img src="<?= htmlspecialchars($base_url); ?>/assets/uploads/<?= htmlspecialchars($data['img_name']) ?>" alt="Image">
                                                </div>
                                            </div>
                                            <div class="content_status_details">
                                                <?php if ($data['availability'] == 0) : ?>
                                                    <div class="ready-to-use">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                        <span id="B">พร้อมใช้งาน</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="moderately">
                                                        <i class="fa-solid fa-ban"></i>
                                                        <span id="B">บำรุงรักษา</span>
                                                    </div>
                                                <?php endif ?>
                                                <div class="content_details">
                                                    <a href="/details/<?= htmlspecialchars($data['ID']) ?>" class="details_btn">
                                                        <i class="fa-solid fa-circle-info"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="grid_content_body">
                                                <div class="content_name">
                                                    <?= htmlspecialchars($data['sci_name']) ?> (<?= htmlspecialchars($data['serial_number']) ?>)
                                                </div>
                                                <div class="content_categories">
                                                    <span id="B">ประเภท : </span><?= htmlspecialchars($data['categories']) ?>
                                                </div>
                                                <div class="content_amount">
                                                    <span id="B">คงเหลือ : </span><?= htmlspecialchars($data['amount']) ?>
                                                </div>
                                            </div>
                                            <div class="grid_content_footer">
                                                <div class="content_btn">
                                                    <?php if ($data['amount'] >= 1 && $data['availability'] == 0) : ?>
                                                        <a href="Cart?action=add&item=<?= htmlspecialchars($data['sci_name']) ?>" class="used_it">
                                                            <i class="fa-solid fa-address-book"></i>
                                                            <span>ขอใช้</span>
                                                        </a>
                                                    <?php else : ?>
                                                        <div class="not_available">
                                                            <i class="fa-solid fa-ban"></i>
                                                            <span>ไม่พร้อมใช้งาน</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- PAGINATION PAGE -->
                        <?php if ($pagination_display) : ?>
                            <div class="pagination">
                                <?php if ($page > 1) : ?>
                                    <a href="?page=1<?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&laquo;</a>
                                    <a href="?page=<?= $page - 1; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&lsaquo;</a>
                                <?php endif; ?>

                                <?php
                                $total_pages = ceil($total_records / $results_per_page);
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    if ($i == $page) {
                                        echo "<a class='active'>$i</a>";
                                    } else {
                                        echo "<a href='?page=$i" . ($searchValue ? '&search=' . htmlspecialchars($searchValue) : '') . "'>$i</a>";
                                    }
                                }
                                ?>

                                <?php if ($page < $total_pages) : ?>
                                    <a href="?page=<?= $page + 1; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&rsaquo;</a>
                                    <a href="?page=<?= $total_pages; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($request_uri == '/notification') : ?>
                    <?php include('notification.php'); ?>
                <?php endif; ?>
            </div>
        </main>

        <!-- ---------------- FOOTER ------------------ -->
        <footer><?php include "assets/includes/footer.php" ?></footer>

    <?php elseif (isset($userData['urole']) && $userData['urole'] == 'staff') : ?>
        <?php include('staff-section/homeStaff.php'); ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="<?= $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?= $base_url; ?>/assets/js/details.js"></script>
    <script src="<?= $base_url; ?>/assets/js/datetime.js"></script>
</body>


</html>