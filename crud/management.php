<?php
session_start();
include_once '../assets/database/connect.php';

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php
try {
    $sql = "SELECT * FROM crud";
    if (isset($_GET["search"]) && !empty($_GET["search"])) {
        $search = $_GET["search"];
        $sql .= " WHERE sci_name LIKE :search";
    }
    $sql .= " ORDER BY uploaded_on DESC";
    $stmt = $conn->prepare($sql);

    if (isset($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num = 1;
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/navigator.css">
    <link rel="stylesheet" href="add-remove-update.css">
    <link rel="stylesheet" href="../assets/css/edit.css">
</head>
<body>
    <?php include('header.php'); ?>
    <div class="main">
        <div class="container">
            <div class="head-section">
                <div class="head-name">
                    ระบบเพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
                </div>
                <div class="head-btn">
                    <button class="cancel" onclick="window.location.href='../home.php';">
                        <i class="icon fa-solid fa-xmark"></i>ยกเลิกการเพิ่มวัสดุ อุปกรณ์ และเครื่องมือ
                    </button>
                    <a class="showPopup add" href="add.php"><i class="icon fa-solid fa-plus"></i>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</a>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div class="count_list">
        <div class="count_list_1">
            <span>รายการที่เลือกทั้งหมด </span>
        </div>
    </div>
    <div class="management_grid">
        <?php if (empty($result)) { ?>
            <div class="management_grid_not_found">ไม่พบข้อมูล</div>
        <?php } else {
            foreach ($result as $results) { ?>
                <div class="management_grid_content">
                    <div class="management_grid_header">
                        <div class="content_img">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($results['img']); ?>">
                        </div>
                    </div>
                    <div class="content_status_details">
                        <?php if ($results['amount'] >= 50) { ?>
                            <div class="ready-to-use">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="B">พร้อมใช้งาน</span>
                            </div>
                        <?php } elseif ($results['amount'] <= 30 && $results['amount'] >= 1) { ?>
                            <div class="moderately">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="B">ความพร้อมปานกลาง</span>
                            </div>
                        <?php } elseif ($results['amount'] == 0) { ?>
                            <div class="not-available">
                                <i class="fa-solid fa-ban"></i>
                                <span id="B">ไม่พร้อมใช้งาน</span>
                            </div>
                        <?php } ?>
                        <div class="content_details">
                            <button class="details_btn" data-modal="<?php echo $results['id']; ?>">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                        </div>
                        <div class="content_details_popup" id="<?php echo $results['id']; ?>">
                            <div class="details">
                                <div class="details_header">
                                    <span id="B">แก้ไขข้อมูล</span>
                                    <div class="modalClose" id="closeDetails">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <form class="details_content_edit" action="update.php" method="post" enctype="multipart/form-data">
                                    <div class="details_content_left">
                                        <div class="img_details">
                                            <div class="img">
                                                <div class="imgInput">
                                                    <i class="upload fa-solid fa-upload"></i>
                                                    <span class="img">เลือกรูปภาพที่จะอัพโหลด</span>
                                                    <img loading="lazy" class="previewImg" id="previewImg" src="../assets/uploads/<?php echo htmlspecialchars($results['img']); ?>">
                                                </div>
                                            </div>
                                            <span class="upload-tip"><b>Note: </b>Only JPG, JPEG, PNG & GIF files allowed to upload.</span>
                                            <div class="btn_img">
                                                <label class="choose-file" for="imgInput">เลือกรูปภาพที่จะอัพโหลด</label>
                                                <span class="file_chosen_img" id="file-chosen-img"><?php echo htmlspecialchars($results['img']); ?></span>
                                            </div>
                                            <input type="file" class="input-img" id="imgInput" name="img" accept="image/jpeg, image/png" hidden>
                                            <input type="hidden" value="<?php echo htmlspecialchars($results['img']); ?>" name="img2">
                                        </div>
                                    </div>
                                    <div class="details_content_right">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($results['id']); ?>">
                                        <ul class="details_content_li">
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">ชื่อ</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <input type="text" name="sci_name" value="<?php echo htmlspecialchars($results['sci_name']); ?>">
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">จำนวน</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <input type="number" name="amount" value="<?php echo htmlspecialchars($results['amount']); ?>">
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">ประเภท</span>
                                                </div>
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
                                                <div class="details_content_1">
                                                    <span id="B">รุ่น</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <span>BK-FD12P</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">ยี่ห้อ</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <span>BIOBASE</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">บริษัท</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <span>BIOBASE BIODUSTRY(SHANDONG) CO.,LTD</span>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="details_content_footer">
                                            <button class="reset" type="reset">คืนค่าเดิม</button>
                                            <button type="submit" name="update">ยืนยัน</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="management_grid_content_body">
                        <div class="content_name">
                            <span id="B">ชื่อ </span><?php echo htmlspecialchars($results['sci_name']); ?>
                        </div>
                        <div class="content_categories">
                            <span id="B">ประเภท </span><?php echo htmlspecialchars($results['categories']); ?>
                        </div>
                        <div class="content_amount">
                            <span id="B">คงเหลือ </span><?php echo htmlspecialchars($results['amount']); ?>
                        </div>
                    </div>
                    <div class="management_grid_content_footer">
                        <a href="edit.php?id=<?php echo htmlspecialchars($results['id']); ?>" class="Edit">
                            <i class="icon fa-solid fa-pen-to-square"></i><span>Edit</span>
                        </a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($results['id']); ?>" class="Delete">
                            <i class="icon fa-solid fa-trash"></i><span>Delete</span>
                        </a>
                    </div>
                </div>
        <?php }
        } ?>
    </div>
    <script src="../assets/js/pop_upEdit.js"></script>
    <script src="../assets/js/add.js"></script>
</body>
</html>
