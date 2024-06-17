<?php
session_start();
require_once 'assets/database/dbConfig.php';
if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login']) && !isset($_SESSION['staff_login'])) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ</title>
        <link href="<?php echo $base_url;?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
        <link rel="stylesheet" href="<?php echo $base_url;?>/assets/font-awesome/css/all.css">
        <link rel="stylesheet" href="<?php echo $base_url;?>/assets/css/login.css">
    </head>

    <body>
        <main class="sign_in_box">
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const toast = document.querySelector(".toast");
                    const closeIcon = document.querySelector(".close");
                    const progress = document.querySelector(".progress");

                    // Add active class to trigger the animation
                    setTimeout(() => {
                        toast.classList.add("active");
                        progress.classList.add("active");
                    }); // Delay slightly to ensure the DOM is ready

                    // Remove active class after a timeout
                    setTimeout(() => {
                        toast.classList.remove("active");
                    }, 5100); // 5s + 100ms delay

                    setTimeout(() => {
                        progress.classList.remove("active");
                    }, 5400); // 5.3s + 100ms delay

                    closeIcon.addEventListener("click", () => {
                        toast.classList.remove("active");
                        setTimeout(() => {
                            progress.classList.remove("active");
                        }, 300);
                    });
                });
            </script>
            <?php if (isset($_SESSION['errorLogin'])) { ?>
                <div class="toast">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['errorLogin']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
                <?php unset($_SESSION['errorLogin']); ?>
            <?php } ?>
            <div class="box_content">
                <form action="<?php echo $base_url;?>/auth/backend/sign_inDB.php" id="sign-in-form" method="POST">
                    <div class="box_content_header">
                        <span id="B">เข้าสู่ระบบ</span>
                    </div>
                    <div class="box_content_logo">
                        <img src="<?php echo $base_url;?>/assets/logo/scicenter_logo.png">
                    </div>
                    <div class="box_content_content">
                        <input type="text" class="input" placeholder="ชื่อผู้ใช้" name="username" autofocus>
                        <div class="show_password">
                            <input type="Password" class="input" id="password" placeholder="รหัสผ่าน" name="password">
                            <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                        </div>
                        <div class="box_content_btn">
                            <input class="sign-in" type="submit" value="เข้าสู่ระบบ" value-after="กำลังเข้าสู่ระบบ..." name="sign_in">
                        </div>
                        <script>
                            document.getElementById('sign-in-form').addEventListener('submit', function(event) {
                                var signInButton = document.querySelector('.sign-in');
                                signInButton.value = signInButton.getAttribute('value-after');
                                // Here you can add code to handle the form submission, e.g., via AJAX.
                                // event.preventDefault(); // Uncomment this line if you don't want the form to submit
                            });
                        </script>
                        <div class="box_content_other">
                            <div class="not_remember">
                                <span><a href="#">ลืมรหัสผ่าน?</a></span>
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
    <script src="<?php echo $base_url;?>/assets/js/show_password.js"></script>
    <script src="<?php echo $base_url;?>/assets/js/noti_toast.js"></script>

    </html>
<?php
} else {
    header('location: $base_url');
}
?>