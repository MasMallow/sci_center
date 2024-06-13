<?php
session_start();
require_once '../assets/database/dbConfig.php';
include_once '../includes/thai_date_time.php';
include_once '../route/route.php';

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

    $firstname = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE name_user = :firstname ORDER BY created_at DESC");
    $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
    $stmt->execute();
    $notification = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $firstname = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
    $stmt = $conn->prepare("SELECT * FROM logs_usage WHERE authName = :firstname ORDER BY created_at DESC");
    $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
    $stmt->execute();
    $usage_log = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_url); ?>/assets/css/profile_user.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <div class="profile_user">
        <div class="profile_user">
            <div class="profile_user_header">
                <a href="<?php echo htmlspecialchars($base_url); ?>"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">รายละเอียดบัญชีผู้ใช้</span>
                <form>
                    <button type="submit" name="page" value="edit_profile">
                        แก้ไขบัญชีผู้ใช้
                    </button>
                </form>
            </div>
            <?php
            if ($page == 'edit_profile') {
                include 'edit_profile.php';
            } else { ?>
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
                <div class="profile_user_00">
                    <div class="profile_user_01">
                        <div class="profile_user_details">
                            <div class="edit_profile_header">
                                <span id="B">แจ้งเตือน</span>
                            </div>
                            <div class="profile_user_notification_body">
                                <div class="profile_user_notification_stack">
                                    <?php foreach ($notification as $notifications) : ?>
                                        <div class="profile_user_notification_data">
                                            <?php echo htmlspecialchars(($notifications['serial_number'])); ?>
                                            <?php echo htmlspecialchars(($notifications['list_name'])); ?>
                                            <?php echo htmlspecialchars(thai_date_time_2($notifications['reservation_date'])); ?>
                                            <?php echo htmlspecialchars(($notifications['approver'])); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile_user_notification">
                        <div class="profile_user_details">
                            <div class="edit_profile_header">
                                <span id="B">ประวัติการใช้งาน</span>
                            </div>
                            <div class="profile_user_notification_body">
                                <div class="profile_user_notification_stack">
                                    <?php foreach ($usage_log as $usage_logs) : ?>
                                        <div class="profile_user_notification_data">
                                            <?php echo htmlspecialchars(($usage_logs['log_orDers'])); ?>
                                            <?php echo htmlspecialchars(thai_date_time_2($usage_logs['created_at'])); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    <?php }
    ?>
    </div>
    <script src="<?php echo htmlspecialchars($base_url); ?>/assets/js/show_password.js"></script>
</body>

</html>