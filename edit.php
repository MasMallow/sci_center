<?php
session_start();
require_once 'assets/database/dbConfig.php';

if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // สร้างคำสั่ง SQL
    $sql = "SELECT * FROM users_db WHERE user_ID = :user_id";

    // เตรียมและ execute คำสั่ง SQL
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ดึงข้อมูล
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขบัญชีผู้ใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/edit_profile.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
</head>

<body>
    <?php
    include_once('includes/header.php');
    ?>
    <!-- <div class="edit_profile_status">
        <div class="edit_profile_status_content">
            <div class="edit_profile_header_status">
                <span id="B">แจ้งเตือน</span>
                <div class="modalClose" id="close"><i class="fa-solid fa-xmark"></i></div>
            </div>
            <div class="edit_profile_header_body">
                <div class="edit_profile_header_body_error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="edit_profile_header_body_2">
                    <span id="B">!! เกิดข้อผิดพลาด แก้ไขบัญชีผู้ใช้ไม่สำเร็จ !!</span>
                </div>
            </div>
        </div>
    </div> -->

    <script src="../assets/js/show_password.js"></script>
</body>

</html>