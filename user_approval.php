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
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ดึงข้อมูลผู้ใช้
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval_user'])) {
    // รับค่า ID ของรายการที่กดยืนยัน
    $user_id = $_POST['id'];

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
    header('Location: approve_for_use.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน</title>

    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/user_approval.css">
</head>

<body>
    <?php include('includes/header.php') ?>
    <div class="user_approve">
        <?php
        $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved'"); // แก้เครื่องหมายเปรียบเทียบ
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) : ?>
            <div class="user_approve_section">
                <div class="user_approve_table">
                    <table class="user_approve_data">
                        <thead>
                            <tr>
                                <th><span>UID</span></th>
                                <th><span>ชื่อ - นามสกุล</span></th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>Line ID</th>
                                <th>ตำแหน่ง</th>
                                <th>สังกัด</th>
                                <th>ประเภท</th>
                                <th>สมัครบัญชีเมื่อ</th>
                                <th>สถานะ</th>
                                <th>ดำเนินการ</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo $user['pre'] . $user['surname'] . $user['lastname']; ?></td>
                                <td><?php echo $user['phone_number']; ?></td>
                                <td><?php echo $user['lineid']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td><?php echo $user['agency']; ?></td>
                                <td><?php echo $user['urole']; ?></td>
                                <td><?php echo $user['created_at']; ?></td>
                                <td><?php echo $user['status']; ?></td>
                                <td>
                                    <form method="POST" action="approve_for_use.php">
                                        <input type="hidden" name="id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="approval_user">ยืนยัน</button>
                                    </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <span>UID</span> <?php echo $user['user_id']; ?><br>
                <span>First Name:</span> <?php echo $user['pre']; ?><br>
                <span>Item Borrowed:</span> <?php echo $user['surname']; ?><br>
                <span>Borrow DateTime:</span> <?php echo $user['lastname']; ?><br>
                <span>Return Date:</span> <?php echo $user['phone_number']; ?><br>
                <span>Return Date:</span> <?php echo $user['lineid']; ?><br>
                <span>Return Date:</span> <?php echo $user['role']; ?><br>
                <span>Return Date:</span> <?php echo $user['agency']; ?><br>
                <span>Return Date:</span> <?php echo $user['urole']; ?><br>
                <span>Return Date:</span> <?php echo $user['created_at']; ?><br>
                <span>Return Date:</span> <?php echo $user['status']; ?><br>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>