<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';

// ดึงข้อมูลผู้ใช้เพียงครั้งเดียว
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
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Fetch data to edit
        $stmt = $conn->prepare("SELECT * FROM crud INNER JOIN info_sciname ON crud.serial_number = info_sciname.serial_number WHERE crud.ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขรายชื่อศูนย์วิทยาศาสตร์</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_edit.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <?php if (isset($_SESSION['updateData_success'])) : ?>
        <div class="toast">
            <div class="toast_section">
                <div class="toast_content">
                    <i class="fas fa-solid fa-check check"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?php echo $_SESSION['updateData_success']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
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
    <main class="add_MET_PAGE">
        <form action="<?php echo $base_url; ?>/models/CRUD.php" method="POST" enctype="multipart/form-data">
            <div class="add_MET_section_header">
                <div class="add_MET_section_header_1">
                    <a class="historyBACK" href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
                    <div class="breadcrumb">
                        <a href="/">หน้าหลัก</a>
                        <span>&gt;</span>
                        <?php
                        if ($request_uri == '/management') {
                            echo '<a href="/management">การจัดการระบบ</a>
                            <span>&gt;</span>';
                        }
                        ?>
                        <a href="<?php echo $editData['ID']; ?>"><?php echo $editData['sci_name']; ?></a>
                    </div>
                </div>
            </div>
            <div class="add_MET_section_form">
                <div class="img">
                    <div class="imgInput">
                        <img src="<?php echo $base_url; ?>/assets/uploads/<?php echo $editData['img_name']; ?>" class="previewImg" id="previewImg">
                    </div>
                </div>
                <div class="btn_img">
                    <div>
                        <label for="imgInput" class="choose-file">เลือกรูปภาพที่จะอัพโหลด</label>
                        <input type="file" id="imgInput" name="img" accept="image/jpeg, image/png">
                    </div>
                    <div class="input">
                        <input type="text" hidden value="<?php echo $editData['ID']; ?>" required name="id">
                        <input type="text" id="imgNameInput" value="<?php echo $editData['img_name']; ?>" required disabled name="sci_name">
                        <input type="hidden" value="<?php echo $editData['img_name']; ?>" required name="img2">
                    </div>
                </div>
                <div class="input_Data">
                    <label for="sci_name">ชื่อ</label>
                    <input type="text" name="sci_name" required value="<?php echo $editData['sci_name'] ?>">
                </div>
                <div class="input_Data">
                    <label for="serial_number">Serial Number</label>
                    <input type="text" name="serial_number" required value="<?php echo $editData['serial_number'] ?>">
                </div>
                <div class="col">
                    <div class="input_Data">
                        <label for="categories">ประเภท</label>
                        <select name="categories" required>
                            <option value="วัสดุ" <?php if ($editData['categories'] === 'วัสดุ') echo 'selected'; ?>>วัสดุ</option>
                            <option value="อุปกรณ์" <?php if ($editData['categories'] === 'อุปกรณ์') echo 'selected'; ?>>อุปกรณ์</option>
                            <option value="เครื่องมือ" <?php if ($editData['categories'] === 'เครื่องมือ') echo 'selected'; ?>>เครื่องมือ</option>
                        </select>
                    </div>
                    <div class="input_Data">
                        <label for="amount">จำนวน</label>
                        <input type="number" name="amount" min="1" required value="<?php echo $editData['amount'] ?>">
                    </div>
                </div>
                <div class="col">
                    <?php
                    $installation_date = date('Y-m-d\TH:i', strtotime($editData['installation_date']));
                    ?>
                    <div class="input_Data">
                        <label for="installation_date">วันที่ติดตั้ง</label>
                        <input type="datetime-local" name="installation_date" value="<?php echo $installation_date; ?>">
                    </div>
                    <div class="input_Data">
                        <label for="company">บริษัท</label>
                        <input type="text" name="company" value="<?php echo $editData['company'] ?>">
                    </div>
                </div>
                <div class="col">
                    <div class="input_Data">
                        <label for="contact_number">เบอร์โทรศัพท์บริษัท</label>
                        <input type="text" name="contact_number" value="<?php echo $editData['contact_number'] ?>">
                    </div>
                    <div class="input_Data">
                        <label for="contact">คนติดต่อ</label>
                        <input type="text" name="contact" value="<?php echo $editData['contact'] ?>">
                    </div>
                </div>
                <div class="col">
                    <div class="input_Data">
                        <label for="brand">ยี่ห้อ</label>
                        <input type="text" name="brand" value="<?php echo $editData['brand'] ?>">
                    </div>
                    <div class="input_Data">
                        <label for="model">รุ่น</label>
                        <input type="text" name="model" value="<?php echo $editData['model'] ?>">
                    </div>
                </div>
                <div class="col">
                    <div class="input_Data">
                        <label for="details">รายละเอียด</label>
                        <textarea id="details" name="details"><?php echo $editData['details'] ?></textarea>
                    </div>
                </div>
            </div>
            <div class="btn_footer">
                <input type="hidden" name="id" value="<?php echo $editData['ID']; ?>">
                <button type="submit" name="update" class="submitADD">ยืนยัน</button>
                <a href="javascript:history.back();" class="go_back">ยกเลิก</a>
            </div>
        </form>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/pop_upEdit.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/noti_toast.js"></script>
</body>

</html>