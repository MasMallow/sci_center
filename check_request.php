<?php
session_start();
require_once 'assets/database/connect.php';
include 'includes/thai_date_time.php';


if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] !== 'approved') {
            unset($_SESSION['cart']);
            header("Location: home.php");
            exit();
        }
    }

    $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE udi = :user_id AND situation = 1 AND date_return IS NULL");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบการขอใช้</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/check_request.css">
    <script>
        function confirmReturn(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to mark this item as returned?")) {
                event.target.closest('form').submit();
            }
        }
    </script>
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="header_approve">
        <div class="header_approve_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">คืนอุปกรณ์ และเครื่องมือ</span>
        </div>
    </div>
    <div class="return_content_section">
        <table class="table_maintenace">
            <thead>
                <tr>
                    <th class="serial_number">Serial Number</th>
                    <th class="list">รายการที่ขอใช้งาน</th>
                    <th class="borrowdatetime">วันเวลาที่ขอใช้งาน</th>
                    <th class="returndate">วันเวลาที่สิ้นสุดขอใช้งาน</th>
                    <th class="approver">ผู้อนุมัติ</th>
                    <th class="approvaldatetime">วันเวลาที่อนุมัติ</th>
                    <th class="situation">สถานะ</th>
                    <th class="return_list">คืนรายการที่ขอใช้งาน</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dataList)) : ?>
                    <?php foreach ($dataList as $data) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['sn']); ?></td>
                            <td>
                                <?php
                                $items = explode(',', $data['itemborrowed']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = rtrim($item_parts[1], ')');
                                    echo $product_name . ' ' . $quantity . ' ชิ้น<br>';
                                }
                                ?>
                            </td>
                            <td><?php echo thai_date_time(htmlspecialchars($data['borrowdatetime'])); ?></td>
                            <td><?php echo thai_date_time($data['returndate']); ?></td>
                            <td><?php echo htmlspecialchars($data['approver']); ?></td>
                            <td><?php echo thai_date_time($data['approvaldatetime']); ?></td>
                            <td><?php echo htmlspecialchars($data['situation']); ?></td>
                            <td>
                                <form method="POST" action="check_request_notification">
                                    <input type="hidden" name="return_id" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($data['udi']); ?>">
                                    <button type="submit" onclick="confirmReturn(event)">คืนอุปกรณ์</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11">No data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>