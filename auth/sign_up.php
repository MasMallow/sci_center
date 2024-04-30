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
<link rel="stylesheet" href="../assets/css/sign_up.css">

<body>
    <form action="../authProcess/sign_upDB.php" method="post">
        <div class="register">
            <!-- PHP -->
            <div class="register-page">
                <div class="head">
                    <h2>สมัครบัญชีผู้ใช้</h2>
                </div>
                <?php if (isset($_SESSION['error1'])) { ?>
                    <div class="error" role="alert">
                        <?php
                        echo $_SESSION['error1'];
                        unset($_SESSION['error1']);
                        ?>
                    </div>
                <?php } ?>
                <?php if (isset($_SESSION['success'])) { ?>
                    <div class="success" role="alert">
                        <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php } ?>
                <?php if (isset($_SESSION['warning'])) { ?>
                    <div class="warning" role="alert">
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
                        <span>สร้าง usernmae password</span>
                        <div class="input-box">
                            <label>ชื่อผู้ใช้</label>
                            <input type="text" class="" placeholder="กรุณาใส่ชื่อผู้ใช้ (Username)" name="Username" require_once>
                            <label for="" class="description"><b>Note : </b>Username ต้องมีความยาวระหว่าง 6 ถึง 12 ตัวอักษร</label>
                        </div>
                        <div class="line"></div>
                        <div class="input-box">
                            <label>รหัสผ่าน</label>
                            <input type="password" class="" placeholder="กรุณาใส่รหัสผ่าน (Password)" name="Password" require_once>
                            <label for="" class="description">
                                <b>Note : </b>รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร<br>
                                <b>Note : </b>รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว
                            </label>
                        </div>
                        <div class="line"></div>
                        <div class="input-box">
                            <label>ยืนยันรหัสผ่านอีกครั้ง</label>
                            <input type="password" class="" placeholder="กรุณายืนยันรหัสผ่าน" name="ConfirmPassword" require_once>
                        </div>
                        <div class="register_page_footer">
                            <a href="#" class="btn-next">ถัดไป</a>
                        </div>
                    </div>
                    <div class="form">
                        <span>กรอกข้อมูลส่วนตัว</span>
                        <div class="col">
                            <div class="input-box">
                                <label for="">คำนำหน้า</label>
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
                                <label for="">ชื่อ</label>
                                <input type="text" class="" placeholder="ชื่อภาษาไทย" name="Firstname">
                            </div>
                            <div class="input-box">
                                <label for="">นามสกุล</label>
                                <input type="text" class="" placeholder="นามสกุลภาษาไทย" name="Lastname">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-box">
                                <label for="">ตำแหน่ง</label>
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
                                <label for="">หน่วยงาน</label>
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
                                <label for="">เบอร์โทรศัพท์</label>
                                <input type="text" class="" placeholder="เช่น 0999999999" name="Numberphone">
                            </div>
                            <div class="input-box">
                                <label for="">Line ID</label>
                                <input type="text" class="" placeholder="เช่นเบอร์โทรศัพท์" name="Lineid">
                            </div>
                        </div>
                        <div class="register_page_footer">
                            <a href="#" class="btn-prev">ก่อนหน้า</a>
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
    </form>
    <!-- Javascript -->
    <script src="../assets/js/sign_up.js"></script>
</body>

</html>