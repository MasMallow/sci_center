<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID    
        ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == 'n_approved') {
            unset($_SESSION['user_login']);
            header('Location: auth/sign_in');
            exit();
        } elseif ($userData['status'] == 'w_approved') {
            unset($_SESSION['reserve_cart']);
            header('Location: /Home.php');
            exit();
        }
    }
} else {
    header("Location: /sign_in");
    exit();
}
try {
    // เตรียมคำสั่ง SQL เพื่อดึงข้อมูลการอนุมัติการจอง
    $sql = "SELECT * FROM approve_to_reserve WHERE reservation_date >= CURDATE() AND situation = 1 AND date_return IS NULL";
    $stmt = $conn->query($sql);

    // ตรวจสอบว่ามีข้อมูลหรือไม่
    if ($stmt->rowCount() > 0) {
    } else {
        echo "ไม่มีการจอง";
    }
} catch (PDOException $e) {
    // แสดงข้อผิดพลาดหากมีการเกิดข้อผิดพลาดในระหว่างการดึงข้อมูล
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn = null;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bookingTable.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="bookingTable">
        <div class="bookingTable_header">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">ตารางการขอใช้ศูนย์วิทยาศาสตร์</span>
        </div>
        <div class="bookingTable_content">
            <div class="bookingTable_div">
                <div class="bookingTable_div_header1">
                    <div id="B">ห้ามขอใช้ในวันที่ที่มีการขอใช้แล้ว</div>
                </div>
                <div class="header1">
                    <div class="header_item" id="B">ชื่อรายการ</div>
                    <div class="header_item" id="B">วันเวลาที่ขอใช้งาน</div>
                </div>
                <?php
                // ใช้ foreach เพื่อวนลูปข้อมูลที่ดึงมา
                foreach ($stmt as $row) :
                ?>
                    <div class="row">
                        <div class="cell">
                            <?php
                            // แยกรายการจาก string เป็น array
                            $items = explode(',', $row['list_name']);
                            foreach ($items as $item) {
                                $item_parts = explode('(', $item); // แยกชื่ออุปกรณ์และจำนวน
                                $product_name = trim($item_parts[0]); // ชื่ออุปกรณ์ (ตัดช่องว่างที่เป็นไปได้)
                                $quantity = str_replace(')', '', $item_parts[1]); // จำนวน (ตัดวงเล็บออก)
                                echo $product_name . " " . $quantity . " รายการ ";
                            }
                            ?>
                        </div>
                        <div class="cell">
                            <?php echo thai_date_time($row['reservation_date']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <?php include_once 'assets/includes/footer.php'; ?>
    </footer>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>