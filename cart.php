<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกรายการวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <!-- ส่วนของ Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="cart.css">
</head>

<body>
    <?php
    session_start();
    include_once 'db.php';

    // Check if cart session exists, create one if not
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle actions like add, clear, or remove items from the cart
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action === 'add' && isset($_GET['item'])) {
            $itemToAdd = $_GET['item'];
            $_SESSION['cart'][] = $itemToAdd;

            header('Location: cart.php');
            exit;
        } elseif ($action === 'clear') {
            $_SESSION['cart'] = [];

            header('Location: cart.php');
            exit;
        } elseif ($action === 'remove' && isset($_GET['item'])) {
            $itemToRemove = $_GET['item'];
            $key = array_search($itemToRemove, $_SESSION['cart']);

            if ($key !== false) {
                unset($_SESSION['cart'][$key]);
            }

            header('Location: cart.php');
            exit;
        }
    }

    // Check if the user is logged in
    if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: login.php');
        exit;
    }

    // Fetch user data if logged in
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (isset($_SESSION['admin_login'])) {
        $user_id = $_SESSION['admin_login'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>

    <!-- Display the items in the cart -->
    <div class="header">
        <h3>การขอใช้งานวัสดุ อุปกรณ์ และเครื่องมือ</h3>
    </div>
    <div class="main-cart">
        <div class="cart">
            <div class="head">
                <div class="head-name">การขอใช้งานวัสดุ อุปกรณ์ และเครื่องมือ</div>
            </div>
            <?php
            if (empty($_SESSION['cart'])) {
                echo '<div class="non-select">ไม่มีวัสดุ อุปกรณ์และเครื่องมือถูกเลือกอยู่</div>';
            } else {
                echo '<form method="post" action="process_return.php">';
                echo '<div class="table">';
                echo '<table class="cart-data">';
                echo '<tr>
                        <th>ลำดับ</th>
                        <th>รูปภาพ</th>
                        <th>ชื่ออุปกรณ์</th>
                        <th>จำนวน</th>
                        <th>การดำเนินการ</th>
                    </tr>';
                foreach ($_SESSION['cart'] as $item) {
                    // Retrieve product details from the database based on the item
                    $query = $conn->prepare("SELECT * FROM image WHERE file_name = :item");
                    $query->bindParam(':item', $item, PDO::PARAM_STR);
                    $query->execute();
                    $product = $query->fetch(PDO::FETCH_ASSOC);
                    $productName = $product['product_name'];
                    $imageURL = 'test/' . $product['file_name'];

                    if (file_exists($imageURL)) {
                        echo '<tr class="row">';
                        echo '<td><p>1</p></td>';
                        echo '<td><img src="' . $imageURL . '" alt="' . $productName . '"></td>';
                        echo '<td class="product-name">' . $productName . '</td>';
                        echo '<td><input type="number" name="amount[' . $item . ']" value="1" min="1" ></td>';
                        echo '<td><a class="delete" href="cart.php?action=remove&item=' . $item . '"><i class="fa fa-trash"><p>ลบรายการที่เลือก</p></i></a></td>';
                    }
                }

                echo '</tr>';
                echo '</table>';
                echo '</div>';
            ?>
                <div class="date">
                    <label class="date" for="return_date">กรุณาเลือกวันที่คืนอุปกรณ์ และเครื่องมือ</label>
                    <input type="date" name="return_date" required>
                </div>
                <div class="firstname">
                    <?php echo 'ผู้ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ : ' . $row["firstname"]; ?>
                </div>
            <?php
                echo '<button type="submit" name="update">ยืนยัน</button>';
                echo '<button class="back" onclick="window.location.href=\'cart.php?action=clear\'">ยกเลิกสิ่งที่เลือกทั้งหมด</button>';
                echo '</form>';
            }
            ?>
            <button class="back" onclick="location.href='ajax.php'">เลือกรายการวัสดุ อุปกรณ์ และเครื่องมือเพิ่ม (จะถูกนำพาไปหน้าหลัก)</button>
        </div>
    </div>
</body>

</html>