<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="en">

<h>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครบัญชีผู้ใช้</title>

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="Register.css">
    <script src="Register.js"></script>

    <body>
        <form action="signup-db.php" method="post">
            <div class="modal">
                <div class="register-page">
                    <div class="head">
                        <h2>สมัครบัญชีผู้ใช้</h2>
                    </div>
                    <div class="form">
                        <?php if (isset($_SESSION['error'])) { ?>
                            <div class="error" role="alert">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
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
                        <div class="input-box">
                            <label>ชื่อผู้ใช้</label>
                            <div class="question-icon" onmouseover="changeText(this, '6-8 ตัวอักษร')" onmouseout="resetText(this)">
                                <i class="bi bi-question-circle "></i>
                                <div class="tooltip"></div>
                            </div>
                            <input type="text" class="" placeholder="กรุณาใส่ชื่อผู้ใช้ (Username)" name="Username" require_once>
                        </div>
                        <div class="input-box">
                            <label>รหัสผ่าน</label>
                            <div class="question-icon" onmouseover="changeText(this, '8-12 ตัวอักษร<br>ตัวอักษรตัวเล็ก (abcd)<br>ตัวอักษรตัวใหญ่ (ABCD)<br>ตัวเลข (1234)')" onmouseout="resetText(this)">
                                <i class="bi bi-question-circle "></i>
                                <div class="tooltip"></div>
                            </div>
                            <input type="password" class="" placeholder="กรุณาใส่รหัสผ่าน (Password)" name="Password" require_once>
                        </div>
                        <div class="input-box">
                            <label>Confirm Password</label>
                            <input type="password" class="" placeholder="กรุณายืนยันรหัสผ่าน (Confirm Password)" name="ConfirmPassword">
                        </div>
                        <div class="col">
                            <div class="input-box">
                                <label for="">ตำแหน่ง</label>
                                <div class="select">
                                    <select>
                                        <option value="" disabled selected>เลือกคำนำหน้า</option>
                                        <option value="อาจารย์">อาจารย์</option>
                                        <option value="บุคคลากร">บุคคลากร</option>
                                        <option value="ผู้บริหาร">ผู้บริหาร</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-box">
                                <label for="">ชื่อ :</label>
                                <input type="text" class="" placeholder=" ชื่อภาษาไทย" name="Firstname">
                            </div>
                            <div class="input-box">
                                <label for="">นามสกุล :</label>
                                <input type="text" class="" placeholder=" นามสกุลภาษาไทย" name="Lastname">
                            </div>
                        </div>
                        <div class="input-box">
                            <label for="">เบอร์โทรศัพท์ :</label>
                            <input type="text" class="" placeholder=" เช่น 0999999999" name="Numberphone">
                        </div>
                        <div class="input-box">
                            <label for="">Line ID :</label>
                            <input type="text" class="" placeholder=" เช่นเบอร์โทรศัพท์" name="Lineid">
                        </div>
                        <div class="button">
                            <button type="submit" class="" name="signup">ยืนยัน</button>
                            <button type="reset" class="">ยกเลิก</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>


        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    </body>

</html>