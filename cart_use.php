<?php
session_start();
require_once 'assets/database/dbConfig.php';

function redirectWithError($location, $message)
{
    $_SESSION['error'] = $message;
    header("Location: $location");
    exit();
}

if (!isset($_SESSION['user_login'])) {
    redirectWithError('auth/sign_in.php', 'กรุณาเข้าสู่ระบบ!');
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData || $userData['status'] !== 'approved') {
    unset($_SESSION['cart']);
    redirectWithError('home.php', 'ผู้ใช้ไม่ได้รับการอนุมัติ!');
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'add' && isset($_GET['item'])) {
        $itemToAdd = $_GET['item'];
        $_SESSION['cart'][] = $itemToAdd;
        header('Location: cart_use');
        exit();
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: cart_use');
        exit();
    } elseif ($action === 'remove' && isset($_GET['item'])) {
        $itemToRemove = $_GET['item'];
        $key = array_search($itemToRemove, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
        }
        header('Location: cart_use');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>

<body>
    <header>
        <?php include('includes/header.php'); ?>
    </header>
    <div class="sci_center_cart">
        <div class="sci_center_cart_header">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายการที่เลือกทั้งหมด</span>
        </div>
        <?php if (empty($_SESSION['cart'])) : ?>
            <div class="main_cart">
                <div class="main_cart_content_non_select">
                    <div class="non_select_1">
                        <i class="fa-solid fa-cart-shopping"></i>
               <span id="B">ไม่มีวัสดุ อุปกรณ์และเครื่องมือถูกเลือกอยู่</span>
                    </div>
                    <div class="non_select_2">
                        <a href="../project/"><span>กลับหน้าหลัก</span></a>
                        <span class="warning">!! ถ้าต้องการเลือกวัสดุ อุปกรณ์และเครื่องมือเพิ่มให้กลับหน้าหลัก !!</span>
                    </div>
                </div>
                <?php if (isset($_SESSION['use_it_1'])) : ?>
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
                                    <span id="B">การขอใช้สำเร็จ รอการอนุมัติจากเจ้าหน้าที่</span>
                                </div>
                                <div class="cart_alert_body_sec2">
                                    <span id="B">ข้อมูลการขอใช้</span>
                                    <table class="cart_alert_table">
                                        <tbody>
                                            <tr>
                                                <td>หมายเลขที่ทำรายการ</td>
                                                <td><?php echo $_SESSION['use_it_1']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>ชื่อรายการ</td>
                                                <td><?php echo $_SESSION['use_it_2']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="cart_alert_footer">
                                <a class="back_to_home" href="../project/">กลับหน้าหลัก</a>
                                <a class="go_to_notification" href="notification">หน้าแจ้งเตือน</a>
                            </div>
                        </div>
                    </div>
                    <script>
                        var closeModalButton = document.getElementById('closeAlertButton');
                        var modalAlertbook = document.querySelector('.cart_alert');

                        closeModalButton.addEventListener('click', function() {
                            closeModal();
                        });

                        modalAlertbook.addEventListener('click', function(event) {
                            if (event.target === modalAlertbook) {
                                closeModal();
                            }
                        });

                        function closeModal() {
                            var modal = document.querySelector('.cart_alert');
                            modal.style.display = 'none';
                        }
                    </script>
                    <?php
                    unset($_SESSION['use_it_1']);
                    unset($_SESSION['use_it_2']);
                    ?>
                <?php endif; ?>
            <?php else : ?>
                <form method="post" action="waiting_approve_for_use">
                <div class="main_cart_content">
                    <div class="count_list">
                        <div class="count_list_1">
                            <span>รายการที่ขอใช้ทั้งหมด </span>
                            <span id="B">(<?php echo count($_SESSION['cart']); ?>)</span><span> รายการ</span>
                        </div>
                        <div class="count_list_2">
                            <a href="booking_log.php"><span id="B">ตรวจสอบการจอง</span></a>
                        </div>
                    </div>
                        <table class="cart_data">
                            <tr>
                                <th class="th_img"></th>
                                <th class="th_name"><span id="B">ชื่อรายการ</span></th>
                                <th class="th_categories"><span id="B">ประเภท</span></th>
                                <th class="th_amount"><span id="B">จำนวน</span></th>
                            </tr>
                            <?php
                            ?>
                            <?php foreach ($_SESSION['cart'] as $item) : ?>
                                <?php
                                $query = $conn->prepare("SELECT * FROM crud WHERE sci_name = :item");
                                $query->bindParam(':item', $item, PDO::PARAM_STR);
                                $query->execute();
                                $product = $query->fetch(PDO::FETCH_ASSOC);

                                if ($product) {
                                    $categories = $product['categories'];
                                    $productName = $product['sci_name'];
                                    $imageURL = 'assets/uploads/' . $product['img_name'];
                                ?>
                                    <tbody>
                                        <tr>
                                            <td><img src="<?php echo $imageURL; ?>" alt="<?php echo $productName; ?>"></td>
                                            <td><?php echo $productName . $product['serial_number']; ?></td>
                                            <td><span><?php echo $categories; ?></span></td>
                                            <td>
                                                <div class="amount_delete">
                                                    <input type="number" name="amount[<?php echo $item; ?>]" value="1" min="1">
                                                    <a class="btn_delete" href="cart_use?action=remove&item=<?php echo $item; ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                <?php } ?>
                            <?php endforeach; ?>
                        </table>
                        <div class="footer_section">
                            <div class="footer_section_btn_1">
                                <a href="../project/" class="back_to_main">
                                    <i class="fa-solid fa-arrow-left-long"></i>
                                    <span>กลับหน้าหลัก</span>
                                </a>
                            </div>
                            <div class="footer_section_btn_2">
                                <button class="submit cart_btn">ยืนยัน</button>
                                <div class="cart_submit_popup">
                                    <div class="cart_submit">
                                        <div class="cart_submit_header">
                                            <span id="B">ระบุข้อมูล</span>
                                            <div class="modalClose" id="closeDetails">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="cart_submit_body">
                                            <label>ระบุวันที่ เวลาที่คืนอุปกรณ์ และเครื่องมือ</label>
                                            <input type="datetime-local" name="return_date" required>
                                            <button type="submit" class="cart_submit_button" name="use_it">
                                                <span>ยืนยัน</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <a href="cart_use?action=clear" class="clear_cart">ยกเลิกสิ่งที่เลือกทั้งหมด</a>
                            </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/ajax.js"></script>
</body>

</html>