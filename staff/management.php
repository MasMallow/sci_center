<?php
session_start();
require_once 'assets/database/dbConfig.php';

// ดึงข้อมูลผู้ใช้เพียงครั้งเดียว
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

$searchTitle = "";
$searchValue = "";
$result = [];

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

try {
    $request_uri = $_SERVER['REQUEST_URI'];
    $searchQuery = isset($_GET["search"]) && !empty($_GET["search"]) ? "%" . $_GET["search"] . "%" : null;

    // สร้างคำสั่ง SQL สำหรับ JOIN ตาราง
    $query = "SELECT * FROM crud INNER JOIN info_sciname ON crud.serial_number = info_sciname.serial_number";

    // เพิ่มเงื่อนไข categories
    if ($request_uri === '/maintenance/material') {
        $query .= " WHERE c.categories = 'วัสดุ'";
    } elseif ($request_uri === '/maintenance/equipment') {
        $query .= " WHERE c.categories = 'อุปกรณ์'";
    } elseif ($request_uri === '/maintenance/tools') {
        $query .= " WHERE c.categories = 'เครื่องมือ'";
    } else {
        $query .= " WHERE 1=1"; // เพิ่มเงื่อนไขให้เป็นจริงเสมอเพื่อให้สามารถเพิ่ม AND ต่อไปได้
    }

    // เพิ่มเงื่อนไขการค้นหา
    if ($searchQuery) {
        $query .= " AND (crud.sci_name LIKE :search OR crud.serial_number LIKE :search)";
    }

    $query .= " ORDER BY crud.ID ASC";
    $stmt = $conn->prepare($query);

    // bind parameter การค้นหา
    if ($searchQuery) {
        $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
    }
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nums = count($result); // นับจำนวนรายการ
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $searchTitle; ?>จัดการวัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- ส่วนของ Link -->
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/edit.css">
</head>

<body>
    <!-- Header -->
    <header><?php include_once('assets/includes/navigator.php'); ?></header>
    <?php
    if (isset($_SESSION['success'])) {
        echo $_SESSION['success'];
        unset($_SESSION['success']); // ลบ session หลังจากแสดงแล้วเพื่อไม่ให้แสดงซ้ำ
    }

    if (isset($_SESSION['error'])) {
        echo $_SESSION['error'];
        unset($_SESSION['error']); // ลบ session หลังจากแสดงแล้วเพื่อไม่ให้แสดงซ้ำ
    }
    ?>
    <div class="Dashboard_Management">
        <div class="header_management_section">
            <div class="header_name_section">
                <a href="<?php echo $base_url; ?>/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">จัดการระบบ</span>
            </div>
            <div class="header_num_section">
                <span id="B">
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
                    echo " $nums รายการ";
                    ?>
                </span>
            </div>
            <div class="choose_categories_btn">
                <a href="<?php echo $base_url; ?>/management/addData">
                    <i class="icon fa-solid fa-plus"></i>
                    <span>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</span>
                </a>
            </div>
        </div>
        <div class="management_section_btn">
            <form class="management_search_header" method="get">
                <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
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
                                <button class="details_btn" data-modal="<?php echo htmlspecialchars($results['ID']); ?>">
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>
                            </div>
                            <div class="content_details_popup" id="<?php echo htmlspecialchars($results['ID']); ?>">
                                <div class="details">
                                    <div class="details_header">
                                        <span id="B">แก้ไขข้อมูล</span>
                                        <div class="modalClose" id="closeDetails">
                                            <i class="fa-solid fa-xmark"></i>
                                        </div>
                                    </div>
                                    <form class="details_content_edit" action="update" method="post" enctype="multipart/form-data">
                                        <div class="details_content_left">
                                            <div class="img_details">
                                                <div class="img">
                                                    <div class="imgInput">
                                                        <img class="previewImg" id="previewImg_<?php echo htmlspecialchars($results['ID']); ?>" src="<?php echo $base_url; ?>/assets/uploads/<?php echo htmlspecialchars($results['img_name']); ?>" loading="lazy">
                                                    </div>
                                                </div>
                                                <span class="upload-tip"><b>Note: </b>Only JPG, JPEG, PNG & GIF files allowed to upload.</span>
                                                <div class="btn_img">
                                                    <input type="file" class="input-img" id="imgInput_<?php echo htmlspecialchars($results['ID']); ?>" name="img" accept="image/jpeg, image/png, image/gif" data-default-img="<?php echo htmlspecialchars($results['img_name']); ?>" hidden>
                                                    <label for="imgInput_<?php echo htmlspecialchars($results['ID']); ?>">เลือกรูปภาพ</label>
                                                    <input type="hidden" name="default-img" value="<?php echo htmlspecialchars($results['img_name']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="details_content_right">
                                            <div class="input_Data">
                                                <label for="id" id="B">รหัสวัสดุ อุปกรณ์ หรือเครื่องมือ</label>
                                                <input type="text" name="id" value="<?php echo htmlspecialchars($results['serial_number']); ?>" readonly>
                                            </div>
                                            <div class="input_Data">
                                                <label for="sci_name" id="B">ชื่อวิทยาศาสตร์</label>
                                                <input type="text" name="sci_name" value="<?php echo htmlspecialchars($results['sci_name']); ?>" required>
                                            </div>
                                            <div class="input_Data">
                                                <label for="amount" id="B">จำนวน</label>
                                                <input type="number" name="amount" value="<?php echo htmlspecialchars($results['amount']); ?>" required>
                                            </div>
                                            <div class="input_Data">
                                                <label for="categories" id="B">ประเภท</label>
                                                <select name="categories" required>
                                                    <option value="วัสดุ" <?php if ($results['categories'] === 'วัสดุ') echo 'selected'; ?>>วัสดุ</option>
                                                    <option value="อุปกรณ์" <?php if ($results['categories'] === 'อุปกรณ์') echo 'selected'; ?>>อุปกรณ์</option>
                                                    <option value="เครื่องมือ" <?php if ($results['categories'] === 'เครื่องมือ') echo 'selected'; ?>>เครื่องมือ
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="input_Data">
                                                <label for="amount" id="B">จำนวน</label>
                                                <input type="text" name="amount" value="<?php echo htmlspecialchars($results['installation_date']); ?>" required>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="management_grid_content_body">
                            <div class="content_name"><span id="B">ชื่อ </span><?php echo htmlspecialchars($results['sci_name']); ?></div>
                            <div class="content_categories"><span id="B">ประเภท </span><?php echo htmlspecialchars($results['categories']); ?></div>
                            <div class="content_amount"><span id="B">คงเหลือ </span><?php echo htmlspecialchars($results['amount']); ?></div>
                        </div>
                        <div class="management_grid_content_footer">
                            <a href="management/editData?id=<?= $results['ID'] ?>" class="edit_crud_btn">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>แก้ไขข้อมูล</span>
                            </a>
                            <button class="delete_btn delete_popup" data-modal="delete_<?php echo $results['ID']; ?>">
                                <i class="icon fa-solid fa-trash"></i>
                                <span>ลบข้อมูล</span>
                            </button>
                            <div class="content_details_popup" id="delete_<?php echo htmlspecialchars($results['ID']); ?>">
                                <div class="details">
                                    <div class="details_header">
                                        <span id="B">ยืนยันการลบ</span>
                                        <div class="modalClose" id="closeDetails">
                                            <i class="fa-solid fa-xmark"></i>
                                        </div>
                                    </div>
                                    <form class="details_content_edit" action="delete" method="post">
                                        <div class="details_content">
                                            <span id="B">คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?</span>
                                            <input type="hidden" name="crud_id" value="<?php echo htmlspecialchars($results['ID']); ?>">
                                            <div class="btn-group">
                                                <button type="submit" id="B">ลบ</button>
                                                <button type="button" class="modalClose" id="B">ยกเลิก</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
            <script src="<?php echo $base_url; ?>/assets/js/pop_upEdit.js"></script>
</body>

</html>