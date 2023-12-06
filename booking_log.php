<h1>ประวัติการจอง</h1>
<h1>*ห้ามยืมอุปกรณ์ที่ถูกจองในวันที่จอง*</h1>
<a href="ajax.php">กลับหน้าหลัก</a>
<br>
<br>
<?php
session_start();
require_once 'db.php';
                            $sql = "SELECT * FROM bookings ORDER BY reservation_date DESC LIMIT 10";
                            $result = $db->query($sql);
                            
                            if ($result->num_rows > 0) {
                                echo "<table border='1'>
                                <tr>
                                <th>ชื่ออุปกรณ์</th>
                                <th>จำนวน</th>
                                <th>วันที่ถูกจอง</th>
                                </tr>";
                                while ($row = $result->fetch_assoc()) {
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
                            $db->close();
                            ?>