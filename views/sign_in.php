<?php
session_start();
if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login']) && !isset($_SESSION['staff_login'])) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ</title>
        <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
        <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
        <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/login.css">
        <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    </head>

    <body>
        <main class="sign_in_box">
            <?php if (isset($_SESSION['successSign_up'])) : ?>
                <div class="toast">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text"><?php echo $_SESSION['successSign_up']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                    </div>
                </div>
                <?php unset($_SESSION['successSign_up']); ?>
            <?php endif ?>
            <?php if (isset($_SESSION['errorLogin'])) : ?>
                <div class="toast">
                    <div class="toast_content error">
                        <i class="fas fa-solid fa-xmark check error"></i>
                        <div class="toast_content_message">
                            <span class="text error"><?php echo $_SESSION['errorLogin']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                    </div>
                </div>
                <?php unset($_SESSION['errorLogin']); ?>
            <?php endif ?>
            <section class="box_layout">
                <div class="box_content_logo">
                    <img src="<?php echo $base_url; ?>/assets/img/logo/sci_center.png">
                </div>
                <div class="box_content">
                    <form action="<?php echo $base_url; ?>/models/sign_inDB.php" id="sign-in-form" method="POST">
                        <div class="box_content_header">
                            <span id="B">เข้าสู่ระบบ</span>
                        </div>
                        <div class="box_content_content">
                            <div class="box_content_input">
                                <label for="username">Username</label>
                                <input type="text" class="input" placeholder="ชื่อผู้ใช้" name="username" autofocus>
                            </div>
                            <div class="box_content_input">
                                <label for="password">Password</label>
                                <div class="show_password">
                                    <input type="Password" class="input" id="password" placeholder="รหัสผ่าน" name="password">
                                    <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                                </div>
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
                                    <a href="/changePassword">ลืมรหัสผ่าน?</a>
                                </div>
                                <div class="sign_up">
                                    <a href="/sign_up">
                                        <span>สมัครสมาชิก</span>
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
            </section>
        </main>
    </body>
    <script src="<?php echo $base_url; ?>/assets/js/show_password.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/noti_toast.js"></script>
    </html>
<?php
} else {
    header('location: /');
}
?>