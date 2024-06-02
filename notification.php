<?php
session_start();
require_once 'assets/database/connect.php';
include_once 'includes/thai_date_time.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            header("Location: home.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'ผู้ใช้ไม่พบ!';
        header('Location: auth/sign_in.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

$firstname = $userData['surname'];
$stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE firstname = :firstname ORDER BY id");
$stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเตือน</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/nofitication.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">แจ้งเตือน</span>
        </div>
    </div>
    <div class="notification_section">
        <table class="table_notification">
            <thead>
                <tr>
                    <th class="serial_number"><span id="B">หมายเลขรายการ</span></th>
                    <th><span id="B">รายการที่ขอใช้งาน</span></th>
                    <th><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                    <th><span id="B">วันเวลาที่สิ้นสุดขอใช้งาน</span></th>
                    <th><span id="B">สถานะ</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td class="serial_number"><?php echo htmlspecialchars($row['sn']); ?></td>
                        <td>
                            <?php
                            $items = explode(',', $row['itemborrowed']);
                            foreach ($items as $item) {
                                $item_parts = explode('(', $item);
                                $product_name = trim($item_parts[0]);
                                $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : 'ไม่ระบุ';
                                echo htmlspecialchars($product_name) . ' <span>' . ' <span id="B">( ' . htmlspecialchars($quantity) . ' )</span> '  . ' รายการ</span><br>';
                            }
                            ?>
                        </td>
                        <td><?php echo (thai_date_time($row['borrowdatetime'])); ?></td>
                        <td><?php echo (thai_date_time($row['returndate'])); ?></td>
                        <td>
                            <?php
                            echo $row['situation'] === null ? 'ยังไม่ได้รับอนุมัติ' : ($row['situation'] == 1 ? 'ได้รับอนุมัติ' : htmlspecialchars($row['situation']));
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>