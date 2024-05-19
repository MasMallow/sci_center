<?php
session_start();
include_once 'assets/database/connect.php';
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการจอง</title>
</head>

<body>
    <div class="container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE approvaldatetime IS NULL AND approver IS NULL ORDER BY serial_number");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $previousSn = '';
        $previousFirstname = '';
        ?>
        <div class="container">
            <?php if (empty($data)) : ?>
                <p>ไม่มีข้อมูลการจอง</p>
            <?php else : ?>
                <?php foreach ($data as $row) :
                    if ($previousSn != $row['serial_number']) { ?>
                        <div class="row">
                            <span class="info">SN:</span> <?php echo $row['serial_number']; ?><br>
                            <span class="info">First Name:</span> <?php echo $row['firstname']; ?><br>
                        </div>
                    <?php
                        $previousSn = $row['serial_number'];
                    }
                    ?>
                    <div class="row">
                        <?php
                        // แยกข้อมูล Item Borrowed
                        $items = explode(',', $row['product_name']);

                        // แสดงข้อมูลรายการที่ยืม
                        foreach ($items as $item) {
                            $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                            $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                            $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                            echo "<span class='info'>$product_name</span> $quantity ชิ้น<br>"; // แสดงข้อมูล
                        }
                        ?>
                        <span class="info">วันที่กดจอง:</span> <?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?><br>
                        <span class="info">วันที่จองใช้:</span> <?php echo date('d/m/Y H:i:s', strtotime($row['reservation_date'])); ?><br>
                        <form method="POST" action="process_reserve.php">
                            <input type="hidden" name="id" value="<?php echo $row['serial_number']; ?>">
                            <input type="hidden" name="userId" value="<?php echo $row['user_id']; ?>">
                            <input type="submit" name="confirm" value="ยืนยันการอนุมัติ">
                        </form><br>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
