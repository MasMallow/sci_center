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
                            <div class="input-box">
                                <span>ชื่อผู้ใช้</span>
                                <input type="text" class="" placeholder="กรุณากรอกชื่อผู้ใช้ (Username)" name="Username" require autofocus>
                                <span class="description"><b>Note : </b>Username ต้องมีความยาวระหว่าง 6 ถึง 12 ตัวอักษร</span>
                            </div>
                            <div class="line"></div>
                            <div class="input-box">
                                <span>รหัสผ่าน</span>
                                <input type="password" class="" placeholder="กรุณากรอกรหัสผ่าน (Password)" name="Password" require>
                                <span class="description">
                                    <b>Note : </b>รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร<br>
                                    <b>Note : </b>รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว
                                </span>
                            </div>
                            <div class="line"></div>
                            <div class="input-box">
                                <span>ยืนยันรหัสผ่านอีกครั้ง</span>
                                <input type="password" class="" placeholder="กรุณายืนยันรหัสผ่าน" name="ConfirmPassword" require>
                            </div>
                            <div class="register_page_footer">
                                <a href="#2" class="btn-next"><span>ถัดไป</span></a>
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
                                <div class="input-box">
                                    <span for="">คำนำหน้า</span>
                                    <div class="select">
                                        <select name="pre">
                                            <option value="" disabled selected>เลือกคำนำหน้า</option>
                                            <option value="นาย">นาย</option>
                                            <option value="นาง">นาง</option>
                                            <option value="นางสาว">นางสาว</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-box">
                                    <span for="">ชื่อ</span>
                                    <input type="text" class="" placeholder="ชื่อภาษาไทย" name="Firstname">
                                </div>
                                <div class="input-box">
                                    <span for="">นามสกุล</span>
                                    <input type="text" class="" placeholder="นามสกุลภาษาไทย" name="Lastname">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-box">
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
                                <div class="input-box">
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
                                <div class="input-box">
                                    <span for="">เบอร์โทรศัพท์</span>
                                    <input type="text" class="" placeholder="เช่น 0999999999" name="Numberphone">
                                </div>
                                <div class="input-box">
                                    <span for="">Line ID</span>
                                    <input type="text" class="" placeholder="เช่นเบอร์โทรศัพท์" name="Lineid">
                                </div>
                            </div>
                            <div class="register_page_footer">
                                <a href="#1" class="btn-prev">ก่อนหน้า</a>
                            </div>
                            <div class="button">
                                <div class="button-1">
                                    <button type="submit" class="submit" name="signup">ยืนยัน</button>
                                </div>
                                <div class="button-2">
                                    <button type="reset" class="reset">เคลียร์</button>
                                    <a href="sign_in.php">ยกเลิก</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>

</html>