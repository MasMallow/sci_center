<?php
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['new_password']) && isset($_POST['newc_password'])) {
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];
        $newc_password = $_POST['newc_password'];

        if ($new_password !== $newc_password) {
            $message = "รหัสผ่านไม่ตรงกัน กรุณาลองใหม่อีกครั้ง";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            try {
                // อัพเดตรหัสผ่านใหม่
                $stmt = $conn->prepare("UPDATE users_db SET password = :password WHERE email = :email");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $message = "รหัสผ่านของคุณถูกเปลี่ยนแล้ว";
                } else {
                    $message = "ไม่พบอีเมลนี้ในระบบ";
                }
            } catch (PDOException $e) {
                $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        }
    } else {
        $message = "กรุณากรอกอีเมลและรหัสผ่านใหม่";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>เปลี่ยนรหัสผ่าน</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/login.css">
</head>

<body>
    <main class="resetPassword">
        <section class="resetpasswordPAGE">
            <div class="box_content_logo">
                <img src="<?php echo $base_url; ?>/assets/img/logo/sci_center.png">
            </div>
            <?php if ($message) : ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form class="resetPasswordMain" method="POST">
                <div class="resetPassword_header">
                    <span id="B">เปลี่ยนรหัสผ่าน</span>
                </div>
                <div class="resetPassword_content">
                    <label id="B" for="email">อีเมล</label>
                    <input type="text" id="email" name="email" required>
                    <label id="B" for="new_password">รหัสผ่านใหม่</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <label id="B" for="newc_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="newc_password" name="newc_password" required>
                    <div class="resetPasswordBTN">
                        <button type="submit" class="btn">เปลี่ยนรหัสผ่าน</button>
                        <a href="/sign_in" class="link">เข้าสู่ระบบ</a>
                    </div>
                </div>
                <div class="footer">
                    <p>
                        ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา
                    </p>
                </div>
            </form>
        </section>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>

</html>
</body>

</html>
