<?php
session_start();
require_once 'assets/database/dbConfig.php';

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="add_MET">
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
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['errorUpload']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['errorUpload']); ?>
        <?php endif ?>
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <a href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
                <label id="B">เพิ่มรายการศูนย์วิทยาศาสตร์</label>
            </div>
            <form action="<?php echo $base_url; ?>/Staff/upload.php" method="POST" enctype="multipart/form-data">
                <div class="add_MET_section_form">
                    <div class="form_left">
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
                    </div>
                    <div class="form_right">
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
                </div>
                <div class="btn_footer">
                    <button type="submit" name="submit" class="submitADD">ยืนยัน</button>
                    <a href="javascript:history.back();" class="go_back">ยกเลิก</a>
                </div>
            </form>
        </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/add.js"></script>
</body>

</html>