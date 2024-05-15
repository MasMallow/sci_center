<?php
session_start();
include_once 'assets/database/connect.php';
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
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
    header('Location: user_approval.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน</title>
</head>

<body>
    <div class="container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved'"); // แก้เครื่องหมายเปรียบเทียบ
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) : ?>
            <div>
                <span>ID:</span> <?php echo $user['user_id']; ?><br>
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
                <form method="POST" action="user_approval.php">
                    <input type="hidden" name="id" value="<?php echo $user['user_id']; ?>">
                    <button type="submit" name="approval_user">ยืนยัน</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>