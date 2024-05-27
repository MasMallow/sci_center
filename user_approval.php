<?php
session_start();
include_once 'assets/database/connect.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ดึงข้อมูลผู้ใช้หากเข้าสู่ระบบ
if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    // ตั้งค่า user_id ตาม session ที่มี
    $user_id = $_SESSION['user_login'] ?? $_SESSION['staff_login']; // ใช้ null coalescing operator

    // เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ดึงข้อมูลผู้ใช้
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval_user'])) {
    // รับค่า ID ของรายการที่กดยืนยัน
    $user_id = $_POST['user_id'];

    // รหัสผู้ดูแลระบบที่กำลังเข้าสู่ระบบ
    $staff_id = $_SESSION['staff_login'];

    // เลือกชื่อผู้ดูแลระบบจากฐานข้อมูล
    $user_query = $conn->prepare("SELECT pre, surname, lastname FROM users WHERE user_id = :staff_id");
    $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    $user_query->execute();
    $staff_name = $user_query->fetch(PDO::FETCH_ASSOC);
    $approver = $staff_name['pre'] . $staff_name['surname'] . ' ' . $staff_name['lastname'];

    $status = 'approved'; // แก้ไขเครื่องหมายคำพูดให้ถูกต้อง

    // วันเวลาปัจจุบัน
    date_default_timezone_set('Asia/Bangkok');
    $approvalDateTime = date('Y-m-d H:i:s');

    // อัปเดตฐานข้อมูล
    $update_status_user = $conn->prepare("UPDATE users SET status = :status, approved_by = :approved_by, approved_date = :approved_date WHERE user_id = :user_id");
    $update_status_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $update_status_user->bindParam(':status', $status, PDO::PARAM_STR); // แก้ชื่อตัวแปร
    $update_status_user->bindParam(':approved_by', $approver, PDO::PARAM_STR);
    $update_status_user->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR); // เพิ่มการผูกพารามิเตอร์
    $update_status_user->execute(); // แก้ชื่อตัวแปร

    // ส่งกลับไปยังหน้าเดิมหลังจากการอัปเดต
    header('Location: user_approval');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved'");
    $stmt->execute();
    $num = $stmt->rowCount(); // เพิ่มบรรทัดนี้เพื่อนับจำนวนผู้ใช้ที่รอการอนุมัติ
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // แก้ไขการจัดการข้อผิดพลาดนี้ตามความเหมาะสม
    exit;
}

include_once 'includes/thai_date_time.php'; // เปลี่ยนเป็น include_once และลบวงเล็บ
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติผู้สร้างบัญชี</title>

    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/user_approval.css">
</head>

<body>
    <header>
        <?php include('includes/header.php') ?>
    </header>
    <main>
        <section class="user_approve_section">
            <div class="user_approve_header_section">
                <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">อนุมัติผู้สร้างบัญชี</span>
                    </div>
            <div class="user_approve_section_body">
                <div class="user_approve_data_header">
                    <span>จำนวนบัญชีที่รออนุมัติ <span id="B"><?php echo $num; ?></span> รายการ</span>
                </div>
                <table class="user_approve_data">
                    <thead>
                        <tr>
                            <th class="UID"><span id="B">UID</span></th>
                            <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                            <th class="role"><span id="B">ตำแหน่ง</span></th>
                            <th class="agency"><span id="B">สังกัด</span></th>
                            <th class="phone_number"><span id="B">เบอร์โทรศัพท์</span></th>
                            <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                            <th class="urole"><span id="B">ประเภท</span></th>
                            <th class="status"><span id="B">สถานะ</span></th>
                            <th class="operation"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td class="UID"><?php echo $user['user_id']; ?></td>
                                <td><?php echo $user['pre'] . $user['surname'] . " " . $user['lastname']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td><?php echo $user['agency']; ?></td>
                                <td><?php echo format_phone_number($user['phone_number']); ?></td>
                                <td><?php echo thai_date_time($user['created_at']); ?></td>
                                <td>
                                    <?php
                                    if ($user['urole'] == 'user') {
                                        echo 'ผู้ใช้งานทั่วไป';
                                    } elseif ($user['urole'] == 'staff') {
                                        echo 'เจ้าหน้าที่';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($user['status'] == 'wait_approved') {
                                        echo 'รอการอนุมัติ';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="user_approval">
                                        <!-- ส่ง user_id ไปยัง server เพื่อให้ระบบทราบว่าเป็น user คนไหน -->
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

                                        <div class="btn_appr_section">
                                            <!-- ปุ่มสำหรับอนุมัติผู้ใช้ -->
                                            <div class="btn_appr_section">
                                                <button type="submit" class="approval_user" name="approval_user">
                                                    <!-- ใช้ไอคอนแสดงการอนุมัติ -->
                                                    <i class="fa-regular fa-circle-check"></i>
                                                    <!-- เพิ่มคำอธิบายสำหรับปุ่มอนุมัติ -->
                                                </button>
                                            </div>
                                            <!-- ปุ่มสำหรับไม่อนุมัติผู้ใช้ -->
                                            <div class="btn_appr_section">
                                                <button type="submit" class="not_approval_user" name="not_approval_user">
                                                    <!-- ใช้ไอคอนแสดงการไม่อนุมัติ -->
                                                    <i class="fa-regular fa-circle-xmark"></i>
                                                    <!-- เพิ่มคำอธิบายสำหรับปุ่มไม่อนุมัติ -->
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>

</html>