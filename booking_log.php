<h1>ตารางการจอง</h1>
<h1>*ห้ามยืมอุปกรณ์ที่ถูกจองในวันที่จอง*</h1>
<a href="home.php">กลับหน้าหลัก</a>
<a href="history_booking_log.php">ประวัติการจองทั้งหมด</a>
<br>
<br>
<?php
session_start();
require_once 'assets/database/connect.php';
include_once 'includes/thai_date_time.php';

try {
    $sql = "SELECT * FROM approve_to_bookings WHERE reservation_date >= CURDATE() AND situation = 1";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        echo "<table border='1'>
        <tr>
        <th>ชื่ออุปกรณ์</th>
        <th>วันที่ถูกจอง</th>
        </tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            $items = explode(',', $row['list_name']);
            echo "<td>";
            foreach ($items as $item) {
                $item_parts = explode('(', $item); // แยกชื่ออุปกรณ์และจำนวน
                $product_name = trim($item_parts[0]); // ชื่ออุปกรณ์ (ตัดช่องว่างที่เป็นไปได้)
                $quantity = str_replace(')', '', $item_parts[1]); // จำนวน (ตัดวงเล็บออก)
                echo $product_name . " " . $quantity . " ชิ้น ";
            }
            echo "</td>";
            echo "<td>" . thai_date_time($row['reservation_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

$pdo = null;
?>