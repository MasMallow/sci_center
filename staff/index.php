<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Staff PAGE</h1>
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
                if (isset($_SESSION['staff_login'])) {
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
</body>

</html>