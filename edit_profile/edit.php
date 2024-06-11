<?php
session_start();
require_once '../assets/database/dbConfig.php';

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // สร้างคำสั่ง SQL
    $sql = "SELECT * FROM users_db WHERE user_id = :user_id";

    // เตรียมและ execute คำสั่ง SQL
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ดึงข้อมูล
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขบัญชีผู้ใช้</title>
    <link href="../assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="../assets/css/navigator.css">
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
    <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
</head>

<body>
    <?php
    include_once('header.php')
    ?>
    <!-- <div class="edit_profile_status">
        <div class="edit_profile_status_content">
            <div class="edit_profile_header_status">
                <span id="B">แจ้งเตือน</span>
                <div class="modalClose" id="close"><i class="fa-solid fa-xmark"></i></div>
            </div>
            <div class="edit_profile_header_body">
                <div class="edit_profile_header_body_error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="edit_profile_header_body_2">
                    <span id="B">!! เกิดข้อผิดพลาด แก้ไขบัญชีผู้ใช้ไม่สำเร็จ !!</span>
                </div>
            </div>
        </div>
    </div> -->
    <?php
    if (isset($_SESSION['edit_profile_success'])) {
    ?>
        <div class="edit_profile_status">
            <div class="edit_profile_status_content">
                <div class="edit_profile_header_status">
                    <span id="B">แจ้งเตือน</span>
                </div>
                <div class="edit_profile_header_body">
                    <div class="edit_profile_header_body_1">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="edit_profile_header_body_2">
                        <span id="B">แก้ไขบัญชีผู้ใช้สำเร็จ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php
        unset($_SESSION['edit_profile_success']); // เคลียร์ค่า error ใน session
    }
    ?>
    <div class="edit_profile">
        <div class="edit_profile_header">
            <a href="../../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">แก้ไขบัญชีผู้ใช้</span>
        </div>
        <div class="edit_profile_body">
            <form action="process/update_profile.php" method="post">
                <div class="col_edit">
                    <div class="input_edit">
                        <span>รหัสผ่านใหม่</span>
                        <div class="show_password">
                            <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่านใหม่">
                            <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    <div class="input_edit">
                        <span>ยืนยันรหัสผ่าน</span>
                        <div class="show_password">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่">
                            <i class="icon_password fas fa-eye-slash" onclick="togglecPassword()"></i>
                        </div>
                    </div>
                </div>
                <div class="col_edit">
                    <div class="input_edit">
                        <span>คำนำหน้า</span>
                        <select name="pre">
                            <?php
                            // คำนำหน้าที่มีอยู่ในฐานข้อมูล
                            $prefixes = ['นาย', 'นาง', 'นางสาว', 'อ.', 'ผศ.ดร.'];
                            foreach ($prefixes as $prefix) {
                                // ตรวจสอบว่าคำนำหน้านี้มีอยู่ในข้อมูลที่มีหรือไม่
                                $selected = ($userData['pre'] == $prefix) ? "selected" : "";
                                // แสดง option
                                echo "<option value='$prefix' $selected>$prefix</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input_edit">
                        <span>ชื่อ</span>
                        <input type="text" name="surname" value="<?php echo $userData['surname']; ?>">
                    </div>
                    <div class="input_edit">
                        <span>นามสกุล</span>
                        <input type="text" name="lastname" value="<?php echo $userData['lastname']; ?>">
                    </div>
                </div>
                <div class="col_edit">
                    <div class="input_edit">
                        <span>ตำแหน่ง</span>
                        <select name="role">
                            <?php
                            // คำนำหน้าที่มีอยู่ในฐานข้อมูล
                            $rolefixes = ['อาจารย์', 'บุคลากร', 'เจ้าหน้าที่'];
                            foreach ($rolefixes as $rolefix) {
                                // ตรวจสอบว่าคำนำหน้านี้มีอยู่ในข้อมูลที่มีหรือไม่
                                $selected = ($userData['role'] == $rolefix) ? "selected" : "";
                                // แสดง option
                                echo "<option value='$rolefix' $selected>$rolefix</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input_edit">
                        <span>สังกัด</span>
                        <input type="text" name="agency" value="<?php echo $userData['agency']; ?>">
                    </div>
                    <div class="input_edit">
                        <span>เบอร์โทรศัพท์</span>
                        <input type="text" name="phone_number" value="<?php echo $userData['phone_number']; ?>">
                    </div>
                </div>
                <div class="edit_profile_footer">
                    <button type="submit" class="submit">ยืนยัน</button>
                    <a href="../" class="cancel">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/show_password.js"></script>
</body>

</html>