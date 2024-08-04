<?php
session_start();
require_once 'assets/config/Database.php';
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
            header('Location: /sign_in');
            exit();
        } elseif ($userData['status'] == 'w_approved') {
            unset($_SESSION['reserve_cart']);
            header('Location: /');
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
        header('Location: cart');
        exit();
    } elseif ($action === 'clear') {
        $_SESSION['reserve_cart'] = [];
        header('Location: cart');
        exit();
    } elseif ($action === 'remove' && isset($_GET['item'])) {
        $itemToRemove = $_GET['item'];
        $key = array_search($itemToRemove, $_SESSION['reserve_cart']);

        if ($key !== false) {
            unset($_SESSION['reserve_cart'][$key]);
        }
        header('Location: cart');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการที่เลือกทั้งหมด</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/cart.css">
</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php'); ?>
    </header>
    <div class="sci_center_cart">
        <div class="sci_center_cart_header">
            <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/cart') {
                    echo '<a href="/cart">รายการที่จะทำการขอใช้</a>';
                }
                ?>
            </div>
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

                <!-- ------------- NOTIFICATION ---------------- -->
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
                                    <span id="B">ข้อมูลการขอใช้</span>
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
                                                <td>วันเวลาที่ขอใช้</td>
                                                <td><?php echo thai_date_time($_SESSION['reserve_3']); ?></td>
                                            </tr>
                                            <tr>
                                                <td>QR CODE <br> สำหรับติดตามการขอใช้</td>
                                                <td>
                                                    <div class="content_img">
                                                        <img src="<?= htmlspecialchars($base_url); ?>/assets/img/qr_code_user/qrcode.png" alt="Image">
                                                    </div>
                                                </td>
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
                <?php endif ?>
            <?php else : ?>
                <?php if (isset($_SESSION['reserveError'])) : ?>
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
                                    <i class="fa-solid fa-circle-xmark error"></i>
                                    <span id="B">เกิดข้อผิดพลาดในการขอใช้</span>
                                </div>
                                <div class="cart_alert_body_sec2">
                                    <span class="cart_alert_body_sec202">
                                        <?php echo $_SESSION['reserveError']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="cart_alert_footer">
                                <a class="back_to_home" href="<?php echo $base_url; ?>/">กลับหน้าหลัก</a>
                                <a class="go_to_notification" href="booking_log">ตรวจสอบการขอใช้</a>
                            </div>
                        </div>
                    </div>
                    <?php
                    unset($_SESSION['reserveError']);
                    ?>
                <?php endif; ?>
                <form method="post" action="<?php echo $base_url; ?>/models/requestUse.php">
                    <div class="main_cart_content">
                        <div class="count_list">
                            <div class="count_list_1">
                                <span>รายการที่เลือกทั้งหมด </span>
                                <span id="B"><?php echo count($_SESSION['reserve_cart']); ?></span><span> รายการ</span>
                            </div>
                            <a class="count_list_2" href="/calendar">ตรวจสอบการขอใช้</a>
                        </div>
                        <div class="cart_data">
                            <div class="cart_header">
                                <div class="cart_col img"></div>
                                <div class="cart_col name"><span id="B">ชื่อรายการ</span></div>
                                <div class="cart_col categories"><span id="B">ประเภท</span></div>
                                <div class="cart_col amount"><span id="B">จำนวน</span></div>
                            </div>
                            <?php foreach ($_SESSION['reserve_cart'] as $item) : ?>
                                <?php
                                $query = $conn->prepare("SELECT * FROM crud WHERE serial_number = :itemToAdd");
                                $query->bindParam(':itemToAdd', $item, PDO::PARAM_STR);
                                $query->execute();
                                $product = $query->fetch(PDO::FETCH_ASSOC);

                                if ($product) {
                                    $categories = $product['categories'];
                                    $productName = $product['sci_name'];
                                    $serial_number = $product['serial_number'];
                                    $imageURL = 'assets/uploads/' . $product['img_name'];
                                ?>
                                    <div class="cart_row">
                                        <div class="cart_col img">
                                            <div class="flex-container">
                                                <div class="flex-item">
                                                    <img src="<?php echo $imageURL; ?>" alt="<?php echo $productName; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart_col name"><?php echo $productName; ?>(<?php echo $serial_number; ?>)</div>
                                        <div class="cart_col categories">
                                            <span><?php echo $categories ?></span>
                                        </div>
                                        <div class="cart_col amount">
                                            <div class="amount_delete">
                                                <input class="amount_input" type="number" name="amount[<?php echo $item; ?>]" value="1" min="1">
                                            </div>
                                            <a class="btn_delete" href="cart?action=remove&item=<?php echo $item; ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="footer_section">
                            <div class="footer_section_btn_1">
                                <a href="javascript:history.back();" class="back_to_main">กลับหน้าหลัก</a>
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
                                                <label for="reservation_date">วันเวลาที่ขอใช้</label>
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
                                <a href="cart?action=clear" class="clear_cart">ยกเลิกสิ่งที่เลือกทั้งหมด</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/cart.js"></script>
</body>

</html>