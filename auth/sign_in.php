<?php
session_start();
require_once '../assets/database/connect.php';
if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login'])) {
?>
    <div class="alert alert-danger" role="alert">
        <?php
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        ?>
    </div>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ</title>
        <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
        <link rel="stylesheet" href="../assets/css/login.css">
    </head>

    <body>
        <main class="sign_in_box">
            <div class="box_content">
                <form action="../authProcess/sign_inDB.php" method="POST">
                    <div class="box_content_header">
                        <span id="B">เข้าสู่ระบบ</span>
                    </div>
                    <div class="box_content_logo">
                        <img src="../assets/logo/scicenter_logo.png">
                    </div>
                    <?php if (isset($_SESSION['errorLogin'])) { ?>
                        <div class="error">
                            <?php
                            echo $_SESSION['errorLogin'];
                            unset($_SESSION['errorLogin']);
                            ?>
                        </div>
                    <?php } ?>
                    <div class="box_content_content">
                        <input type="text" class="input" placeholder="ชื่อผู้ใช้" name="Username" autofocus>
                        <div class="show-password">
                            <input type="Password" class="input" id="password" placeholder="รหัสผ่าน" name="Password">
                            <i class="icon-password fas fa-eye-slash" onclick="togglePassword()"></i>
                        </div>
                        <div class="box_content_btn">
                            <button class="sign-in" name="sign-in">เข้าสู่ระบบ</button>
                        </div>
                        <div class="box_content_other">
                            <div class="not_remember">
                                <span><a href="#">ลืมรหัสผ่าน?</a></label>
                            </div>
                            <div class="sign_up">
                                <span>ไม่มีบัญชี ?</span>
                                <a href="sign_up.php"><span>สมัครสมาชิก</span>
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="footer">
                        <p>
                            บริหารจัดการโดย แผนกกรรมวิธีข้อมูล กองสารบรรณ <br> กรมสารบรรณทหารเรือ
                        </p>
                    </div> -->
                </form>
            </div>
        </main>
    </body>
    <script>
        /**
         * ฟังก์ชัน togglePassword ใช้สำหรับเปิด/ปิดการแสดงข้อมูลในช่องรหัสผ่าน
         */
        function togglePassword() {
            // เลือก element ของช่องรหัสผ่าน
            const passwordField = document.getElementById("password");
            // เลือก icon ที่ใช้สำหรับแสดง/ซ่อน รหัสผ่าน
            const icon = document.querySelector(".icon-password");

            // ตรวจสอบว่าช่องรหัสผ่านมีการแสดงข้อมูลอยู่หรือไม่
            if (passwordField.type === "password") {
                // ถ้าใช่ ก็เปลี่ยนเป็นการแสดงข้อมูล
                passwordField.type = "text";
                // เปลี่ยน icon เพื่อแสดงสถานะการแสดงข้อมูล
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                // ถ้าไม่ใช่ ก็เปลี่ยนเป็นการซ่อนข้อมูล
                passwordField.type = "password";
                // เปลี่ยน icon เพื่อแสดงสถานะการซ่อนข้อมูล
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>

    </html>


<?php
} elseif (isset($_SESSION['user_login'])) {
    header('location:../index.php');
} elseif (isset($_SESSION['admin_login'])) {
    header('location:../index.php');
}
?>