<?php
session_start();
require_once 'assets/database/connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการยืมสินค้า</title>
</head>

<body>
    <a href="home.php">กลับหน้าหลัก</a>
    <div class="">
        <h1 class="">รายงานการยืมอุปกรณ์</h1>

        <!-- Form to enter user ID -->
        <form action="view_report.php" method="GET">
            <div class="form-group">
                <label for="userID">กรุณาใส่ไอดีผู้ใช้:</label>
                <input type="text" class="form-control" id="userID" name="user_id" placeholder="กรอกไอดีผู้ใช้">
                <button type="submit" class="btn btn-primary">ดูรายงาน</button>
            </div>
        </form>

        <!-- Form to enter time range -->
        <form action="view_report.php" method="GET">
            <div class="form-group">
                <label for="startDate">ช่วงเวลาเริ่มต้น:</label>
                <input type="date" class="form-control" id="startDate" name="start_date">
            </div>
            <div class="form-group">
                <label for="endDate">ช่วงเวลาสิ้นสุด:</label>
                <input type="date" class="form-control" id="endDate" name="end_date">
            </div>
            <button type="submit" class="btn btn-primary">ดูรายงาน</button>
        </form>

        <!-- Button to view all records -->
        <form action="view_report.php" method="GET">
            <div class="form-group">
                <input type="hidden" name="user_id" value="all">
                <button type="submit" class="btn btn-secondary">ดูทั้งหมด</button>
            </div>
        </form>
    </div>

    <div id="reportResult" class="">
        <table>
            <thead>
                <tr>
                    <th>รหัสผู้ใช้</th>
                    <th>ชื่อ</th>
                    <th>ชื่อสินค้า</th>
                    <th>วันที่ยืม</th>
                    <th>วันที่คืน</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Construct SQL query based on user_id and time range filters
                $sql = "SELECT * FROM waiting_for_approval WHERE 1";
                
                if (isset($_GET['user_id']) && $_GET['user_id'] !== 'all') {
                    $user_id = $_GET['user_id'];
                    $sql .= " AND udi = :user_id";
                }

                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = $_GET['start_date'];
                    $end_date = $_GET['end_date'];
                    $sql .= " AND (borrowdatetime BETWEEN :start_date AND :end_date)";
                }

                // Prepare and execute the SQL query
                $stmt = $conn->prepare($sql);

                if (isset($user_id)) {
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                }

                if (isset($start_date) && isset($end_date)) {
                    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
                    $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
                }

                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Display the fetched records
                if (count($data) > 0) {
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["udi"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["firstname"]) . "</td>";

                        $items = explode(',', $row['itemborrowed']);
                        echo "<td>";
                        foreach ($items as $item) {
                            $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                            $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                            $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                            echo "<span class='info'>$product_name</span> $quantity ชิ้น<br>"; // แสดงข้อมูล
                        }
                        echo "</td>";

                        echo "<td>" . date('d/m/Y H:i:s', strtotime($row["borrowdatetime"])) . "</td>"; // แสดงวันที่และเวลาที่ยืมในรูปแบบวัน/เดือน/ปี และ เวลา
                        echo "<td>" . date('d/m/Y H:i:s', strtotime($row["returndate"])) . "</td>"; // แสดงวันที่และเวลาที่คืนในรูปแบบวัน/เดือน/ปี และ เวลา
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>ไม่พบข้อมูลในฐานข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
