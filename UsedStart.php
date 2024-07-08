<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

try {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        header("Location: /sign_in");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_id'])) {
        $reserve_id = $_POST['reserve_id'];
        $list_name = str_replace(',', '', $_POST['list_name']);

        // Parse the list_name to extract item names and quantities
        preg_match_all('/(.*?)[(](\d+)[)]/', $list_name, $matches, PREG_SET_ORDER);

        // Update the usage status and reduce the quantity in the database
        $conn->beginTransaction();

        // Update the approve_to_reserve table to set Usage_item
        $updateUsageStmt = $conn->prepare("UPDATE approve_to_reserve SET Usage_item = 1 WHERE ID = :reserve_id AND userID = :user_id");
        $updateUsageStmt->bindParam(':reserve_id', $reserve_id, PDO::PARAM_INT);
        $updateUsageStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateUsageStmt->execute();

        // Reduce the quantity in the crud table for each item
        foreach ($matches as $match) {
            $item_name = trim($match[1]);
            $quantity = intval($match[2]);

            $updateCrudStmt = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE sci_name = :item_name");
            $updateCrudStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $updateCrudStmt->bindParam(':item_name', $item_name, PDO::PARAM_STR);
            $updateCrudStmt->execute();
        }

        $conn->commit();
    }

    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE userID = :user_id AND Usage_item IS NULL AND situation = 1 AND DATE(reservation_date) = CURDATE()");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num = count($dataList);
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: error_page.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เริ่มต้นการใช้งาน</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/Used.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <div class="UsedPage">
        <div class="UsedPage_header">
            <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">ใช้งานวัสดุ อุปกรณ์ เครื่องมือที่ทำการขอใช้งาน</span>
        </div>
        <?php if (empty($dataList)) : ?>
            <div class="UsedPage_not_found">
                <i class="fa-solid fa-database"></i>
                <span id="B">ไม่พบข้อมูล</span>
            </div>
        <?php else : ?>
            <div class="UsedPage_content">
                <div class="UsedPage_tableHeader">
                    <span>รายการที่จองทั้งหมด <span id="B">(<?php echo $num; ?>)</span> รายการ</span>
                </div>
                <div class="UsedPage_Table">
                    <?php foreach ($dataList as $data) : ?>
                        <div class="UsedPage_row">
                            <div class="UsedPage_serial_number">
                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                <?php echo htmlspecialchars($data['serial_number']); ?>
                            </div>
                            <div class="UsedPage_list">
                                <?php
                                $items = explode(',', $data['list_name']);
                                foreach ($items as $item) {
                                    $item_parts = explode('(', $item);
                                    $product_name = trim($item_parts[0]);
                                    $quantity = rtrim($item_parts[1], ')');
                                    echo htmlspecialchars($product_name) . ' <span id="B"> ( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                                }
                                ?>
                            </div>
                            <div class="UsedPage_borrowdatetime">
                                <?php echo thai_date_time($data['reservation_date']); ?>
                            </div>
                            <div class="UsedPage_returndate">
                                <div class="notification">
                                    <span class="icon">&#9888;</span>
                                    <?php echo thai_date_time($data['end_date']); ?>
                                </div>
                            </div>
                            <div class="UsedPage_returnList">
                                <form method="POST" action="">
                                    <input type="hidden" name="reserve_id" value="<?= htmlspecialchars($data['ID']); ?>">
                                    <input type="hidden" name="list_name" value="<?= htmlspecialchars($data['list_name']); ?>">
                                    <div class="list_item">
                                        <button class="submit_returned" type="submit">เริ่มใช้งาน</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="expandable_row" style="display: none;">
                            <div>
                                <?php echo htmlspecialchars($data['approver']); ?>
                            </div>
                            <div>
                                <?php echo thai_date_time(htmlspecialchars($data['approvaldatetime'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script>
        function toggleExpandRow(element) {
            var row = element.closest('.return_row').nextElementSibling;
            if (row && row.classList.contains('expandable_row')) {
                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'block';
                    element.classList.remove('fa-circle-arrow-right');
                    element.classList.add('fa-circle-arrow-down');
                } else {
                    row.style.display = 'none';
                    element.classList.remove('fa-circle-arrow-down');
                    element.classList.add('fa-circle-arrow-right');
                }
            }
        }
    </script>
</body>

</html>