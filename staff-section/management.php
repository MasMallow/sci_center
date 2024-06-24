<?php
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

// ตรวจสอบและกำหนดค่าการค้นหา
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
    $searchQuery = "%" . $_GET["search"] . "%";
} else {
    $searchQuery = null;
}

// หากมีการกำหนดหน้าปัจจุบันให้ใช้ค่านี้ ไม่งั้นให้ใช้หน้าที่ 1
if (!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

$results_per_page = 1;

// คำนวณ offset สำหรับคำสั่ง SQL LIMIT
$offset = ($page - 1) * $results_per_page;

// คำสั่ง SQL เพื่อดึงข้อมูล
$query = "SELECT * FROM crud LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

// เพิ่มเงื่อนไข categories
if ($request_uri === '/management/material') {
    $query .= " WHERE crud.categories = 'วัสดุ'";
} elseif ($request_uri === '/management/equipment') {
    $query .= " WHERE crud.categories = 'อุปกรณ์'";
} elseif ($request_uri === '/management/tools') {
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
if ($request_uri === '/management/material') {
    $total_records_query .= " WHERE crud.categories = 'วัสดุ'";
} elseif ($request_uri === '/management/equipment') {
    $total_records_query .= " WHERE crud.categories = 'อุปกรณ์'";
} elseif ($request_uri === '/management/tools') {
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $searchTitle; ?>จัดการวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">

    <style>
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <!-- Header -->
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
                <a href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">จัดการระบบ</span>
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
            <div class="choose_categories_btn">
                <a href="<?php echo $base_url; ?>/management/addData">
                    <span>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</span>
                </a>
            </div>
        </div>
        <div class="management_section_btn">
            <form class="management_search_header" method="get">
                <input class="search_input" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                <button class="search_btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <form class="btn_management_all">
                <a href="/management" class="<?= (strpos($request_uri, '/management') !== false && $request_uri === '/management') ? 'active' : ''; ?> btn_management_01">ทั้งหมด</a>
                <a href="/management/material" class="<?= (strpos($request_uri, '/management/material') !== false) ? 'active' : ''; ?> btn_management_02">วัสดุ</a>
                <a href="/management/equipment" class="<?= (strpos($request_uri, '/management/equipment') !== false) ? 'active' : ''; ?> btn_management_02">อุปกรณ์</a>
                <a href="/management/tools" class="<?= (strpos($request_uri, '/management/tools') !== false) ? 'active' : ''; ?> btn_management_03">เครื่องมือ</a>
            </form>
        </div>
        <?php if (empty($result)) : ?>
            <div class="management_found">
                <i class="icon fa-solid fa-xmark"></i>
                <span id="B">ไม่พบรายการวัสดุ อุปกรณ์ และเครื่องมือในระบบ</span>
            </div>
        <?php else : ?>
            <div class="management_grid">
                <?php foreach ($result as $results) : ?>
                    <div class="management_grid_content">
                        <div class="management_grid_header">
                            <div class="content_img">
                                <img src="<?php echo $base_url; ?>/assets/uploads/<?php echo htmlspecialchars($results['img_name']); ?>" loading="lazy">
                            </div>
                        </div>
                        <div class="content_status_details">
                            <?php if ($results['amount'] >= 50) { ?>
                                <div class="ready-to-use">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">ปกติ</span>
                                </div>
                            <?php } elseif ($results['amount'] <= 30 && $results['amount'] >= 1) { ?>
                                <div class="moderately">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <span id="B">ปานกลาง</span>
                                </div>
                            <?php } elseif ($results['amount'] == 0) { ?>
                                <div class="not-available">
                                    <i class="fa-solid fa-ban"></i>
                                    <span id="B">ไม่พร้อมใช้งาน</span>
                                </div>
                            <?php } ?>
                            <div class="content_details">
                                <a href="management/detailsData?id=<?= $results['ID'] ?>" class="details_btn">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                        </div>
                        <div class="management_grid_content_body">
                            <div class="content_name"><span id="B">ชื่อ </span><?php echo htmlspecialchars($results['sci_name']); ?></div>
                            <div class="content_categories"><span id="B">ประเภท </span><?php echo htmlspecialchars($results['categories']); ?></div>
                            <div class="content_amount"><span id="B">คงเหลือ </span><?php echo htmlspecialchars($results['amount']); ?></div>
                        </div>
                        <div class="management_grid_content_footer">
                            <a href="<?php echo $base_url; ?>/management/editData?id=<?= $results['ID'] ?>" class="edit_crud_btn">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>แก้ไขข้อมูล</span>
                            </a>
                            <a href="<?php echo $base_url; ?>/management/detailsData?id=<?= $results['ID'] ?>" class="delete_btn">
                                <i class="icon fa-solid fa-trash"></i>
                                <span>ลบข้อมูล</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
            <!-- Pagination -->
            <?php if ($pagination_display) : ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ceil($total_records / $results_per_page); $i++) : ?>
                        <a href="?page=<?= $i ?>" <?= ($page == $i) ? 'class="active"' : '' ?>><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/pop_upEdit.js"></script>
</body>

</html>