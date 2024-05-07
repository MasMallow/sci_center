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
        $userCategories = $_SESSION['user_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE user_id =$userCategories");
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (isset($_SESSION['admin_login'])) {
        $userCategories = $_SESSION['admin_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE user_id =$userCategories");
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>

    <?php
    include_once('includes/header.php');
    ?>
    <main class="content">
        <div class="content_sidebar">
            <div class="head">
                <div class="user-details">
                    <p class="title"></p>
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
                    <!-- <li>
                        <a>
                            <i class="icon fa-solid fa-screwdriver-wrench"></i>
                            <span class="text">การบำรุงรักษา</span>
                        </a>
                    </li> -->
                    <li>
                        <a onclick="loadReport(); clearChangeContent(); changeButtonBackground(this);">
                            <i class="icon fa-solid fa-flag"></i>
                            <span class="text">รายงาน</span>
                        </a>
                    </li>
                    <?php
                    // ตรวจสอบว่ามี session ของผู้ใช้ที่ล็อกอินหรือไม่
                    if (isset($_SESSION['admin_login'])) {
                        // ถ้ามี session ของผู้ใช้ (ล็อกอินอยู่) ให้แสดงปุ่มออกจากระบบ
                        echo '<li>
                        <a class="link">
                            <i class="fa-solid fa-user-tie"></i></i>
                            <span class="text">สำหรับผู้ดูแล</span>
                            <i class="ardata fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="sb-sub-ul">
                            <li>
                                <a href="manage_users.php">
                                    <i class="fa-solid fa-user-gear"></i>
                                    <span class="text">แก้ไขชื่อผู้ใช้</span>
                                </a>
                            </li>
                            <li>
                                <a href="crud/add-remove-update.php">
                                    <i class="fa-solid fa-plus-minus"></i>
                                    <span class="text">เพิ่ม / ลบ /แก้ไข</span>
                                </a>
                            </li>
                            <li>
                                <a href="view_report.php">
                                    <i class="icon fa-solid fa-square-check"></i>
                                    <span class="text">ดูประวัติการใช้งาน</span>
                                </a>
                            </li>
                            <li>
                                <a href="approval.php">
                                    <i class="icon fa-solid fa-square-check"></i>
                                    <span class="text">การอนุมัติ</span>
                                </a>
                            </li>
                        </ul>
                    </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        if (!isset($_GET['page']) && empty($_GET['page'])) {
            include('MET/index.php');
        } elseif (isset($_GET['page']) && $_GET['page'] == 'material') {
            include('material/index.php');
        } elseif (isset($_GET['page']) && $_GET['page'] == 'equipment') {
            include('equipment/index.php');
        } elseif (isset($_GET['page']) && $_GET['page'] == 'tools') {
            include('tools/index.php');
        }
        ?>
    </main>
    <footer>
        <div class="container_1">
            <div class="footer about">
                <h2>ศูนย์วิทยาศาสตร์</h2>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus, odit magni odio eaque natus porro rerum neque quaerat corrupti delectus consectetur placeat eveniet illum? Nobis architecto maiores obcaecati tempore iusto.</p>

                <ul class="footer-about1">
                    <li class="footer-about2"><a href=""><i class="fa-brands fa-facebook"></i>เพจมหาวิทยาลัย</a>
                    </li>
                </ul>
            </div>
            <div class="footer-link">
                <h2>เมนูต่าง ๆ</h2>
                <ul>
                    <li><a onclick="location.reload();">หน้าหลัก</a></li>
                    <li><a onclick="log()">รายการตรวจสอบ</a></li>
                    <li><a>การบำรุงรักษา</a></li>
                    <li><a onclick="loadReport(); clearChangeContent(); changeButtonBackground(this);">รายงาน</a>
                    </li>
                </ul>
            </div>
            <div class="footer-link">
                <h2>หมวดหมู่ต่าง ๆ</h2>
                <ul>
                    <li><a onclick="category(this);">หมวดวัสดุ</a></li>
                    <li><a onclick="equipment(this);">หมวดอุปกรณ์</a></li>
                    <li><a onclick="tool(this);">หมวดเครื่องมือ</a></li>
                </ul>
            </div>
            <!-- <div class="footer-map">
                <h2>แผนที่</h2>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2304.5470977317045!2d100.48893255781918!3d13.732322577161767!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e298fe8dcd0d13%3A0x8166225c8081ce3a!2z4Lih4Lir4Liy4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4Lij4Liy4LiK4Lig4Lix4LiP4Lia4LmJ4Liy4LiZ4Liq4Lih4LmA4LiU4LmH4LiI4LmA4LiI4LmJ4Liy4Lie4Lij4Liw4Lii4Liy!5e0!3m2!1sth!2sth!4v1697617426190!5m2!1sth!2sth" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div> -->
        </div>
    </footer>
    <div class="copyright">
        <p>Copyright ©2023 Puwadech and Phisitphong. All Rights Reserved</p>
    </div>
</body>


<!-- JavaScript -->
<script src="assets/js/ajax.js"></script>
<script src="assets/js/datetime.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.2/dist/js/splide.min.js"></script>
<script>
    function category(selectElement) { // เพิ่มพารามิเตอร์ selectElement
        var selectedValue = selectElement.value;
        clearChangeContent();
        $.ajax({
            url: "bordata.php", // ระบุพาธไปยังสคริปต์ PHP ที่จะประมวลผลข้อมูล
            dataType: "html", // รูปแบบข้อมูลที่จะโหลด (HTML)
            success: function(data) {
                $(".product").empty().append(data); // แทนที่เนื้อหา .change ด้วยข้อมูลที่โหลด
            },
            error: function() {
                alert("การโหลดข้อมูลผิดพลาด");
            },
        });
    }
</script>

<script>
    function resetSelect() {
        document.getElementById("mySelect").value = "0"; // Set the value to the default option value
    }
</script>
<script>
    function searchProducts() {
        var searchQuery = document.getElementById('searchInput').value;
        // Perform the search using AJAX
        $.ajax({
            url: "search_bordata.php", // Replace with the actual PHP script handling the search
            type: "GET",
            data: {
                search: searchQuery
            },
            success: function(data) {
                // Update the content with the search results
                $(".bordata").empty().append(data);
            },
            error: function() {
                alert("การค้นหาผิดพลาด");
            }
        });
    }
</script>
<!-- partial -->
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