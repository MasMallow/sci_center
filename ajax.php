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
                        <a class="link" href="#" onclick="location.reload();"><i class="icon fa-solid fa-house"></i>
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
                        <a href="#">
                            <i class="icon fa-solid fa-square-check"></i>
                            <span class="text">รายการตรวจสอบ</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="icon fa-solid fa-screwdriver-wrench"></i>
                            <span class="text">การบำรุงรักษา</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="loadReport(); clearChangeContent(); changeButtonBackground(this);">
                            <i class="icon fa-solid fa-flag"></i>
                            <span class="text">รายงาน</span>
                        </a>
                    </li>
                    <?php
                    // ตรวจสอบว่ามี session ของผู้ใช้ที่ล็อกอินหรือไม่
                    if (isset($_SESSION['admin_login'])) {
                        // ถ้ามี session ของผู้ใช้ (ล็อกอินอยู่) ให้แสดงปุ่มออกจากระบบ
                        echo '<li>
                        <a href="#" onclick="clearChangeContent(); AdminMode();">
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
            <nav>
                <div class="nav-container">
                    <a href="ajax.php">
                        <span class="head-name">ระบบการจัดการวัสดุอุปกรณ์และเครื่องมือ000000</span>
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
                    }
                    elseif (isset($_SESSION['admin_login'])) {
                        echo '<div onclick="openInfo()" class="info" style="cursor: pointer;">
                        <img class="profile" src="./test/profile.png" alt="">
                    </div>
                </div>
            </nav>';
                    } 
                    else {
                        // ถ้าไม่มี session ของผู้ใช้ (ไม่ได้ล็อกอิน) ให้แสดงปุ่ม Default
                        echo '<button type="button" class="col-start-11 col-span-2 w-26 m-1 text-white bg-blue-700 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 "><a href="login.php">เข้าสู่ระบบ</a></button></nav>';
                    }
                    ?>

                    <div id="modalInfo" class="modal" style="display: none;">
                        <div onclick="closeModal()" class="madol-bg"></div>
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
                    <div class="product">
                        <div class="borrow grid grid-cols-4">
                            <?php
                            $query = $db->query("SELECT * FROM image ORDER BY uploaded_on DESC");
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
                                    <div class="bg-white border-black rounded-md relative text-center mt-10">
                                        <a href="#" class="flex justify-center">
                                            <img src="<?php echo $imageURL ?>" alt="" class="rounded-md h-40 w-32 m-1">
                                        </a>
                                        <div class="mas p-1">
                                            <a href="#">
                                                <br>
                                                <p>ชื่ออุปกรณ์: <?php echo $row['product_name']; ?></p> <!-- เพิ่มบรรทัดนี้เพื่อแสดงชื่อสินค้า -->
                                                <p>จำนวนคงเหลือ: <?php echo $row['amount']; ?></p> <!-- เพิ่มบรรทัดนี้เพื่อแสดงจำนวนคงเหลือ -->
                                            </a>
                                            <a href="cart.php?action=add&item=<?= $row['file_name'] ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover-bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark-bg-blue-600 dark-hover-bg-blue-700 dark-focus-ring-blue-800">
                                                Add to Cart
                                                <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                            <?php
                                }
                                // ตรวจสอบว่าเราได้แสดง 10 รูปภาพแล้ว ถ้าเป็นเช่นนั้นให้ออกจากลูป
                                if ($imageCount >= 10) {
                                    break;
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

        </div>
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
</body>

</html>