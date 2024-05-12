<?php
session_start();
require_once 'assets/database/connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.2/dist/css/splide.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="ajax.js"></script>
    <title>SCICENTER Management</title>
</head>

<body>

    <?php
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (isset($_SESSION['staff_login'])) {
        $user_id = $_SESSION['staff_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>

    <?php
    include_once('includes/header.php');
    ?>
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
                                <a onclick="log()">
                                    <i class="icon fa-solid fa-square-check"></i>
                                    <span class="text">ตรวจสอบการขอใช้</span>
                                </a>
                            </li>
                            <li>
                                <a onclick="booking()">
                                    <i class="icon fa-solid fa-square-check"></i>
                                    <span class="text">ตรวจสอบการจอง</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <?php
        if (isset($userData['urole']) && $userData['urole'] == 'staff') {
            include('staff/index.php');
        } else {
            if (!isset($_GET['page']) || empty($_GET['page'])) {
                include('MET/index.php');
            } elseif ($_GET['page'] == 'material') {
                include('material/index.php');
            } elseif ($_GET['page'] == 'equipment') {
                include('equipment/index.php');
            } elseif ($_GET['page'] == 'tools') {
                include('tools/index.php');
            }
        }
        ?>
    </main>
    <?php
    include_once('includes/footer.php');
    ?>
</body>

<!-- JavaScript -->
<script src="assets/js/ajax.js"></script>
<script src="assets/js/datetime.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
<script>
    function Return() {
        $.ajax({
            url: "Return.php",
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