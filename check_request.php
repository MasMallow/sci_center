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
    <style>
        /* ซ่อนแถวขยายโดยค่าเริ่มต้น */
        .expand_row {
            display: none;
        }

        /* แสดงแถวขยายเมื่อมีคลาส visible */
        .expand_row.visible {
            display: table-row;
        }

        /* กำหนดสไตล์ให้ปุ่มเปิด/ปิดเป็นลิงก์และมีสีฟ้า */
        .open_expand_row {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
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
                    <th class="serial_number"><span id="B">Serial Number</span></th>
                    <th class="list"><span id="B">รายการที่ขอใช้งาน</span></th>
                    <th class="borrowdatetime"><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                    <th class="returndate"><span id="B">วันเวลาที่สิ้นสุดขอใช้งาน</span></th>
                    <th class="return_list"><span id="B">คืนรายการที่ขอใช้งาน</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dataList)) : ?>
                    <?php foreach ($dataList as $data) : ?>
                        <tr>
                            <!-- คอลัมน์สำหรับหมายเลขลำดับและปุ่มเปิด/ปิด -->
                            <td class="serial_number">
                                <!-- ปุ่มเปิด/ปิดแถวขยาย เมื่อคลิกจะเรียกใช้ฟังก์ชัน toggleExpandRow -->
                                <span class="open_expand_row" onclick="toggleExpandRow(this)">เปิด</span>
                                <?php echo htmlspecialchars($data['sn']); ?>
                            </td>
                            <!-- คอลัมน์สำหรับแสดงรายการที่ยืม -->
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
                            <!-- คอลัมน์สำหรับแสดงวันที่ยืม -->
                            <td><?php echo thai_date_time(htmlspecialchars($data['borrowdatetime'])); ?></td>
                            <!-- คอลัมน์สำหรับแสดงวันที่คืน -->
                            <td><?php echo thai_date_time($data['returndate']); ?></td>
                            <!-- คอลัมน์สำหรับปุ่มคืนรายการ -->
                            <td>
                                <form method="POST" action="check_request_notification">
                                    <input type="hidden" name="return_id" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($data['udi']); ?>">
                                    <button type="submit" class="return" onclick="confirmReturn(event)">คืนรายการที่ขอใช้งาน</button>
                                </form>
                            </td>
                        </tr>
                        <!-- แถวขยายสำหรับแสดงข้อมูลผู้อนุมัติและวันที่อนุมัติ -->
                        <tr class="expand_row">
                            <td colspan="5">
                                <div>
                                    <?php echo htmlspecialchars($data['approver']); ?>
                                </div>
                                <div>
                                    <?php echo thai_date_time($data['approvaldatetime']); ?>
                                </div>
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
    <script>
        function toggleExpandRow(element) {
            // หาตัวแถวที่ใกล้ที่สุดกับปุ่มที่ถูกคลิก
            const row = element.closest('tr');
            // หาแถวถัดไปที่เป็นแถวขยาย
            const expandRow = row.nextElementSibling;

            // สลับคลาส visible เพื่อแสดงหรือซ่อนแถวขยาย
            if (expandRow.classList.contains('visible')) {
                expandRow.classList.remove('visible');
                element.textContent = 'เปิด'; // เปลี่ยนข้อความปุ่มเป็น 'เปิด'
            } else {
                expandRow.classList.add('visible');
                element.textContent = 'ปิด'; // เปลี่ยนข้อความปุ่มเป็น 'ปิด'
            }
        }
    </script>
</body>

</html>