<?php
session_start();
require_once 'assets/database/dbConfig.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครบัญชีผู้ใช้</title>
    <link href="<?php echo $base_url ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/sign_up.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/notification_popup.css">
</head>

<body>
    <?php if (isset($_SESSION['errorSign_up'])) { ?>
        <div class="toast">
            <div class="toast_section error">
                <div class="toast_content">
                    <i class="fas fa-solid fa-xmark check error"></i>
                    <div class="toast_content_message">
                        <span class="text text_2"><?php echo $_SESSION['errorSign_up']; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark close"></i>
                    <div class="progress error"></div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['errorSign_up']); ?>
    <?php } ?>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <form action="<?php echo $base_url; ?>/auth/backend/sign_upDB.php" method="post">
        <div class="register">
            <div class="register_page">
                <div class="register_page_head">
                    <a href="<?php echo $base_url; ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
                    <span id="B">สมัครบัญชีผู้ใช้</span>
                </div>
                <div class="register_page_body">
                    <div class="form">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 1</span>
                            <span>กรุณากรอกชื่อผู้ใช้และรหัสผ่าน</span>
                        </div>
                        <div class="form_body">
                            <div class="input_box">
                                <span>ชื่อผู้ใช้</span>
                                <input type="text" placeholder="กรุณากรอกชื่อผู้ใช้ (Username)" name="username" required autofocus>
                            </div>
                            <div class="input_box">
                                <span>รหัสผ่าน</span>
                                <div class="show_password">
                                    <input type="password" id="password" name="password" required placeholder="กรุณากรอกรหัสผ่าน (Password)">
                                    <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                                </div>
                                <div class="description">
                                    <b>Note : </b>รหัสผ่านต้องมีความยาวมากกว่า 8 ตัวอักษร<br>
                                    <b>Note : </b>รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว
                                </div>
                            </div>
                            <div class="input_box">
                                <span>ยืนยันรหัสผ่านอีกครั้ง</span>
                                <div class="show_password">
                                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="กรุณากรอกรหัสผ่านอีกครั้ง (confirmPassword)">
                                    <i class="icon_password fas fa-eye-slash" onclick="togglecPassword()"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 2</span>
                            <span>กรอกข้อมูลส่วนบุคคล</span>
                        </div>
                        <div class="form_body">
                            <div class="col">
                                <div class="input_box">
                                    <span>คำนำหน้า</span>
                                    <div class="select">
                                        <select name="pre" required>
                                            <option value="" disabled selected>เลือกคำนำหน้า</option>
                                            <option value="นาย">นาย</option>
                                            <option value="นาง">นาง</option>
                                            <option value="นางสาว">นางสาว</option>
                                            <option value="ดร.">ดร.</option>
                                            <option value="ผศ.ดร.">ผศ.ดร.</option>
                                            <option value="อ.">อ.</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box">
                                    <span>ชื่อ</span>
                                    <input type="text" placeholder="ชื่อภาษาไทย" name="firstname" required>
                                </div>
                                <div class="input_box">
                                    <span>นามสกุล</span>
                                    <input type="text" placeholder="นามสกุลภาษาไทย" name="lastname" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box">
                                    <span>ตำแหน่ง</span>
                                    <div class="select">
                                        <select name="role" required>
                                            <option value="" disabled selected>เลือกตำแหน่ง</option>
                                            <option value="อาจารย์">อาจารย์</option>
                                            <option value="บุคลากร">บุคลากร</option>
                                            <option value="เจ้าหน้าที่">เจ้าหน้าที่</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box">
                                    <span>หน่วยงาน</span>
                                    <input type="text" placeholder="หน่วยงาน" name="agency" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box">
                                    <span>เบอร์โทรศัพท์</span>
                                    <input type="text" placeholder="000-000-0000" name="phone_number" required>
                                </div>
                                <div class="input_box">
                                    <span>E-Mail</span>
                                    <input type="email" placeholder="example@example.com" name="email" required>
                                </div>
                            </div>
                            <div class="btn_section_sign_up">
                                <button type="submit" class="submit" name="signup">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>ยืนยัน</span>
                                </button>
                                <a href="/sign_in" class="cancel">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    <span>ยกเลิก</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <script src="<?php echo $base_url ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url ?>/assets/js/noti_toast.js"></script>
    <script src="<?php echo $base_url ?>/assets/js/show_password.js"></script>
</body>

</html>