<?php
session_start();
require_once 'db.php';
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
        <link rel="stylesheet" href="login.css">
    </head>

    <body>
        <form action="signin-db.php" method="post">
            <div class="page">
                <div class="madal">
                    <div class="login-page">
                        <div class="header">
                            <h2>กรุณาเข้าสู่ระบบเพื่อเข้าใช้งาน</h2>
                        </div>
                        <?php if (isset($_SESSION['error'])) { ?>
                            <div class="alert" role="alert">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php } ?>
                        <?php if (isset($_SESSION['success'])) { ?>
                            <div class="alert alert-success text-center" role="alert">
                                <?php
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php } ?>
                        <br>

                        <!-- ส่วนการใส่ข้อมูล -->
                        <div class="info">
                            <div>
                                <input type="text" name="Username" class="input" placeholder="USERNAME">
                            </div>
                            <div>
                                <input type="password" name="Password" class="input" placeholder="PASSWORD">
                            </div>

                            <!-- ส่วนของ buttom -->
                            <button class="sign-in" name="sign-in">
                                เข้าสู่ระบบ
                            </button>
                            <label class="not-remember"><a href="#">ลืมรหัสผ่าน?</a></label>
                            <p class="sign-up">ไม่มีบัญชี ? <a href="Register.php" class="text-blue-600">สมัครบัญชีใหม่</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </body>

    </html>
<?php
} elseif (isset($_SESSION['user_login'])) {
    header('location:ajax.php');
} elseif (isset($_SESSION['admin_login'])) {
    header('location:ajax.php');
}
?>