<?php
    session_start();
    include_once 'assets/database/connect.php';
    if (!isset($_SESSION['admin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: auth/sign_in.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        // รับค่า ID ของรายการที่กดยืนยัน
        $id = $_POST['id'];
    
        // รหัสผู้ดูแลระบบที่กำลังเข้าสู่ระบบ
        $admin_id = $_SESSION['admin_login'];
    
        // เลือกชื่อผู้ดูแลระบบจากฐานข้อมูล
        $user_query = $conn->prepare("SELECT firstname FROM users WHERE user_id = :admin_id");
        $user_query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $approver = $user['firstname'];
    
        // วันเวลาปัจจุบัน
        $approvalDateTime = date('Y-m-d H:i:s');
    
        // อัปเดตฐานข้อมูล
        $update_query = $conn->prepare("UPDATE waiting_for_approval SET Approver = :approver, ApprovalDateTime = :approvalDateTime WHERE id = :id");
        $update_query->bindParam(':id', $id, PDO::PARAM_INT);
        $update_query->bindParam(':approver', $approver, PDO::PARAM_STR);
        $update_query->bindParam(':approvalDateTime', $approvalDateTime, PDO::PARAM_STR);
        $update_query->execute();
    
        // ส่งกลับไปยังหน้าเดิมหลังจากการอัปเดต
        header('Location: approval.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE ApprovalDateTime IS NULL AND Approver IS NULL");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo $approver = $_SESSION['admin_login'];
        foreach ($data as $row): ?>
        <div class="row">
            <span class="info">ID:</span> <?php echo $row['id']; ?><br>
            <span class="info">First Name:</span> <?php echo $row['FirstName']; ?><br>
            <span class="info">Item Borrowed:</span> <?php echo $row['ItemBorrowed']; ?><br>
            <span class="info">Borrow DateTime:</span> <?php echo $row['BorrowDateTime']; ?><br>
            <span class="info">Return Date:</span> <?php echo $row['ReturnDate']; ?><br>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="submit" name="confirm" value="ยืนยันการยืม">
            </form><br><br>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>