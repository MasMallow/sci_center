<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

// ดึงข้อมูลผู้ใช้เพียงครั้งเดียว (เพิ่มตรวจสอบค่า session)
if (isset($_SESSION['staff_login']) && !empty($_SESSION['staff_login'])) {
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

$results_per_page = 48; // เปลี่ยนค่าตามความต้องการ

// คำนวณ offset สำหรับคำสั่ง SQL LIMIT
$offset = ($page - 1) * $results_per_page;

// ตรวจสอบ URI ปัจจุบันเพื่อกำหนด category
$request_uri = $_SERVER['REQUEST_URI'];

// คำสั่ง SQL เพื่อดึงข้อมูล
$query = "SELECT * FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

// เพิ่มเงื่อนไข categories
if (strpos($request_uri, '/management/material') !== false) {
    $query .= " WHERE crud.categories = 'วัสดุ'";
} elseif (strpos($request_uri, '/management/equipment') !== false) {
    $query .= " WHERE crud.categories = 'อุปกรณ์'";
} elseif (strpos($request_uri, '/management/tools') !== false) {
    $query .= " WHERE crud.categories = 'เครื่องมือ'";
} else {
    $query .= " WHERE 1=1"; // เพิ่มเงื่อนไขให้เป็นจริงเสมอเพื่อให้สามารถเพิ่ม AND ต่อไปได้
}

// เพิ่มเงื่อนไขการค้นหา
if ($searchQuery) {
    $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
}

$query .= " ORDER BY crud.ID ASC LIMIT :offset, :results_per_page";

// ดึงข้อมูลจากฐานข้อมูล
$stmt = $conn->prepare($query);

// bind parameter การค้นหา
if ($searchQuery) {
    $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':results_per_page', $results_per_page, PDO::PARAM_INT);

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// นับจำนวนรายการทั้งหมด
$total_records_query = "SELECT COUNT(*) AS total FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

// เพิ่มเงื่อนไข categories สำหรับนับจำนวน
if (strpos($request_uri, '/management/material') !== false) {
    $total_records_query .= " WHERE crud.categories = 'วัสดุ'";
} elseif (strpos($request_uri, '/management/equipment') !== false) {
    $total_records_query .= " WHERE crud.categories = 'อุปกรณ์'";
} elseif (strpos($request_uri, '/management/tools') !== false) {
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
unset($search_results);
// ลบค่าใน session ที่ชื่อ search_value
unset($_SESSION['search_value']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $searchTitle; ?>จัดการวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
</head>

<body>
    <header>
        <?php include_once('assets/includes/navigator.php'); ?>
    </header>
    <div class="Dashboard_Management">
        <?php if (isset($_SESSION['updateData_success'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-check check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['updateData_success']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['updateData_success']); ?>
        <?php endif ?>
        <?php if (isset($_SESSION['updateData_error'])) : ?>
            <div class="toast error">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check error"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['updateData_error']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress error"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['updateData_error']); ?>
        <?php endif ?>
        <?php if (isset($_SESSION['delete_success'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-check check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['delete_success']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['delete_success']); ?>
        <?php endif ?>
        <div class="header_management_section">
            <div class="header_name_section">
                <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
                <div class="breadcrumb">
                    <a href="/">หน้าหลัก</a>
                    <span>&gt;</span>
                    <?php
                    if ($request_uri == '/management') {
                        echo '<a href="/management">การจัดการระบบ</a>';
                    }
                    if ($request_uri == '/management/material') {
                        echo '<a href="/management">การจัดการระบบ</a>';
                        echo '<span>&gt;</span>';
                        echo '<a href="/management/material">วัสดุ</a>';
                    }
                    if ($request_uri == '/management/equipment') {
                        echo '<a href="/management">การจัดการระบบ</a>';
                        echo '<span>&gt;</span>';
                        echo '<a href="/management/equipment">อุปกรณ์</a>';
                    }
                    if ($request_uri == '/management/tools') {
                        echo '<a href="/management">การจัดการระบบ</a>';
                        echo '<span>&gt;</span>';
                        echo '<a href="/management/tools">เครื่องมือ</a>';
                    }
                    ?>
                </div>
            </div>
            <a class="managementBUTTON" href="<?php echo $base_url; ?>/management/addData">
                <span>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</span>
            </a>
        </div>
        <!-- ----------------- BTN SECTION ------------------- -->
        <div class="management_section_btn">
            <div class="management_section_left">
                <form class="btn_management_all">
                    <a href="/management" class="<?= ($request_uri == '/management') ? 'active' : ''; ?> btn_approve_01">ทั้งหมด</a>
                    <a href="/management/material" class="<?= ($request_uri == '/management/material') ? 'active' : ''; ?> btn_approve_02">วัสดุ</a>
                    <a href="/management/equipment" class="<?= ($request_uri == '/management/equipment') ? 'active' : ''; ?> btn_approve_02">อุปกรณ์</a>
                    <a href="/management/tools" class="<?= ($request_uri == '/management/tools') ? 'active' : ''; ?> btn_approve_02">เครื่องมือ</a>
                </form>
                <form class="management_search_header" method="get">
                    <input class="search_input" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                    <button class="search_btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>
            <div class="header_num_section">
                <span>
                    <?php
                    if ($request_uri === '/management') {
                        echo 'วัสดุ อุปกรณ์ และเครื่องมือทั้งหมด';
                    } elseif ($request_uri === '/management/material') {
                        echo 'วัสดุทั้งหมด';
                    } elseif ($request_uri === '/management/equipment') {
                        echo 'อุปกรณ์ทั้งหมด';
                    } elseif ($request_uri === '/management/tools') {
                        echo 'เครื่องมือทั้งหมด';
                    }
                    echo " $total_records รายการ";
                    ?>
                </span>
            </div>
        </div>
        <?php if (empty($result)) : ?>
            <div class="management_found">
                <i class="icon fa-solid fa-xmark"></i>
                <span id="B">ไม่พบรายการวัสดุ อุปกรณ์ และเครื่องมือในระบบ</span>
            </div>
        <?php else : ?>
            <div class="management_grid">
                <?php foreach ($result as $results) : ?>
                    <div class="management_grid_row">
                        <div class="content_img">
                            <div class="contentBLOCK">
                                <img src="<?php echo $base_url; ?>/assets/uploads/<?php echo htmlspecialchars($results['img_name']); ?>" loading="lazy">
                            </div>
                        </div>
                        <div class="content_info">
                            <div class="content_name">
                                <?php echo htmlspecialchars($results['sci_name']); ?>
                            </div>
                            <div class="subcontent_name">
                                <div class="categories">
                                    <span id="B">ประเภท </span><?php echo htmlspecialchars($results['categories']); ?>
                                </div>
                                <div class="amount">
                                    <span id="B">จำนวน </span><?php echo htmlspecialchars($results['amount']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="management_grid_content_footer">
                            <?php if ($results['availability'] == 0) : ?>
                                <div class="ready-to-use">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>พร้อมใช้งาน</span>
                                </div>
                            <?php else : ?>
                                <div class="moderately">
                                    <i class="fa-solid fa-ban"></i>
                                    <span>บำรุงรักษา</span>
                                </div>
                            <?php endif ?>
                            <div class="content_actions">
                                <a href="<?php echo $base_url; ?>/management/detailsData?id=<?= $results['ID'] ?>" class="detailsCRUD action_btn">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <div class="tooltip"><span>รายละเอียด</span></div>
                                </a>
                                <a href="<?php echo $base_url; ?>/management/edit?id=<?= $results['ID'] ?>" class="edit_crud_btn action_btn">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <div class="tooltip"><span>แก้ไข</span></div>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- PAGINATION PAGE -->
        <?php if ($pagination_display) : ?>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?page=1<?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&laquo;</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&lsaquo;</a>
                <?php endif; ?>
                <?php
                $total_pages = ceil($total_records / $results_per_page);
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $page) {
                        echo "<a class='active'>$i</a>";
                    } else {
                        echo "<a href='?page=$i" . ($searchValue ? '&search=' . $searchValue : '') . "'>$i</a>";
                    }
                }
                ?>
                <?php if ($page < $total_pages) : ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&rsaquo;</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>t