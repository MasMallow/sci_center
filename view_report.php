<?php
session_start();
require_once 'assets/database/connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการยืมสินค้า</title>

    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/view_report.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php' ?>
    </header>
    <div class="header_approve">
        <div class="header_approve_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายงานการยืมอุปกรณ์</span>
        </div>
    </div>
    <div class="view_report">
        <div class="view_report_form">
            <form class="form_1" action="view_report.php" method="GET">
                <div class="view_report_column">
                    <div class="view_report_input">
                        <label id="B" for="userID">UID ของผู้ใช้งาน</label>
                        <input type="text" id="userID" name="user_id" placeholder="กรอกไอดีผู้ใช้">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="startDate">ช่วงเวลาเริ่มต้น</label>
                        <input type="date" id="startDate" name="start_date">
                    </div>
                    <div class="view_report_input">
                        <label id="B" for="endDate">ช่วงเวลาสิ้นสุด</label>
                        <input type="date" id="endDate" name="end_date">
                    </div>
                </div>
                <div class="view_report_btn">
                    <button type="submit" class="search">ค้นหา</button>
                    <button type="submit" class="reset" name="user_id" value="all">แสดงข้อมูลทั้งหมด</button>
                </div>
            </form>
        </div>
        <div class="view_report_table">
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

                    if (isset($_GET['user_id']) && $_GET['user_id'] !== 'all') {
                        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    }

                    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
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
    </div>

</body>

</html>