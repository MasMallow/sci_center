<?php
session_start();
require_once '../assets/database/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครบัญชีผู้ใช้</title>

<!-- ส่วน Link -->
<link href="../assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
<link rel="stylesheet" href="../assets/font-awesome/css/all.css">
<link rel="stylesheet" href="../assets/css/sign_up.css">

<body>
    <form action="../authProcess/sign_upDB.php" method="post">
        <div class="register">
            <div class="register_page">
                <div class="register_page_head">
                    <span id="B">สมัครบัญชีผู้ใช้</span>
                </div>
                <?php if (isset($_SESSION['error1'])) { ?>
                    <div class="error">
                        <?php
                        echo $_SESSION['error1'];
                        unset($_SESSION['error1']);
                        ?>
                    </div>
                <?php } ?>
                <?php if (isset($_SESSION['success'])) { ?>
                    <div class="success">
                        <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php } ?>
                <?php if (isset($_SESSION['warning'])) { ?>
                    <div class="warning">
                        <?php
                        echo $_SESSION['warning'];
                        unset($_SESSION['warning']);
                        ?>
                    </div>
                <?php } ?>
                <div class="register_page_body">
                    <div class="pagination">
                        <div class="number active">1</div>
                        <div class="bar"></div>
                        <div class="number">2</div>
                    </div>
                    <div class="form active">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 1</span><br>
                            <span>กรอก USERNAME และ PASSWORD</span>
                        </div>
                        <div class="form_body">
                            <div class="input_box_1">
                                <span>ชื่อผู้ใช้</span>
                                <input type="text" class="" placeholder="กรุณากรอกชื่อผู้ใช้ (Username)" name="username" require autofocus>
                                <span class="description"><b>Note : </b>Username ต้องมีความยาวระหว่าง 6 ถึง 12 ตัวอักษร</span>
                            </div>
                            <div class="input_box_1">
                                <span>รหัสผ่าน</span>
                                <input type="password" class="" placeholder="กรุณากรอกรหัสผ่าน (Password)" name="password" require>
                                <span class="description">
                                    <b>Note : </b>รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร<br>
                                    <b>Note : </b>รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว
                                </span>
                            </div>
                            <div class="input_box_1">
                                <span>ยืนยันรหัสผ่านอีกครั้ง</span>
                                <input type="password" class="" placeholder="กรุณายืนยันรหัสผ่าน" name="confirmpassword" require>
                            </div>
                            <div class="register_page_footer_1">
                                <a href="#2" class="btn_next"><span>ถัดไป</span><i class="fa-solid fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="form">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 2</span><br>
                            <span>กรอกข้อมูลส่วนบุคคล</span>
                        </div>
                        <div class="form_body">
                            <div class="col">
                                <div class="input_box_2">
                                    <span>คำนำหน้า</span>
                                    <div class="select">
                                        <select name="pre">
                                            <option value="" disabled selected>เลือกคำนำหน้า</option>
                                            <option value="นาย">นาย</option>
                                            <option value="นาง">นาง</option>
                                            <option value="นางสาว">นางสาว</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box_2">
                                    <span for="">ชื่อ</span>
                                    <input type="text" class="" placeholder="ชื่อภาษาไทย" name="surname">
                                </div>
                                <div class="input_box_2">
                                    <span for="">นามสกุล</span>
                                    <input type="text" class="" placeholder="นามสกุลภาษาไทย" name="lastname">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box_2">
                                    <span for="">ตำแหน่ง</span>
                                    <div class="select">
                                        <select name="role">
                                            <option value="" disabled selected>เลือกตำแหน่ง</option>
                                            <option value="อาจารย์">อาจารย์</option>
                                            <option value="บุคลากร">บุคลากร</option>
                                            <option value="ผู้บริหาร">ผู้บริหาร</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box_2">
                                    <span for="">หน่วยงาน</span>
                                    <div class="select">
                                        <select name="agency">
                                            <option value="" disabled selected>เลือกหน่วยงาน</option>
                                            <option value="คณะวิทยาสตร์">คณะวิทยาสตร์</option>
                                            <option value="คณะตลก">คณะตลก</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box_2">
                                    <span for="">เบอร์โทรศัพท์</span>
                                    <input type="text" class="" placeholder="เช่น 0999999999" name="phone_number">
                                </div>
                                <div class="input_box_2">
                                    <span for="">Line ID</span>
                                    <input type="text" class="" placeholder="เช่นเบอร์โทรศัพท์" name="line_id">
                                </div>
                            </div>
                            <div class="register_page_footer_2">
                                <a href="#1" class="btn_prev"><i class="fa-solid fa-angle-left"></i><span>ก่อนหน้า</span></a>
                            </div>
                            <div class="btn_section_sign_up">
                                <div class="btn_section_sign_up_1">
                                    <button type="submit" class="submit" name="signup"><i class="fa-solid fa-circle-check"></i><span>ยืนยัน</span></button>
                                </div>
                                <div class="btn_section_sign_up_btn_2">
                                    <button type="reset" class="reset"><i class="fa-solid fa-rotate"></i><span>เคลียร์</span></button>
                                    <a href="sign_in.php" class="cancel"><i class="fa-solid fa-circle-xmark"></i><span>ยกเลิก</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script src="../assets/js/sign_up.js"></script>
</body>

</html>