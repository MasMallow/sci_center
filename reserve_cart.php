<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าจองสินค้า</title>
    <link rel="stylesheet" href="cart.css">
</head>

<body>
    <a href="ajax.php">กลับหน้าหลัก</a>
    <?php
    session_start();
    include_once 'assets/database/connect.php';

    // Check if cart session exists, create one if not
    if (!isset($_SESSION['reserve_cart'])) {
        $_SESSION['reserve_cart'] = [];
    }

    // Handle actions like add, clear, or remove items from the reserve cart
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action === 'add' && isset($_GET['item'])) {
            $itemToAdd = $_GET['item'];
            // Check if the item already exists in the reserve cart
            if (!in_array($itemToAdd, $_SESSION['reserve_cart'])) {
                $_SESSION['reserve_cart'][] = $itemToAdd;
            }
            header('Location: reserve_cart.php');
            exit;
        } elseif ($action === 'clear') {
            $_SESSION['reserve_cart'] = [];

            header('Location: reserve_cart.php');
            exit;
        } elseif ($action === 'remove' && isset($_GET['item'])) {
            $itemToRemove = $_GET['item'];
            $key = array_search($itemToRemove, $_SESSION['reserve_cart']);

            if ($key !== false) {
                unset($_SESSION['reserve_cart'][$key]);
            }

            header('Location: reserve_cart.php');
            exit;
        }
    }
    ?>

    <!-- Display items in the reserve cart -->
    <h2>ตะกร้าจองสินค้า</h2>
    <a href="booking_log.php" style="color: red;">*ตรวจสอบการจองก่อนยืมอุปกรณ์*</a>

    <?php
    if (empty($_SESSION['reserve_cart'])) {
        echo '<p>ไม่มีสินค้าในตะกร้าจอง</p>';
    } else {
        echo '<form method="post" action="process_reserve.php">';
        echo '<table>';
        echo'<tr>
                <th>ลำดับ</th>
                <th>รูปภาพ</th>
                <th>ชื่อสินค้า</th>
                <th>จำนวน</th>
                <th>การดำเนินการ</th>
            </tr>';
        $num = 1;
        foreach ($_SESSION['reserve_cart'] as $item) {
            // Retrieve product details from the database based on the item
            $query = $conn->prepare("SELECT * FROM crud WHERE file_name = :item");
            $query->bindParam(':item', $item, PDO::PARAM_STR);
            $query->execute();
            $product = $query->fetch(PDO::FETCH_ASSOC);
            $productName = $product['product_name'];
            $imageURL = 'uploads/' . $product['file_name'];

            if (file_exists($imageURL)) {
                echo '<tr>';
                echo "<td>$num</td>";
                echo '<td><img src="' . $imageURL . '" alt="' . $productName . '"></td>';
                echo '<td>' . $productName . '</td>';
                echo '<td><input type="number" name="amount[' . $item . ']" value="1" min="1"></td>';
                echo '<td><a href="reserve_cart.php?action=remove&item=' . $item . '">ลบ</a></td>';
                echo '</tr>';
                $num++;
            }
        }
        echo '</table>';
    ?>
        <!-- เพิ่มส่วนเลือกวันที่และเวลาที่จอง -->
        <label for="return_date">เลือกวันที่และเวลาที่จองอุปกรณ์:</label>
        <input type="datetime-local" name="reservation_date" required>
        <input type="submit" name="submit" value="ยืนยันการจอง">
        <input type="button" value="ยกเลิกการจองทั้งหมด" onclick="location.href='reserve_cart.php?action=clear'">
        </form>
    <?php } ?>
</body>

</html>