<?php
session_start();
require_once 'assets/database/dbConfig.php';

// ดึงข้อมูลผู้ใช้เพียงครั้งเดียว
if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: ' . $base_url . ' auth/sign_in.php');
    exit;
}
?>
<?php
$searchTitle = "";
$searchValue = "";
$result = [];

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

try {
    $action = $_GET['action'] ?? 'all';
    $searchQuery = isset($_GET["search"]) && !empty($_GET["search"]) ? "%" . $_GET["search"] . "%" : null;
    $query = "SELECT * FROM crud WHERE 1=1";

    if ($action === 'material') {
        $query .= " AND categories = 'วัสดุ'";
    } elseif ($action === 'equipment') {
        $query .= " AND categories = 'อุปกรณ์'";
    } elseif ($action === 'tools') {
        $query .= " AND categories = 'เครื่องมือ'";
    }

    if ($searchQuery) {
        $query .= " AND (sci_name LIKE :search OR s_number LIKE :search)";
    }

    $query .= " ORDER BY id ASC";
    $stmt = $conn->prepare($query);

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
    <header><?php include('includes/header.php'); ?></header>
    <div class="header_management">
        <div class="header_management_section">
            <div class="header_name_section">
                <a href="<?php echo $base_url; ?>/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">จัดการระบบ</span>
            </div>
            <div class="header_num_section">
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
            </div>
            <div class="header_btn_section">
                <a href="<?php echo $base_url; ?>/addData" class="choose_categories_btn">
                    <i class="icon fa-solid fa-plus"></i>
                    <span>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</span>
                </a>
            </div>
        </div>
    </div>
    <div class="management_section_btn">
        <form class="management_search_header" method="get">
            <input type="hidden" name="action" value="<?= htmlspecialchars($action); ?>">
            <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
            <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <form class="btn_management_all" method="get">
            <button type="submit" class="<?= ($action === 'all') ? 'active' : ''; ?> btn_management_01" name="action" value="all">ทั้งหมด</button>
            <button type="submit" class="<?= ($action === 'material') ? 'active' : ''; ?> btn_management_02" name="action" value="material">วัสดุ</button>
            <button type="submit" class="<?= ($action === 'equipment') ? 'active' : ''; ?> btn_management_02" name="action" value="equipment">อุปกรณ์</button>
            <button type="submit" class="<?= ($action === 'tools') ? 'active' : ''; ?> btn_management_03" name="action" value="tools">เครื่องมือ</button>
        </form>
    </div>
    <div class="management_grid">
        <?php if (empty($result)) { ?>
            <div class="user_approve_not_found">
                <i class="icon fa-solid <?= ($notification === 'noti_use') ? 'fa-arrow-up' : 'fa-address-book'; ?>"></i>
                <span id="B"><?= ($notification === 'noti_use') ? 'ไม่มีแจ้งเตือนการขอใช้' : 'ไม่มีแจ้งเตือนการจอง'; ?></span>
            </div>
            <?php } else {
            foreach ($result as $results) { ?>
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
                                                <label for="imgInput_<?php echo htmlspecialchars($results['ID']); ?>">เลือกรูปภาพที่จะอัพโหลด</label>
                                                <input type="hidden" value="<?php echo htmlspecialchars($results['img_name']); ?>" required name="img2">
                                                <span class="file_chosen_img" id="file-chosen-img_<?php echo htmlspecialchars($results['ID']); ?>"><?php echo htmlspecialchars($results['img_name']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="details_content_right">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($results['ID']); ?>">
                                        <ul class="details_content_li">
                                            <li>
                                                <div class="details_content_1"><span id="B">ชื่อ</span></div>
                                                <div class="details_content_2"><input type="text" name="sci_name" value="<?php echo htmlspecialchars($results['sci_name']); ?>"></div>
                                            </li>
                                            <li>
                                                <div class="details_content_1"><span id="B">จำนวน</span></div>
                                                <div class="details_content_2"><input type="number" name="amount" value="<?php echo htmlspecialchars($results['amount']); ?>"></div>
                                            </li>
                                            <li>
                                                <div class="details_content_1"><span id="B">ประเภท</span></div>
                                                <div class="details_content_2">
                                                    <select name="categories">
                                                        <?php
                                                        $categoriesfixes = ['วัสดุ', 'อุปกรณ์', 'เครื่องมือ'];
                                                        foreach ($categoriesfixes as $categoriesfixe) {
                                                            $selected = ($results['categories'] == $categoriesfixe) ? "selected" : "";
                                                            echo "<option value='$categoriesfixe' $selected>$categoriesfixe</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1"><span id="B">รุ่น</span></div>
                                                <div class="details_content_2"><span>BK-FD12P</span></div>
                                            </li>
                                            <li>
                                                <div class="details_content_1"><span id="B">ยี่ห้อ</span></div>
                                                <div class="details_content_2"><span>BIOBASE</span></div>
                                            </li>
                                            <li>
                                                <div class="details_content_1"><span id="B">บริษัท</span></div>
                                                <div class="details_content_2"><span>BIOBASE BIODUSTRY(SHANDONG) CO.,LTD</span></div>
                                            </li>
                                        </ul>
                                        <div class="details_content_footer">
                                            <button type="submit" name="update">ยืนยัน</button>
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
                        <button class="edit_crud_btn details_btn" data-modal="<?php echo $results['ID']; ?>">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>แก้ไขข้อมูล</span>
                        </button>
                        <button class="delete_btn delete_popup" data-modal="delete_<?php echo $results['ID']; ?>">
                            <i class="icon fa-solid fa-trash"></i>
                            <span>ลบข้อมูล</span>
                        </button>
                        <div class="delete_content_popup" id="delete_<?php echo $results['ID']; ?>">
                            <div class="delete_content">
                                <div class="delete_content_header">
                                    <span id="B">ยืนยันการลบ</span>
                                    <div class="close_popup_delete">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="delete_content_body">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($results['ID']); ?>">
                                    <table class="delete_content_table">
                                        <tr>
                                            <td>
                                                <span id="B">Serial Number</span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($results['serial_number']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span id="B">ชื่อ</span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($results['sci_name']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span id="B">จำนวน</span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($results['amount']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span id="B">ประเภท</span>
                                            </td>
                                            <td>
                                                <?php
                                                $categoriesfixes = ['วัสดุ', 'อุปกรณ์', 'เครื่องมือ'];
                                                foreach ($categoriesfixes as $categoriesfixe) {
                                                    if ($results['categories'] == $categoriesfixe) {
                                                        echo $categoriesfixe;
                                                        break;
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="delete_content_footer">
                                    <a href="delete.php?id=<?php echo htmlspecialchars($results['ID']); ?>" class="Delete">
                                        <i class="icon fa-solid fa-trash"></i><span>ลบข้อมูล</span>
                                    </a>
                                    <button class="close_popup_delete">ปิดหน้าต่าง</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php }
        } ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/choose_categories.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/choose_categories.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/pop_upEdit.js"></script>
</body>

</html>