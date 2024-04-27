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
        <title>Document</title>
        <link rel="stylesheet" href="../assets/css/login.css">
    </head>

    <body>
        <form action="../authProcess/sign_inDB.php" method="post">
            <div class="login">
                <div class="login-page">
                    <div class="header">
                        <h2>กรุณาเข้าสู่ระบบเพื่อเข้าใช้งาน</h2>
                    </div>
                    <?php if (isset($_SESSION['error1'])) { ?>
                        <div class="alert" role="alert">
                            <?php
                            echo $_SESSION['error1'];
                            unset($_SESSION['error1']);
                            ?>
                        </div>
                    <?php } ?>
                    <?php if (isset($_SESSION['error2'])) { ?>
                        <div class="alert" role="alert">
                            <?php
                            echo $_SESSION['error2'];
                            unset($_SESSION['error2']);
                            ?>
                        </div>
                    <?php } ?>
                    <?php if (isset($_SESSION['error3'])) { ?>
                        <div class="alert" role="alert">
                            <?php
                            echo $_SESSION['error3'];
                            unset($_SESSION['error3']);
                            ?>
                        </div>
                    <?php } ?>
                    <br>
                    <!-- ส่วนการใส่ข้อมูล -->
                    <div class="info">
                        <div>
                            <input type="text" name="Username" class="input" placeholder="USERNAME" autofocus>
                        </div>
                        <div>
                            <input type="password" name="Password" class="input" placeholder="PASSWORD">
                        </div>
                        <!-- ส่วนของ buttom -->
                        <div class="line"></div>
                        <button class="sign-in" name="sign-in">
                            เข้าสู่ระบบ
                        </button>
                        <label class="not-remember"><a href="#">ลืมรหัสผ่าน?</a></label>
                        <p class="sign-up">ไม่มีบัญชี ? <a href="sign_up.php">สมัครบัญชีใหม่</a></p>
                    </div>
                </div>
            </div>
        </form>
    </body>

    </html>


<?php
} elseif (isset($_SESSION['user_login'])) {
    header('location:index.php');
} elseif (isset($_SESSION['admin_login'])) {
    header('location:index.php');
}
?>