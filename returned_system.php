<?php
session_start();
require_once 'assets/database/connect.php'; // เรียกใช้งานไฟล์ที่เกี่ยวข้องกับการเชื่อมต่อฐานข้อมูล
include 'includes/thai_date_time.php'; // เรียกใช้งานไฟล์ที่เกี่ยวข้องกับการแสดงวันที่และเวลาเป็นภาษาไทย

try {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        header("Location: auth/sign_in");
        exit();
    }

    if ($userData['status'] !== 'approved') {
        unset($_SESSION['cart']);
        header("Location: ../projects/");
        exit();
    }

    $returned = $_GET['returned'] ?? 'used'; // ตรวจสอบค่าที่ถูกส่งมาจาก query parameter 'returned'

    // ตรวจสอบและเลือกคำสั่ง SQL ตามค่า 'returned' ที่รับมา
    if ($returned === 'used') {
        $stmt = $conn->prepare("SELECT * FROM approve_to_use WHERE user_id = :user_id AND situation = 1 AND date_return IS NULL");
    } else {
        $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE user_id = :user_id AND situation = 1 AND date_return IS NULL");
    }
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC); // เก็บข้อมูลที่ได้จากการ query ลงในตัวแปร $dataList
    $num = count($dataList); // นับจำนวนรายการ
} catch (Exception $e) {
    error_log($e->getMessage()); // บันทึกข้อผิดพลาดลงในไฟล์ log
    header("Location: error_page.php"); // เปลี่ยนเส้นทางไปยังหน้าข้อผิดพลาด
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คืนอุปกรณ์ และเครื่องมือ</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/return_for_used_bookings.css">
    <script>
        // เพิ่มความยืดหยุ่นในการจัดการกับ JavaScript
        function confirmReturn(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to mark this item as returned?")) {
                event.target.closest('form').submit();
            }
        }

        function toggleExpandRow(element) {
            const row = element.closest('tr');
            const expandRow = row.nextElementSibling;
            if (expandRow.classList.contains('visible')) {
                expandRow.classList.remove('visible');
                element.textContent = 'เปิด';
            } else {
                expandRow.classList.add('visible');
                element.textContent = 'ปิด';
            }
        }
    </script>
    <style>
        .expand_row {
            display: none;
        }

        .expand_row.visible {
            display: table-row;
        }

        .open_expand_row {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .drop_down_confirm {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            padding: 12px 16px;
            z-index: 1;
        }

        .drop_down_confirm.show {
            display: block;
        }
    </style>
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="return_page">
        <div class="return_content_header_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">คืนอุปกรณ์ และเครื่องมือ<?= ($returned === 'used') ? 'จากการขอใช้' : 'จากการจอง'; ?></span>
        </div>
        <form class="returned_btn" method="get">
            <!-- ปรับปรุงการเพิ่ม class active ตามค่า returned -->
            <button type="submit" class="<?= ($returned === 'used') ? 'active' : ''; ?> returned_btn_01" name="returned" value="used">คืนอุปกรณ์ และเครื่องมือจากการขอใช้</button>
            <button type="submit" class="<?= ($returned === 'reserve') ? 'active' : ''; ?> returned_btn_02" name="returned" value="reserve">คืนอุปกรณ์ และเครื่องมือจากการจอง</button>
        </form>
        <?php if (empty($dataList)) : ?>
            <div class="return_content_not_found_section">
                <i class="fa-solid fa-hourglass-end"></i>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
        <?php else : ?>
            <div class="return_content_section">
                <div class="return_table_header">
                    <span>รายการที่ขอใช้งานทั้งหมด <span id="B">(<?php echo $num; ?>)</span> รายการ</span>
                </div>
                <table class="table_return">
                    <thead>
                        <tr>
                            <th class="return_serial_number"><span id="B">หมายเลขรายการ</span></th>
                            <th class="return_list"><span id="B"><?= ($returned === 'used') ? 'รายการที่ขอใช้งาน' : 'รายการที่ขอจอง'; ?></span></th>
                            <th class="return_borrowdatetime"><span id="B"><?= ($returned === 'used') ? 'วันเวลาที่ขอใช้งาน' : 'วันเวลาที่ขอจอง'; ?></span></th>
                            <th class="return_returndate"><span id="B"><?= ($returned === 'used') ? 'วันเวลาที่สิ้นสุดขอใช้งาน' : 'วันเวลาที่เกินกำหนด'; ?></span></th>
                            <th class="return_return_list"><span id="B"><?= ($returned === 'used') ? 'คืนรายการที่ขอใช้งาน' : 'คืนรายการที่ขอจอง'; ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataList as $data) : ?>
                            <tr>
                                <td class="return_serial_number">
                                    <span class="open_expand_row" onclick="toggleExpandRow(this)">เปิด</span>
                                    <?php echo htmlspecialchars($data['serial_number']); ?>
                                </td>
                                <td>
                                    <?php
                                    $items = explode(',', $data['list_name']);
                                    foreach ($items as $item) {
                                        $item_parts = explode('(', $item);
                                        $product_name = trim($item_parts[0]);
                                        $quantity = rtrim($item_parts[1], ')');
                                        echo htmlspecialchars($product_name) . ' <span id="B"> ( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo thai_date_time($data['borrowdatetime'] ?? $data['reservation_date']); ?></td>
                                <td><?php echo thai_date_time($data['returndate'] ?? $data['end_date']); ?></td>
                                <td>
                                    <?php if ($returned === 'used') : ?>
                                        <form method="POST" action="check_request_notification">
                                            <input type="hidden" name="return_id" value="<?= htmlspecialchars($data['user_id']); ?>">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user_id']); ?>">
                                            <div class="confirm_btn">
                                                <span class="btn_text">คืนรายการที่ขอใช้งาน</span>
                                            </div>
                                            <div class="list_item">
                                                <button class="submit_returned" type="submit">ยืนยัน</button>
                                                <span class="close_confirm_btn">ยกเลิก</span>
                                            </div>
                                        </form>
                                    <?php else : ?>
                                        <form method="POST" action="check_request_bookings_notification">
                                            <input type="hidden" name="return_id" value="<?= htmlspecialchars($data['id']); ?>">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($data['user_id']); ?>">
                                            <div class="confirm_btn">
                                                <span class="btn_text">คืนรายการที่ขอใช้งาน</span>
                                            </div>
                                            <div class="list_item">
                                                <button class="submit_returned" type="submit">ยืนยัน</button>
                                                <span class="close_confirm_btn">ยกเลิก</span>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr class="expand_row">
                                <td colspan="5">
                                    <div>
                                        <?php echo htmlspecialchars($data['approver']); ?>
                                    </div>
                                    <div>
                                        <?php echo thai_date_time(htmlspecialchars($data['approvaldatetime'])); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script>
        const
            selectBTN = document.querySelector(".confirm_btn"),
            cancelBTN = document.querySelector(".close_confirm_btn"),
            items = document.querySelectorAll(".item");

        selectBTN.addEventListener("click", () => {
            selectBTN.classList.toggle("open");
        });
        cancelBTN.addEventListener("click", () => {
            selectBTN.classList.remove("open");
        });
    </script>
</body>

</html>