<?php
session_start();
require_once 'assets/database/dbConfig.php';
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

    $sql = "SELECT * FROM approve_to_reserve WHERE userID = :userID ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $log_usage = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page = isset($_GET['page']) ? $_GET['page'] : 'profile';
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/profile_user.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/footer.css">
</head>

<body>
    <header><?php include 'assets/includes/navigator.php'; ?></header>
    <main class="profile_user">
        <div class="profile_user_header">
            <div class="HEADER_USER_1">
                <a href="<?php echo htmlspecialchars($base_url); ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">รายละเอียดบัญชีผู้ใช้</span>
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
        if ($request_uri == '/profile_user/edit_profile') : ?>
            <?php if (isset($_SESSION['edit_profile_success'])) : ?>
                <div class="toast">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-xmark check"></i>
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
            <div class="editProfile">
                <div class="profile_user_details">
                    <div class="edit_profile_header">
                        <span id="B">แก้ไขบัญชีผู้ใช้</span>
                    </div>
                    <form class="edit_profile_body" action="<?php echo $base_url; ?>/SystemsUser/update_profile.php" method="post">
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
                        <div class="edit_profile_footer">
                            <button type="submit" class="submit">ยืนยัน</button>
                            <a href="<?php echo htmlspecialchars($base_url); ?>" class="cancel">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else : ?>
            <div class="profile_user_00">
                <div class="profile_user_01">
                    <div class="profile_user_details">
                        <div class="edit_profile_header">
                            <span id="B">รายละเอียด</span>
                        </div>
                        <div class="profile_user_content">
                            <div class="edit_profile_body">
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">USERID</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars($userData['userID']); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">ชื่อ</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars($userData['pre']) . htmlspecialchars($userData['firstname']) . ' ' . htmlspecialchars($userData['lastname']); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">เบอร์โทร</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars($userData['phone_number']); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">อีเมล</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars($userData['email']); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">บทบาท</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars($userData['role']) . ' ' . htmlspecialchars($userData['agency']); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">สร้างบัญชีเมื่อ</div>
                                    <div class="user_info_row2"><?php echo htmlspecialchars(thai_date_time_2($userData['created_at'])); ?></div>
                                </div>
                                <div class="user_info_row">
                                    <div class="user_info_row1" id="B">สถานะบัญชี</div>
                                    <div class="user_info_row2">
                                        <?php if ($userData['status'] === 'w_approved') : ?>
                                            <span class="wait_approved">รอการอนุมัติบัญชี</span>
                                        <?php elseif ($userData['status'] === 'approved') : ?>
                                            <span class="approved">บัญชีผ่านการอนุมัติ</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile_user_02">
                    <div class="profile_user_notification">
                        <div class="edit_profile_header">
                            <span id="B">ประวัติการเข้าสู่ระบบ</span>
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
                </div>
            </div>
        <?php endif; ?>
    </main>
    <footer class="small">
        <div class="footer-content">
            <div class="footer-copyright">
                <span>Copyright © 2024 ศูนย์วิทยาศาสตร์</span>
                <span>ออกแบบและพัฒนาโดย ภูวเดช และ พิสิฐพงศ์. All Rights Reserved</span>
            </div>
    </footer>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/show_password.js"></script>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/noti_toast.js"></script>
</body>

</html>