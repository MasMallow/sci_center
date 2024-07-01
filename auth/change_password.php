<?php
// change_password.php
require_once '../assets/database/dbConfig.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['new_password'])) {
        $email = $_POST['email'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

        try {
            // อัพเดตรหัสผ่านใหม่
            $stmt = $conn->prepare("UPDATE users_db SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $new_password);
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
</head>
<body>
    <h2>เปลี่ยนรหัสผ่าน</h2>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="change_password.php" method="POST">
        <label for="email">อีเมล:</label>
        <input type="email" id="email" name="email" required>
        <br><br>
        <label for="new_password">รหัสผ่านใหม่:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br><br>
        <button type="submit">เปลี่ยนรหัสผ่าน</button>
        <a href="/sign_in">เข้าสู่ระบบ</a>
    </form>
</body>
</html>
