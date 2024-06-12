<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'includes/thai_date_time.php';

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    $sql = "SELECT * FROM users_db WHERE user_ID = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM logs_user WHERE authID = :user_id ORDER BY log_Date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData_log = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดบัญชีผู้ใช้</title>
    <link href="<?php echo htmlspecialchars($base_url); ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/profile_user.css">
</head>

<body>
    <?php include_once 'includes/header.php'; ?>
    <div class="profile_user">
        <div class="profile_user_header">
            <a href="<?php echo htmlspecialchars($base_url); ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายละเอียดบัญชีผู้ใช้</span>
        </div>
        <div class="profile_user_00">
            <div class="profile_user_01">
                <div class="profile_user_details">
                    <div class="edit_profile_header">
                        <span id="B">รายละเอียด</span>
                    </div>
                    <div class="edit_profile_body">
                        <div class="user_info_row1">
                            <span id="B">ID <span><?php echo htmlspecialchars($userData['user_ID']); ?></span></span>
                            <span id="B">ชื่อ <span><?php echo htmlspecialchars($userData['pre']) . htmlspecialchars($userData['firstname']) . ' ' . htmlspecialchars($userData['lastname']); ?></span></span>
                            <span id="B">เบอร์โทร <span><?php echo htmlspecialchars($userData['phone_number']); ?></span></span>
                            <span id="B">อีเมล <span><?php echo htmlspecialchars($userData['email']); ?></span></span>
                        </div>
                        <div class="user_info_row1">
                            <span id="B"><span><?php echo htmlspecialchars($userData['role']) . ' ' . htmlspecialchars($userData['agency']); ?></span></span>
                            <span id="B">สร้างบัญชีเมื่อ <span><?php echo htmlspecialchars(thai_date_time_2($userData['created_at'])); ?></span></span>
                            <span id="B">Status <span>
                                    <?php if ($userData['status'] === 'wait_approved') : ?>
                                        <span class="wait_approved" id="B">รอการอนุมัติบัญชี</span>
                                    <?php elseif ($userData['status'] === 'approved') : ?>
                                        <span class="approved" id="B">บัญชีผ่านการอนุมัติ</span>
                                    <?php endif; ?>
                                </span></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if (isset($_SESSION['edit_profile_success'])) {
            ?>
                <div class="edit_profile_status">
                    <div class="edit_profile_status_content">
                        <div class="edit_profile_header_status">
                            <span id="B">แจ้งเตือน</span>
                        </div>
                        <div class="edit_profile_header_body">
                            <div class="edit_profile_header_body_1">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="edit_profile_header_body_2">
                                <span id="B">แก้ไขบัญชีผู้ใช้สำเร็จ</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                unset($_SESSION['edit_profile_success']); // Clear session success message
            }
            ?>
            <div class="profile_user_notification">
                <div class="edit_profile_header">
                    <span id="B">ประวัติการใช้งาน</span>
                </div>
                <div class="profile_user_notification_body">
                    <div class="profile_user_notification_stack">
                        <?php foreach ($userData_log as $log_user) : ?>
                            <div class="profile_user_notification_data">
                                <?php echo htmlspecialchars(thai_date_time_2($log_user['log_Date'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="profile_user_02">
                <div class="profile_edit_details">
                    <div class="edit_profile_header">
                        <span id="B">แก้ไขบัญชีผู้ใช้</span>
                    </div>
                    <form class="edit_profile_body" action="process/update_profile.php" method="post">
                        <div class="columnData">
                            <div class="input_edit">
                                <span>รหัสผ่านใหม่</span>
                                <div class="show_password">
                                    <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่านใหม่">
                                    <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                                </div>
                            </div>
                            <div class="input_edit">
                                <span>ยืนยันรหัสผ่าน</span>
                                <div class="show_password">
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่">
                                    <i class="icon_password fas fa-eye-slash" onclick="togglecPassword()"></i>
                                </div>
                            </div>
                        </div>
                        <div class="columnData">
                            <div class="input_edit">
                                <span>คำนำหน้า</span>
                                <select name="pre">
                                    <?php
                                    // Prefixes available
                                    $prefixes = ['นาย', 'นาง', 'นางสาว', 'อ.', 'ผศ.ดร.'];
                                    foreach ($prefixes as $prefix) {
                                        $selected = ($userData['pre'] == $prefix) ? "selected" : "";
                                        echo "<option value='$prefix' $selected>$prefix</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input_edit">
                                <span>ชื่อ</span>
                                <input type="text" name="firstname" value="<?php echo htmlspecialchars($userData['firstname']); ?>">
                            </div>
                            <div class="input_edit">
                                <span>นามสกุล</span>
                                <input type="text" name="lastname" value="<?php echo htmlspecialchars($userData['lastname']); ?>">
                            </div>
                        </div>
                        <div class="columnData">
                            <div class="input_edit">
                                <span>ตำแหน่ง</span>
                                <select name="role">
                                    <?php
                                    $roles = ['อาจารย์', 'บุคลากร', 'เจ้าหน้าที่'];
                                    foreach ($roles as $role) {
                                        $selected = ($userData['role'] == $role) ? "selected" : "";
                                        echo "<option value='$role' $selected>$role</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input_edit">
                                <span>สังกัด</span>
                                <input type="text" name="agency" value="<?php echo htmlspecialchars($userData['agency']); ?>">
                            </div>
                            <div class="input_edit">
                                <span>เบอร์โทรศัพท์</span>
                                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                            </div>
                        </div>
                        <div class="edit_profile_footer">
                            <button type="submit" class="submit">ยืนยัน</button>
                            <a href="<?php echo htmlspecialchars($base_url); ?>" class="cancel">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/show_password.js"></script>
</body>

</html>