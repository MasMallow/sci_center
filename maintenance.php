<?php
session_start();
include_once 'assets/database/connect.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// เตรียมการเชื่อมต่อฐานข้อมูล (สมมติว่าคุณมีการเชื่อมต่อที่ถูกต้องใน $conn)
$stmt = $conn->prepare("SELECT * FROM crud WHERE Availability=0 ORDER BY id ASC");
$stmt->execute();

// ดึงข้อมูลทั้งหมดเป็น associative array
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table with Checkboxes</title>
    <style>
        .table {
            display: grid;
            grid-template-columns: auto repeat(4, 1fr);
            gap: 5px;
        }

        .table .header {
            font-weight: bold;
        }

        .table .row {
            display: contents;
            /* Allow grid items to be placed as children of their parents */
        }

        .table .cell {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .form-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>แจ้งเตือนรอบการบำรุงรักษา อุปกรณ์ เครื่องมือ</h1>
    <form action="maintenance_notification" method="post">
        <div class="table">
            <div class="header cell">ID</div>
            <div class="header cell">ชื่อ</div>
            <div class="header cell">ประเภท</div>
            <div class="header cell">จำนวน</div>
            <div class="header cell">Select</div>

            <?php
            // ตรวจสอบข้อมูล
            foreach ($data as $row) {
                echo '<div class="row">';
                echo '<div class="cell">' . $row['id'] . '</div>';
                echo '<div class="cell">' . $row['sci_name'] . '</div>';
                echo '<div class="cell">' . $row['categories'] . '</div>';
                echo '<div class="cell">' . $row['amount'] . '</div>';
                echo '<div class="cell"><input type="checkbox" name="selected_ids[]" value="' . $row['id'] . '"></div>';
                echo '</div>';
            }
            ?>
        </div>
        <div class="form-container">
            <input type="text" name="note" placeholder="หมายเหตุ">
            <input type="date" class="form-control" id="endDate" name="end_date" required>
            <input type="submit" name="confirm" value="ยืนยัน">
        </div>
    </form>
    
    <h2>การบำรุงรักษาเสร็จสิ้น</h2>
    <form action="maintenance_complete" method="POST">
        <div class="table">
            <div class="header cell">ID</div>
            <div class="header cell">ชื่อ</div>
            <div class="header cell">ประเภท</div>
            <div class="header cell">จำนวน</div>
            <div class="header cell">Select</div>

            <?php
            $stmt = $conn->prepare("SELECT * FROM crud WHERE Availability != 0 ORDER BY id ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $row) {
                echo '<div class="row">';
                echo '<div class="cell">' . $row['id'] . '</div>';
                echo '<div class="cell">' . $row['sci_name'] . '</div>';
                echo '<div class="cell">' . $row['categories'] . '</div>';
                echo '<div class="cell">' . $row['amount'] . '</div>';
                echo '<div class="cell">';
                echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                echo '<button type="submit" name="complete_maintenance" value="' . $row['id'] . '">การบำรุงรักษาเสร็จสิ้น</button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </form>
</body>

</html>
