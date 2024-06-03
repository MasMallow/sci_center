<?php
session_start();
require_once '../assets/database/connect.php';
if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login']) && !isset($_SESSION['staff_login'])) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ</title>
        <link href="../assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
        <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
        <link rel="stylesheet" href="../assets/css/login.css">
    </head>

    <body>
        <main class="sign_in_box">
            <div class="box_content">
                <form id="loginForm" action="../authProcess/sign_inDB.php" method="POST">
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
                        <input type="text" class="input" placeholder="ชื่อผู้ใช้" name="username" autofocus>
                        <div class="show_password">
                            <input type="Password" class="input" id="password" placeholder="รหัสผ่าน" name="password">
                            <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                        </div>
                        <div class="box_content_btn">
                            <button id="signInButton" class="sign-in" name="sign_in">เข้าสู่ระบบ</button>
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
                    <div class="footer">
                        <p>
                            ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา
                        </p>
                    </div>
                </form>
            </div>
        </main>
    </body>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const icon = document.querySelector(".icon_password");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>

    </html>
<?php
} else {
    header('location:../home.php');
}
?>