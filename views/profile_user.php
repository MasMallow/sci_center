<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $userID = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    $sql = "SELECT * FROM users_db WHERE userID = :userID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM logs_user WHERE authID = :userID ORDER BY log_Date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/profile_user.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <main class="profile_user">
        <div class="profile_user_header">
            <div class="HEADER_USER_1">
                <a class="historyBACK" href="<?php echo htmlspecialchars($base_url); ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
                <div class="breadcrumb">
                    <a href="/">หน้าหลัก</a>
                    <span>&gt;</span>
                    <?php
                    if ($request_uri == '/profile_user') {
                        echo '<a href="/profile_user">รายละเอียดบัญชี</a>
                    ';
                    }
                    if ($request_uri == '/profile_user/edit_profile') {
                        echo '<a href="/profile_user/edit_profile">แก้ไขบัญชี</a>
                    ';
                    }
                    ?>
                </div>
            </div>
            <?php
            $request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            if ($request_uri == '/profile_user') : ?>
                <form>
                    <a href="/profile_user/edit_profile">แก้ไขบัญชีผู้ใช้</a>
                </form>
            <?php elseif ($request_uri == '/profile_user/edit_profile') : ?>
                <form>
                    <a href="/profile_user">รายละเอียดบัญชีผู้ใช้</a>
                </form>
            <?php endif; ?>
        </div>
        <?php
        $request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        if ($request_uri == '/profile_user/edit_profile' || $request_uri == '/edit_user') : ?>
            <?php if (isset($_SESSION['edit_profile_success'])) : ?>
                <div class="toast">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-check check"></i>
                            <div class="toast_content_message">
                                <span class="text text_2"><?php echo $_SESSION['edit_profile_success']; ?></span>
                            </div>
                            <i class="fa-solid fa-xmark close"></i>
                            <div class="progress"></div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['edit_profile_success']); ?>
            <?php endif ?>
            <?php
            try {
                // ถ้ามีการส่งค่า GET id มา จะใช้ค่า GET id นี้แทน
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :id");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $profileUSER = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($profileUSER) {
                        $userData = $profileUSER; // ใช้ข้อมูลจากค่า GET id
                    }
                }
            } catch (PDOException $e) {
                // จัดการข้อผิดพลาดที่เกิดจากการเชื่อมต่อฐานข้อมูล
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit();
            }
            ?>
            <div class="editProfile">
                <div class="edit_profile_header">
                    <span id="B">แก้ไขบัญชีผู้ใช้</span>
                </div>
                <form action="<?php echo $base_url; ?>/models/updateProfile.php" method="post">
                    <div class="edit_profile_body">
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
                    </div>
                    <div class="edit_profile_footer">
                        <button type="submit" class="submit">ยืนยัน</button>
                        <a href="<?php echo htmlspecialchars($base_url); ?>" class="cancel">ยกเลิก</a>
                    </div>
                </form>
            </div>
        <?php else : ?>
            <div class="profileUSER">
                <div class="detailsUSER">
                    <div class="profile_userHeader">
                        <span id="B">รายละเอียด</span>
                    </div>
                    <div class="profile_user_content">
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">USERID</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars($userData['userID']); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">ชื่อ</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars($userData['pre']) . htmlspecialchars($userData['firstname']) . ' ' . htmlspecialchars($userData['lastname']); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">เบอร์โทร</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars($userData['phone_number']); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">อีเมล</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars($userData['email']); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">ตำแหน่ง</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars($userData['role']) . ' ' . htmlspecialchars($userData['agency']); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">สร้างบัญชีเมื่อ</span></div>
                            <div class="user_info_value"><?php echo htmlspecialchars(thai_date_time_2($userData['created_at'])); ?></div>
                        </div>
                        <div class="user_info_row">
                            <div class="user_info_label"><span id="B">สถานะบัญชี</span></div>
                            <div class="user_info_value">
                                <?php if ($userData['status'] === 'w_approved') : ?>
                                    <span class="status wait_approved">รอการอนุมัติบัญชี</span>
                                <?php elseif ($userData['status'] === 'approved') : ?>
                                    <span class="status approved">บัญชีผ่านการอนุมัติ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="UserHistoryLogin">
                    <div class="profile_userHeader">
                        <span id="B">ประวัติการเข้าสู่ระบบ</span>
                    </div>
                    <div class="profile_user_notification_body">
                        <?php foreach ($userData_log as $log_user) : ?>
                            <div class="profile_user_notification_stack">
                                <?php echo htmlspecialchars(thai_date_time_2($log_user['log_Date'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <!-- --------------- FOOTER --------------- -->
    <footer>
        <?php include_once 'assets/includes/footer_2.php' ?>
    </footer>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/ajax.js"></script>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/show_password.js"></script>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/noti_toast.js"></script>
</body>

</html>