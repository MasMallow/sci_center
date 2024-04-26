<h1>ประวัติการจองทั้งหมด</h1>
<a href="ajax.php">กลับหน้าหลัก</a>
<br>
<br>
<?php
session_start();
require_once 'assets/database/connect.php';

try {
    $sql = "SELECT * FROM bookings ORDER BY reservation_date DESC LIMIT 10";
    $stmt = $conn->query($sql);
    
    if ($stmt->rowCount() > 0) {
        echo "<table border='1'>
        <tr>
        <th>ชื่ออุปกรณ์</th>
        <th>จำนวน</th>
        <th>วันที่ถูกจอง</th>
        </tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['reservation_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
} catch(PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

$pdo = null;
?>
