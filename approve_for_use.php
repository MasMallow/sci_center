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
    <title>อนุมัติการยืม</title>
</head>

<body>
    <div class="container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE approvaldatetime IS NULL AND approver IS NULL AND situation = 0 ORDER BY sn");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $previousSn = '';
        $previousFirstname = '';
        ?>
        <div class="container">
            <?php
            foreach ($data as $row) :
                if ($previousSn != $row['sn']) { ?>
                    <div class="row">
                        <span class="info">SN:</span> <?php echo $row['sn']; ?><br>
                        <span class="info">First Name:</span> <?php echo $row['firstname']; ?><br>
                    </div>
                <?php
                    $previousSn = $row['sn'];
                }
                ?>
                <div class="row">
                    <?php
                    // แยกข้อมูล Item Borrowed
                    $items = explode(',', $row['itemborrowed']);

                    // แสดงข้อมูลรายการที่ยืม
                    foreach ($items as $item) {
                        $item_parts = explode('(', $item); // แยกชื่อสินค้าและจำนวนชิ้น
                        $product_name = trim($item_parts[0]); // ชื่อสินค้า (ตัดวงเล็บออก)
                        $quantity = str_replace(')', '', $item_parts[1]); // จำนวนชิ้น (ตัดวงเล็บออกและตัดช่องว่างข้างหน้าและหลัง)
                        echo "<span class='info'>$product_name</span> $quantity ชิ้น<br>"; // แสดงข้อมูล
                    }
                    ?>
                    <span class="info">borrowdatetime:</span> <?php echo date('d/m/Y H:i:s', strtotime($row['borrowdatetime'])); ?><br>
                    <span class="info">returndate:</span> <?php echo date('d/m/Y H:i:s', strtotime($row['returndate'])); ?><br>
                    <form method="POST" action="process_return.php">
                        <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                        <input type="hidden" name="udi" value="<?php echo $row['udi']; ?>">
                        <input type="submit" name="confirm" value="ยืนยันการอนุมัติ">
                    </form>
                    <form method="POST" action="process_cancel_return.php">
                        <input type="hidden" name="id" value="<?php echo $row['sn']; ?>">
                        <input type="hidden" name="udi" value="<?php echo $row['udi']; ?>">
                        <input type="submit" name="cancel" value="ยกเลิกการยืม">
                    </form>
                    
                    <br>
                </div>
            <?php endforeach; ?>
            <?php if (empty($data)) { ?>
                <p>ไม่มีข้อมูลการยืม</p>
                <?php }?>
        </div>
    </div>
</body>

</html>