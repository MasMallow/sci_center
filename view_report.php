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
    <a href="index.php">กลับหน้าหลัก</a>
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
                    <th>จำนวน</th>
                    <th>วันที่ยืม</th>
                    <th>วันที่คืน</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Construct SQL query based on user_id and time range filters
                $sql = "SELECT * FROM borrow_history WHERE 1";
                
                if (isset($_GET['user_id']) && $_GET['user_id'] !== 'all') {
                    $user_id = $_GET['user_id'];
                    $sql .= " AND user_id = :user_id";
                }

                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = $_GET['start_date'];
                    $end_date = $_GET['end_date'];
                    $sql .= " AND (borrow_date BETWEEN :start_date AND :end_date)";
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

                // Display the fetched records
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . $row["user_id"] . "</td>";
                        echo "<td>" . $row["firstname"] . "</td>";
                        echo "<td>" . $row["product_name"] . "</td>";
                        echo "<td>" . $row["quantity"] . "</td>";
                        echo "<td>" . date('d/m/Y H:i:s', strtotime($row["borrow_date"])) . "</td>"; // แสดงวันที่และเวลาที่ยืมในรูปแบบวัน/เดือน/ปี และ เวลา
                        echo "<td>" . date('d/m/Y H:i:s', strtotime($row["return_date"])) . "</td>"; // แสดงวันที่และเวลาที่คืนในรูปแบบวัน/เดือน/ปี และ เวลา
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
