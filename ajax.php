<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.2/dist/css/splide.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="ajax.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="ajax.js"></script>
    <title>Document</title>
</head>

<body>

    <?php
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE id =$user_id");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (isset($_SESSION['admin_login'])) {
        $user_id = $_SESSION['admin_login'];
        $stmt = $conn->query("SELECT * FROM users WHERE id =$user_id");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>

    <div class="mainpage">
        <div class="sidebar">
            <div class="head">
                <div class="user-details">
                    <p class="title"></p>
                </div>
            </div>
            <div class="menu">
                <ul class="sb-ul">
                    <li>
                        <a class="link" onclick="location.reload();"><i class="icon fa-solid fa-house"></i>
                            <span class="text">หน้าหลัก</span>
                        </a>
                    </li>
                    <li>
                        <a class="link" href="#">
                            <i class="icon fa-solid fa-bars"></i>
                            <span class="text">หมวดหมู่</span>
                            <i class="arrow fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="sb-sub-ul">
                            <li>
                                <a href="#" onclick="category(this);">
                                    <span class="text">หมวดวัสดุ</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="equipment(this);">
                                    <span class="text">หมวดอุปกรณ์</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="tool(this);">
                                    <span class="text">หมวดเครื่องมือ</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a onclick="log()">
                            <i class="icon fa-solid fa-square-check"></i>
                            <span class="text">รายการตรวจสอบ</span>
                        </a>
                    </li>
                    <li>
                        <a>
                            <i class="icon fa-solid fa-screwdriver-wrench"></i>
                            <span class="text">การบำรุงรักษา</span>
                        </a>
                    </li>
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
                        <a href="#" onclick="AdminMode();">
                            <i class="icon fa-solid fa-flag"></i>
                            <span class="text">สำหรับผู้ดูแล</span>
                        </a>
                    </li>';
                    }
                    ?>

                </ul>
            </div>
        </div>
        <div class="dashborad">
            <!-- แถบบนของ Dashboard -->
            <div id="modalInfo" class="modal" style="display: none;">
                <div class="madol-bg"></div>
                <div class="modal-page">
                    <h2>รายละเอียด</h2>
                    <br>
                    <div class="user-info">
                        <div class="user-dropdown">
                            <p class="username"><?php echo $row['firstname'] ?></p>
                            <a href="logout.php" class="sign-out">ออกจากระบบ</a>
                            <a href="" onclick="closeModal()" class="sign-out">ปิดหน้าต่าง</a>
                        </div>
                    </div>
                </div>
            </div>
            <nav>
                <div class="nav-container">
                    <a href="ajax.php">
                        <span class="head-name">ระบบการจัดการวัสดุอุปกรณ์และเครื่องมือ</span>
                    </a>
                    <div class="nav-profile">
                        <div class="nav-profile-user">

                        </div>
                    </div>
                    <?php
                    // ตรวจสอบว่ามี session ของผู้ใช้ที่ล็อกอินหรือไม่
                    if (isset($_SESSION['user_login'])) {
                        // ถ้ามี session ของผู้ใช้ (ล็อกอินอยู่) ให้แสดงปุ่มออกจากระบบ
                        echo '<div onclick="openInfo()" class="info" style="cursor: pointer;">
                        <img class="profile" src="./test/profile.png" alt="">
                    </div>
                </div>
            </nav>';
                    } elseif (isset($_SESSION['admin_login'])) {
                        echo '<div onclick="openInfo()" class="info" style="cursor: pointer;">
                        <img class="profile" src="./test/profile.png" alt="">
                    </div>
                </div>
            </nav>';
                    } else {
                        // ถ้าไม่มี session ของผู้ใช้ (ไม่ได้ล็อกอิน) ให้แสดงปุ่ม Default
                        echo '<button type="button" class=""><a href="login.php">เข้าสู่ระบบ</a></button></nav>';
                    }
                    ?>

                    <!-- ส่วนแสดงเวลา -->
                    <div class="info-date">
                        <div class="info1-date">
                            <div class="info-time">วันที่&nbsp;</div>
                            <div class="date" id="date"></div>
                            &nbsp;
                            <div>เวลา&nbsp;</div>
                            <div class="time" id="time"></div>
                        </div>
                    </div>
                    <div class="all-info">
                        วัสดุ อุปกรณ์ เครื่องมือ
                    </div>
                    <!-- ส่วนแสดงตาราง -->
                    <div class="product">
                        <div class="display-system">
                            <table class="display-system-table">
                                <thead>
                                    <tr>
                                        <th>รูปภาพ</th>
                                        <th>ชื่อ</th>
                                        <th>ประเภท</th>
                                        <th>จำนวนคงเหลือ</th>
                                        <th>สถานะ</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <?php
                                $query = $db->query("SELECT * FROM crud ORDER BY uploaded_on DESC");
                                $displayedImages = array();
                                $imageCount = 0; // ใช้ตัวแปรนับรูปภาพที่แสดง
                                while ($row = $query->fetch_assoc()) {
                                    $imageURL = 'test/' . $row['file_name'];
                                    // ตรวจสอบว่ารูปภาพนี้เคยถูกแสดงแล้วหรือไม่
                                    if (!in_array($imageURL, $displayedImages)) {
                                        // เพิ่มรูปภาพลงในตัวแปรที่เก็บรายชื่อรูปภาพที่แสดงแล้ว
                                        $displayedImages[] = $imageURL;
                                        $imageCount++; // เพิ่มจำนวนรูปภาพที่แสดงแล้ว
                                ?>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="img">
                                                        <img src="<?php echo $imageURL ?>" alt="">
                                                    </div>
                                                </td>
                                                <td>
                                                    <p><?php echo $row['product_name']; ?></p>
                                                </td>
                                                <td>ตรงนี้เรียกข้อมูลประเภท</td>
                                                <td>
                                                    <p>คงเหลือ : <?php echo $row['amount']; ?></p>
                                                </td>
                                                <td>
                                                    <p>เดี๋ยวทำเป็นสถานะ ใช้คล้ายกับหน้า LOGIN จำนวนเท่าไหน แสดงเท่าไหน</p>
                                                </td>
                                                <td><?php if ($row['amount'] >= 1) {
                                                    ?>
                                                        <div class="button">
                                                            <button onclick="location.href='cart.php?action=add&item=<?= $row['file_name'] ?>'" class="use-it"><i class="icon fa-solid fa-arrow-up"></i>
                                                                <p>ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ</p>
                                                            </button>
                                                        </div>
                                                    <?php } elseif ($row['amount'] <= 0) { ?>
                                                        <div class="button">
                                                            <button class="out-of">
                                                                <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                                                <p>วัสดุ อุปกรณ์ และเครื่องมือ "หมด"</p>
                                                            </button>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                <?php
                                    }
                                    // ตรวจสอบว่าเราได้แสดง 10 รูปภาพแล้ว ถ้าเป็นเช่นนั้นให้ออกจากลูป
                                    if ($imageCount >= 10) {
                                        break;
                                    }
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
        <footer>
            <div class="container_1">
                <div class="footer about">
                    <h2>ศูนย์วิทยาศาสตร์</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla nesciunt nemo, ut quae magni adipisci a error inventore odit aspernatur facilis hic voluptatem tenetur reprehenderit distinctio consequuntur dolorum cupiditate dolor.</p>
                    <ul class="footer-about1">
                        <li class="footer-about2"><a href=""><i class="fa-brands fa-facebook"></i>เพจมหาวิทยาลัย</a></li>
                    </ul>
                </div>
                <div class="footer-link">
                    <h2>เมนูต่าง ๆ</h2>
                    <ul>
                        <li><a onclick="location.reload();">หน้าหลัก</a></li>
                        <li><a onclick="log()">รายการตรวจสอบ</a></li>
                        <li><a>การบำรุงรักษา</a></li>
                        <li><a onclick="loadReport(); clearChangeContent(); changeButtonBackground(this);">รายงาน</a></li>
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
                <div class="footer-map">
                    <h2>แผนที่</h2>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2304.5470977317045!2d100.48893255781918!3d13.732322577161767!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e298fe8dcd0d13%3A0x8166225c8081ce3a!2z4Lih4Lir4Liy4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4Lij4Liy4LiK4Lig4Lix4LiP4Lia4LmJ4Liy4LiZ4Liq4Lih4LmA4LiU4LmH4LiI4LmA4LiI4LmJ4Liy4Lie4Lij4Liw4Lii4Liy!5e0!3m2!1sth!2sth!4v1697617426190!5m2!1sth!2sth" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </footer>
        <div class="copyright">
            <p>Copyright ©2023 Puwadech and Phisitphong. All Rights Reserved</p>
        </div>

        <!-- JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.2/dist/js/splide.min.js"></script>
        <script>
            function updateDateTime() {
                const now = new Date();
                const dateString = now.toLocaleDateString();
                const timeString = now.toLocaleTimeString();
                document.getElementById("date").textContent = dateString;
                document.getElementById("time").textContent = timeString;
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();
        </script>

        <script>
            function category(selectElement) { // เพิ่มพารามิเตอร์ selectElement
                var selectedValue = selectElement.value;
                clearChangeContent();
                $.ajax({
                    url: "borrow.php", // ระบุพาธไปยังสคริปต์ PHP ที่จะประมวลผลข้อมูล
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
                    url: "search_borrow.php", // Replace with the actual PHP script handling the search
                    type: "GET",
                    data: {
                        search: searchQuery
                    },
                    success: function(data) {
                        // Update the content with the search results
                        $(".borrow").empty().append(data);
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
            function tool() {
                $.ajax({
                    url: "tool.php",
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
            function equipment() {
                $.ajax({
                    url: "equipment.php",
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
</body>

</html>