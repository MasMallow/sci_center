<?php
session_start();
include_once '../assets/database/connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
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

    <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/navigator.css">
    <link rel="stylesheet" href="add-remove-update.css">
</head>

<body>
    <?php
    include('header.php')
    ?>
    <!-- Modal Popup -->
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <span id="B">เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</span>
            </div>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="pagination">
                    <div class="number active">1</div>
                    <div class="bar"></div>
                    <div class="number">2</div>
                </div>
                <div class="add_MET_section_form active">
                    <div class="img">
                        <div class="imgInput">
                            <label for="imgInput">
                                <i class="upload fa-solid fa-upload"></i>
                                <span class="img">เลือกรูปภาพที่จะอัพโหลด</span>
                                <img loading="lazy" class="previewImg" id="previewImg" alt="">
                            </label>
                            <input type="file" required class="input-img" id="imgInput" name="img" accept="image/jpeg, image/png" hidden>
                        </div>
                    </div>
                    <span class="upload-tip"><b>Note: </b>Only JPG, JPEG, PNG & GIF files allowed to upload.</span>
                    <div class="btn_img">
                        <label class="choose-file" for="imgInput">เลือกรูปภาพที่จะอัพโหลด</label>
                        <span class="file_chosen_img" id="file-chosen-img">ยังไม่ได้เลือกไฟล์</span>
                    </div>
                    <div class="input_box">
                        <span>ชื่อ</span>
                        <input type="text" name="sci_name" required placeholder="ระบุชื่อของวัสดุ อุปกรณ์ และเครื่องมือ">
                    </div>
                    <div class="input_box">
                        <span>Serial Number</span>
                        <input type="text" name="s_number" required placeholder="ระบุ Serial Number ของวัสดุ อุปกรณ์ และเครื่องมือ">
                    </div>
                    <div class="col">
                        <div class="input_box">
                            <span>จำนวน</span>
                            <input type="number" name="amount" min="1" required placeholder="กรุณาระบุจำนวน">
                        </div>
                        <div class="input_box">
                            <span>ประเภท</span>
                            <select name="categories">
                                <option value="" disabled selected>กรุณาเลือก</option>
                                <option value="วัสดุ">วัสดุ</option>
                                <option value="อุปกรณ์">อุปกรณ์</option>
                                <option value="เครื่องมือ">เครื่องมือ</option>
                            </select>
                        </div>
                    </div>
                    <div class="add_MET_footer_1">
                        <a href="#2" class="btn_next"><span>ถัดไป</span><i class="fa-solid fa-angle-right"></i></a>
                    </div>
                </div>
                <div class="add_MET_section_form">
                    <div class="col">
                        <div class="input_box">
                            <span>วันที่ติดตั้ง</span>
                            <input type="datetime-local" name="installation_date" required>
                        </div>
                        <div class="input_box">
                            <span>บริษัท</span>
                            <input type="text" name="company" required placeholder="ระบุ Serial Number ของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_box">
                            <span>เบอร์โทร บริษัท</span>
                            <input type="text" name="contact_number" required placeholder="ระบุ Serial Number ของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                        <div class="input_box">
                            <span>คนติดต่อ</span>
                            <input type="text" name="contact" required placeholder="ระบุ Serial Number ของวัสดุ อุปกรณ์ และเครื่องมือ">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_box">
                            <span>ยี่ห้อ</span>
                            <input type="text" name="brand" required placeholder="กรุณาระบุจำนวน">
                        </div>
                        <div class="input_box">
                            <span>รุ่น</span>
                            <input type="text" name="model" required placeholder="กรุณาระบุจำนวน">
                        </div>
                    </div>
                    <div class="add_MET_footer_2">
                        <a href="#1" class="btn_prev"><i class="fa-solid fa-angle-left"></i><span>ก่อนหน้า</span></a>
                    </div>
                    <div class="btn">
                        <button type="submit" name="submit" value="Upload" class="">ยืนยัน</button>
                        <button type="reset" class="reset">ล้างข้อมูล</button>
                    </div>
                </div>
        </div>
        </form>
        </div>
    </main>
    <script src="../assets/js/add.js"></script>
</body>

</html>