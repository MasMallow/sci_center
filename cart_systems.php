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
            header('Location: /home.php');
            exit();
        }
    }
} else {
    header("Location: /sign_in");
    exit();
}

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
        header('Location: cart_systems');
        exit();
    } elseif ($action === 'clear') {
        $_SESSION['reserve_cart'] = [];
        header('Location: cart_systems');
        exit();
    } elseif ($action === 'remove' && isset($_GET['item'])) {
        $itemToRemove = $_GET['item'];
        $key = array_search($itemToRemove, $_SESSION['reserve_cart']);

        if ($key !== false) {
            unset($_SESSION['reserve_cart'][$key]);
        }
        header('Location: cart_systems');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองอุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/cart.css">
</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php'); ?>
    </header>
    <div class="sci_center_cart">
        <div class="sci_center_cart_header">
            <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายการที่เลือกทั้งหมด</span>
        </div>
        <?php if (empty($_SESSION['reserve_cart'])) : ?>
            <div class="main_cart">
                <div class="main_cart_content_non_select">
                    <div class="non_select_1">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span id="B">ไม่มีวัสดุ อุปกรณ์และเครื่องมือถูกเลือกอยู่</span>
                    </div>
                    <div class="non_select_2">
                        <a href="<?php echo $base_url; ?>/"><span>กลับหน้าหลัก</span></a>
                        <span class="warning">!! ถ้าต้องการเลือกวัสดุ อุปกรณ์และเครื่องมือเพิ่มให้กลับหน้าหลัก !!</span>
                    </div>
                </div>
                <?php if (isset($_SESSION['reserve_1'])) : ?>
                    <div class="cart_alert">
                        <div class="cart_alert_content">
                            <div class="cart_alert_header">
                                <span id="B">แจ้งเตือน</span>
                                <div class="modalClose" id="closeAlertButton">
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                            </div>
                            <div class="cart_alert_body">
                                <div class="cart_alert_body_sec1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">การขอใช้งานสำเร็จ รอการอนุมัติจากเจ้าหน้าที่</span>
                                </div>
                                <div class="cart_alert_body_sec2">
                                    <span id="B">ข้อมูลการจอง</span>
                                    <table class="cart_alert_table">
                                        <tbody>
                                            <tr>
                                                <td>หมายเลขที่ทำรายการ</td>
                                                <td><?php echo $_SESSION['reserve_1']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>ชื่อรายการ</td>
                                                <td><?php echo $_SESSION['reserve_2']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>วันเวลาที่ขอจอง</td>
                                                <td><?php echo thai_date_time($_SESSION['reserve_3']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="cart_alert_footer">
                                <a class="back_to_home" href="<?php echo $base_url; ?>/">กลับหน้าหลัก</a>
                                <a class="go_to_notification" href="notification">หน้าแจ้งเตือน</a>
                            </div>
                        </div>
                    </div>
                    <?php
                    unset($_SESSION['reserve_1']);
                    unset($_SESSION['reserve_2']);
                    unset($_SESSION['reserve_3']);
                    ?>
                <?php endif; ?>
            <?php else : ?>
                <form method="post" action="<?php echo $base_url; ?>/SystemsUser/waiting_approve_reserve.php">
                    <div class="main_cart_content">
                        <div class="table_section">
                            <div class="count_list">
                                <div class="count_list_1">
                                    <span>รายการที่เลือกทั้งหมด </span>
                                    <span id="B">( <?php echo count($_SESSION['reserve_cart']); ?> )</span><span> รายการ</span>
                                </div>
                                <div class="count_list_2">
                                    <a href="booking_log.php">ตรวจสอบการจอง</a>
                                </div>
                            </div>
                            <table class="cart_data">
                                <tr>
                                    <th class="th_img"></th>
                                    <th class="th_name"><span id="B">ชื่อรายการ</span></th>
                                    <th class="th_categories"><span id="B">ประเภท</span></th>
                                    <th class="th_amount"><span id="B">จำนวน</span></th>
                                </tr>
                                <?php foreach ($_SESSION['reserve_cart'] as $item) : ?>
                                    <?php
                                    // Retrieve product details from the database based oapn the item
                                    $query = $conn->prepare("SELECT * FROM crud WHERE sci_name = :itemToAdd");
                                    $query->bindParam(':itemToAdd', $item, PDO::PARAM_STR);
                                    $query->execute();
                                    $product = $query->fetch(PDO::FETCH_ASSOC);

                                    // Check if the product is found
                                    if ($product) {
                                        $categories = $product['categories'];
                                        $productName = $product['sci_name'];
                                        $imageURL = 'assets/uploads/' . $product['img_name'];
                                    ?>
                                        <tbody>
                                            <tr>
                                                <td><img src="<?php echo $imageURL; ?>" alt="<?php echo $productName; ?>"></td>
                                                <td><?php echo $productName; ?></td>
                                                <td>
                                                    <span><?php echo $categories ?></span>
                                                </td>
                                                <td>
                                                    <div class="amount_delete">
                                                        <input type="number" name="amount[<?php echo $item; ?>]" value="1" min="1">
                                                        <a class="btn_delete" href="cart_systems?action=remove&item=<?php echo $item; ?>">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    <?php } ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <div class="footer_section">
                            <div class="footer_section_btn_1">
                                <a href="<?php echo $base_url; ?>/" class="back_to_main">กลับหน้าหลัก</a>
                            </div>
                            <div class="footer_section_btn_2">
                                <span class="submit cart_btn">ยืนยัน</span>
                                <div class="cart_submit_popup">
                                    <div class="cart_submit">
                                        <div class="cart_submit_header">
                                            <span id="B">ระบุข้อมูล</span>
                                            <div class="modalClose" id="closeDetails">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="cart_submit_body">
                                            <div class="cart_submit_01">
                                                <label for="reservation_date">วันเวลาที่ขอจอง</label>
                                                <input type="datetime-local" id="reservation_date" name="reservation_date" required>
                                            </div>
                                            <div class="cart_submit_02">
                                                <label for="end_date">วันเวลาที่สิ้นสุดการใช้</label>
                                                <input type="datetime-local" id="end_date" name="end_date" required>
                                            </div>
                                            <button type="submit" class="cart_submit_button" name="reserve">
                                                <span>ยืนยัน</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <a href="cart_systems?action=clear" class="clear_cart">ยกเลิกสิ่งที่เลือกทั้งหมด</a>
                            </div>
                        </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/cart.js"></script>
</body>

</html>