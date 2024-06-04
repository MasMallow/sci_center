<?php
session_start();
require_once 'assets/database/connect.php';
?>
<?php
if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == 'not_approved') {
            unset($_SESSION['user_login']);
            header('Location: auth/sign_in.php');
            exit();
        }
    }
}
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCICENTER Management</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <script src="ajax.js"></script>
</head>

<body>
    <?php
    include_once('includes/header.php');
    ?>
    <?php if (isset($userData['urole']) && $userData['urole'] == 'user') : ?>
        <main class="content">
            <div class="content_sidebar">
                <div class="content_sidebar_header">
                    <div class="content_sidebar_header_details">
                        <span>dummy</span>
                    </div>
                </div>
                <div class="menu">
                    <ul class="sb-ul">
                        <li>
                            <a class="link <?php echo !isset($_GET['page']) && empty($_GET['page']) ? 'active ' : '' ?>" href="../project/">
                                <i class="icon fa-solid fa-house"></i>
                                <span class="text">หน้าหลัก</span>
                            </a>
                        </li>
                        <li>
                            <a class="link">
                                <i class="icon fa-solid fa-bars"></i>
                                <span class="text">ประเภท</span>
                                <i class="ardata fa-solid fa-chevron-down"></i>
                            </a>
                            <ul class="sb-sub-ul">
                                <li>
                                    <a class="link <?php echo isset($_GET['page']) && ($_GET['page'] == 'material') ? 'active ' : '' ?>" href="?page=material">
                                        <span class="text">ประเภทวัสดุ</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="link <?php echo isset($_GET['page']) && ($_GET['page'] == 'equipment') ? 'active ' : '' ?>" href="?page=equipment">
                                        <span class="text">ประเภทอุปกรณ์</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="link <?php echo isset($_GET['page']) && ($_GET['page'] == 'tools') ? 'active ' : '' ?>" href="?page=tools">
                                        <span class="text">ประเภทเครื่องมือ</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a class="link">
                                <i class="fa-solid fa-check-to-slot"></i>
                                <span class="text">รายการตรวจสอบ</span>
                                <i class="ardata fa-solid fa-chevron-down"></i>
                            </a>
                            <ul class="sb-sub-ul">
                                <li>
                                    <a href="check_request">
                                        <i class="fa-solid fa-hourglass-end"></i>
                                        <span class="text">สิ้นสุดการขอใช้</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="check_request_bookings">
                                        <i class="fa-solid fa-hourglass-end"></i>
                                        <span class="text">สิ้นสุดการขอใช้</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="booking_log">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <span class="text">ติดตามการจอง</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="bookings_list">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                        <span class="text">ยกเลิกการจอง</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a class="link">
                                <i class="fa-solid fa-envelope"></i>
                                <span class="text">แจ้งเตือน</span>
                            </a>
                            <ul class="sb-sub-ul">
                                <li>
                                    <a href="notification_use">
                                        <i class="fa-solid fa-hourglass-end"></i>
                                        <span class="text">แจ้งเตือนการขอใช้</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="notification_bookings">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <span class="text">แจ้งเตือนการจอง</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
            if (!isset($_GET['page']) || empty($_GET['page'])) {
                include('MET/index.php');
            } elseif ($_GET['page'] == 'material') {
                include('material/index.php');
            } elseif ($_GET['page'] == 'equipment') {
                include('equipment/index.php');
            } elseif ($_GET['page'] == 'tools') {
                include('tools/index.php');
            }
            ?>
        </main>
    <?php endif; ?>

    <?php
    if (isset($userData['urole']) && $userData['urole'] == 'staff') {
        include('staff/index.php');
    }
    ?>
    <?php
    include_once('includes/footer.php');
    ?>
</body>

<!-- JavaScript -->
<script src="assets/js/ajax.js"></script>
<script src="assets/js/details.js"></script>
<script src="assets/js/datetime.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
<script>
    function log() {
        $.ajax({
            url: "viewlog.php",
            dataType: "html",
            success: function(data) {
                $(".product").empty().append(data);
            },
            error: function() {
                alert("การโหลดรายงานผิดพลาด");
            },
        });
    }
</script>
<script>
    function booking() {
        $.ajax({
            url: "bookings_list.php",
            dataType: "html",
            success: function(data) {
                $(".product").empty().append(data);
            },
            error: function() {
                alert("การโหลดรายงานผิดพลาด");
            },
        });
    }
</script>
</body>

</html>