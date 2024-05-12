<?php
session_start();
include_once 'assets/database/connect.php';
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // รับค่า ID ของรายการที่กดยืนยัน
    $id = $_POST['id'];

    // รหัสผู้ดูแลระบบที่กำลังเข้าสู่ระบบ
    $staff_id = $_SESSION['staff_login'];

    // เลือกชื่อผู้ดูแลระบบจากฐานข้อมูล
    $user_query = $conn->prepare("SELECT surname FROM users WHERE user_id = :staff_id");
    $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $approver = $user['surname'];

    // วันเวลาปัจจุบัน
    $approvaldatetime = date('Y-m-d H:i:s');

    // อัปเดตฐานข้อมูล
    $update_query = $conn->prepare("UPDATE waiting_for_approval SET approver = :approver, approvaldatetime = :approvaldatetime WHERE id = :id");
    $update_query->bindParam(':id', $id, PDO::PARAM_INT);
    $update_query->bindParam(':approver', $approver, PDO::PARAM_STR);
    $update_query->bindParam(':approvaldatetime', $approvaldatetime, PDO::PARAM_STR);
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
        $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE approvaldatetime IS NULL AND approver IS NULL ORDER BY sn");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $previousSn = '';
        $previousFirstname = '';
        ?>
        <div class="container">
            <?php
            foreach ($data as $row) :
                if ($previousSn != $row['sn']) { ?>
                    <div class="row">
                        <span class="info">SN:</span> <?php echo $row['sn']; ?><br>
                        <span class="info">First Name:</span> <?php echo $row['firstname']; ?><br>
                    </div>
                <?php
                    $previousSn = $row['sn'];
                }
                ?>
                <div class="row">
                    <?php
                    // แยกข้อมูล Item Borrowed
                    $items = explode(',', $row['itemborrowed']);

                    // แสดงข้อมูลรายการที่ยืม
                    foreach ($items as $item) {
                        echo "<span class='info'>$item ชิ้น</span><br>";
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>