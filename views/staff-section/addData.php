<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

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
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลการอนุมัติการจอง
$stmt = $conn->prepare("SELECT * FROM logs_management");
$stmt->execute();
$Management = $stmt->fetchAll(PDO::FETCH_ASSOC);
$ManagementCount = count($Management); // นับจำนวนรายการ
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php') ?>
    </header>
    <?php if ($request_uri == '/management/addData') : ?>
        <main class="add_MET_PAGE">
            <?php if (isset($_SESSION['Uploadsuccess'])) : ?>
                <div class="toast">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-check check"></i>
                            <div class="toast_content_message">
                                <span class="text text_2"><?php echo $_SESSION['Uploadsuccess']; ?></span>
                            </div>
                            <i class="fa-solid fa-xmark close"></i>
                            <div class="progress"></div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['Uploadsuccess']); ?>
            <?php endif ?>
            <?php if (isset($_SESSION['errorUpload'])) : ?>
                <div class="toast error">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-xmark check error"></i>
                            <div class="toast_content_message">
                                <span class="text text_2"><?php echo $_SESSION['errorUpload']; ?></span>
                            </div>
                            <i class="fa-solid fa-xmark close"></i>
                            <div class="progress error"></div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['errorUpload']); ?>
            <?php endif ?>
            <div class="add_MET_section_header">
                <div class="add_MET_section_header_1">
                    <a class="historyBACK" href="javascript:history.back()">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                    <div class="breadcrumb">
                        <a href="/">หน้าหลัก</a>
                        <span>&gt;</span>
                        <?php
                        if (strpos($request_uri, '/management') !== false) {
                            echo '<a href="/management">การจัดการระบบ</a>';
                            echo '<span>&gt;</span>';
                        }
                        if ($request_uri == '/management/addData') {
                            echo '<a href="/management/addData">เพิ่มข้อมูล</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="add_MET_section_header_11">
                    <a href="<?php echo $base_url; ?>/management/viewlog"><span id="B">ดูระบบ</span></a>
                </div>
            </div>
            <form action="<?php echo $base_url; ?>/models/CRUD.php" method="POST" enctype="multipart/form-data">
                <div class="add_MET_section_form">
                    <div class="img">
                        <div class="imgInput">
                            <i class="upload fa-solid fa-upload"></i>
                            <label for="imgInput">
                                <label class="img" id="B">เลือกรูปภาพที่จะอัพโหลด</label>
                                <img loading="lazy" class="previewImg" id="previewImg" alt="">
                            </label>
                            <input type="file" required class="input-img" id="imgInput" name="img" accept="image/jpeg, image/png" hidden>
                        </div>
                    </div>
                    <label class="upload-tip"><b>Note : </b> Only JPG, JPEG & PNG files allowed to upload.</label>
                    <div class="btn_img">
                        <label class="choose-file" for="imgInput">เลือกรูปภาพที่จะอัพโหลด</label>
                        <label class="file_chosen_img" id="file-chosen-img">ยังไม่ได้เลือกไฟล์</label>
                    </div>
                    <div class="input_Data">
                        <label>ชื่อ</label>
                        <input type="text" name="sci_name" required placeholder="ระบุชื่อของวัสดุ อุปกรณ์ และเครื่องมือ">
                    </div>
                    <div class="input_Data">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" required placeholder="ระบุ Serial Number">
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>ประเภท</label>
                            <select name="categories" required>
                                <option value="" selected disabled>ระบุประเภท</option>
                                <option value="วัสดุ">วัสดุ</option>
                                <option value="อุปกรณ์">อุปกรณ์</option>
                                <option value="เครื่องมือ">เครื่องมือ</option>
                            </select>
                        </div>
                        <div class="input_Data">
                            <label>จำนวน</label>
                            <input type="number" name="amount" min="1" required placeholder="กรุณาระบุจำนวน">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>วันที่ติดตั้ง</label>
                            <input type="datetime-local" name="installation_date">
                        </div>
                        <div class="input_Data">
                            <label>บริษัท</label>
                            <input type="text" name="company" placeholder="ระบุบริษัทของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>เบอร์โทรศัพท์บริษัท</label>
                            <input type="text" name="contact_number" placeholder="ระบุเบอร์โทรศัพท์บริษัทของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                        <div class="input_Data">
                            <label>คนติดต่อ</label>
                            <input type="text" name="contact" placeholder="ระบคนติดต่อของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>ยี่ห้อ</label>
                            <input type="text" name="brand" placeholder="ระบุยี่ห้อ">
                        </div>
                        <div class="input_Data">
                            <label>รุ่น</label>
                            <input type="text" name="model" placeholder="ระบุรุ่น">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="details">รายละเอียด</label>
                            <textarea id="details" name="details" placeholder="ระบุรายละเอียด"></textarea>
                        </div>
                    </div>
                </div>
                <div class="btn_footer">
                    <button type="submit" name="submit" class="submitADD">ยืนยัน</button>
                    <a href="javascript:history.back();" class="go_back">ยกเลิก</a>
                </div>
            </form>
        </main>
    <?php elseif ($request_uri == '/management/viewlog') : ?>
        <main class="viewLog_Management">
            <div class="viewLog_Management_section">
                <div class="viewLog_Management_section_1">
                    <a class="historyBACK" href="javascript:history.back()">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                    <div class="breadcrumb">
                        <a href="/">หน้าหลัก</a>
                        <span>&gt;</span>
                        <?php
                        if (strpos($request_uri, '/management') !== false) {
                            echo '<a href="/management">การจัดการระบบ</a>';
                            echo '<span>&gt;</span>';
                        }
                        if ($request_uri == '/management/viewlog') {
                            echo '<a href="/management/viewlog">ดูประวัติการจัดการระบบ</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="viewLog_Management_section_2">
                    <a href="<?php echo $base_url; ?>/management/addData">กลับหน้าเพิ่มข้อมูล</a>
                </div>
            </div>
            <?php if (!empty($Management)) : ?>
                <div class="viewLog_Management_PAGE">
                    <div class="viewLog_Management_MAIN">
                        <div class="viewLog_Management_header">
                            <span id="B">การจัดการระบบคลัง</span>
                        </div>
                        <div class="viewLog_Management_body">
                            <?php foreach ($Management as $Data) : ?>
                                <div class="viewLog_Management_content">
                                    <div class="viewLog_User_content_1">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right"></i>
                                        <a href="<?= $base_url . '/management/viewlog/details?id=' . htmlspecialchars($Data['ID'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8') ?>
                                            (<?= htmlspecialchars($Data['log_Role'], ENT_QUOTES, 'UTF-8') ?>) </a>
                                    </div>
                                    <div class="viewLog_User_content_2">
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="viewLog_User_content_3">
                                        <?php
                                        switch ($Data['log_Status']) {
                                            case 'Add':
                                                echo "ได้ทำการเพิ่มข้อมูล";
                                                break;
                                            case 'Edit':
                                                echo "ได้ทำการแก้ไขข้อมูล";
                                                break;
                                            case 'Delete':
                                                echo "ได้ทำการลบข้อมูล";
                                                break;
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>

        <?php elseif ($request_uri == '/management/viewlog/details') : ?>
            <?php
            try {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("SELECT * FROM logs_management WHERE ID = :id");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $detailsManagement = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            } ?>
            <?php if (!empty($detailsManagement)) : ?>
                <div class="viewLog_Management_Details">
                    <div class="viewLog_Management_section">
                        <div class="viewLog_Management_section_1">
                            <a class="historyBACK" href="javascript:history.back()">
                                <i class="fa-solid fa-arrow-left-long"></i>
                            </a>
                            <div class="breadcrumb">
                                <a href="/">หน้าหลัก</a>
                                <span>&gt;</span>
                                <?php
                                if (strpos($request_uri, '/management') !== false) {
                                    echo '<a href="/management">การจัดการระบบ</a>';
                                    echo '<span>&gt;</span>';
                                }
                                if (strpos($request_uri, '/management/viewlog') !== false) {
                                    echo '<a href="/management/viewlog">ดูประวัติการจัดการระบบ</a>';
                                    echo '<span>&gt;</span>';
                                }
                                if (strpos($request_uri, '/management/viewlog/details') !== false) {
                                    echo '<a href="">รายละเอียด</a>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="viewLog_Management_section_2">
                            <a href="<?php echo $base_url; ?>/management/addData">กลับหน้าเพิ่มข้อมูล</a>
                        </div>
                    </div>
                    <div class="viewLog_Management_MAIN">
                        <div class="viewLog_Management_header" id="B">
                            รายละเอียด
                        </div>
                        <div class="viewLog_Management_body">
                            <?php foreach ($detailsManagement as $Data) : ?>
                                <div class="viewLog_Management_content">
                                    <div class="viewLog_Management_content_1">
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                        <?= htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($Data['log_Role'], ENT_QUOTES, 'UTF-8') ?>)
                                        <?php
                                        switch ($Data['log_Status']) {
                                            case 'Add':
                                                echo "ได้ทำการเพิ่มข้อมูล";
                                                break;
                                            case 'Edit':
                                                echo "ได้ทำการแก้ไขข้อมูล";
                                                break;
                                            case 'Delete':
                                                echo "ได้ทำการลบข้อมูล";
                                                break;
                                        }
                                        ?>
                                    </div>
                                    <div class="viewLog_Management_content_2">
                                        <?php
                                        $logContent = json_decode($Data['log_Content'], true);
                                        if ($logContent) : ?>
                                            <div class="log-item"><span id="B">ชื่อวิทยาศาสตร์</span> <?= htmlspecialchars($logContent['sci_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="log-item"><span id="B">หมายเลขซีเรียล</span> <?= htmlspecialchars($logContent['serial_number'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php else : ?>
                                            ไม่สามารถแสดงข้อมูลได้
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>
        </main>
    <?php endif; ?>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/pop_upEdit.js"></script>
</body>

</html>