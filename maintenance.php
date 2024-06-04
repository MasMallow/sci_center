<?php
session_start();
require_once 'assets/database/connect.php';
include_once 'includes/thai_date_time.php';

$searchTitle = "";
$searchValue = "";

if (isset($_GET['search'])) {
    $searchValue = htmlspecialchars($_GET['search']);
    $searchTitle = "ค้นหา \"$searchValue\" | ";
}

try {
    if (isset($_SESSION['staff_login']) || isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'] ?? $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($_SESSION['user_login']) && $userData['status'] !== 'approved') {
            header("Location: home");
            exit();
        }
    }

    $action = $_GET['action'] ?? 'start_maintenance';

    if ($action === 'start_maintenance') {
        $searchQuery = isset($_GET["search"]) && !empty($_GET["search"]) ? "%" . $_GET["search"] . "%" : null;
        $stmt = $conn->prepare($searchQuery ? "SELECT * FROM crud WHERE availability = 0 AND sci_name LIKE :search ORDER BY id ASC" : "SELECT * FROM crud WHERE availability = 0 ORDER BY id ASC");

        if ($searchQuery) {
            $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
        }

        $stmt->execute();
        $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($action === 'end_maintenance') {
        $searchQuery = isset($_GET["search"]) && !empty($_GET["search"]) ? "%" . $_GET["search"] . "%" : null;
        $stmt = $conn->prepare($searchQuery ? "SELECT * FROM crud WHERE availability != 0 AND sci_name LIKE :search ORDER BY id ASC" : "SELECT * FROM crud WHERE availability != 0 ORDER BY id ASC");

        if ($searchQuery) {
            $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
        }

        $stmt->execute();
        $maintenance_success = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การบำรุงรักษา</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/maintenance.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">การบำรุงรักษา</span>
        </div>
    </div>
    <div class="maintenance_section_btn">
        <form class="btn_maintenance_all" method="get">
            <button type="submit" class="<?= ($action === 'start_maintenance') ? 'active' : ''; ?> btn_maintenance_01" name="action" value="start_maintenance">เริ่มการบำรุงรักษา</button>
            <button type="submit" class="<?= ($action === 'end_maintenance') ? 'active' : ''; ?> btn_maintenance_02" name="action" value="end_maintenance">สิ้นสุดการบำรุงรักษา</button>
        </form>
        <form class="maintenance_search_header" method="get">
            <input type="hidden" name="action" value="<?= htmlspecialchars($action); ?>">
            <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
            <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <?php if ($action === 'start_maintenance') : ?>
        <?php if (!empty($maintenance)) : ?>
            <form action="maintenance_notification" method="post">
                <div class="maintenance_section">
                    <table class="table_maintenace">
                        <thead>
                            <tr>
                                <th class="serial_number"><span id="B">Serial Number</span></th>
                                <th class="sci_name"><span id="B">ชื่อ</span></th>
                                <th class="categories"><span id="B">ประเภท</span></th>
                                <th class="amount"><span id="B">จำนวน</span></th>
                                <th class="installation_date"><span id="B">วันที่ติดตั้ง</span></th>
                                <th><span class="maintenance_button" id="B">บำรุงรักษา</span></th>
                                <div class="choose_categories_popup">
                                    <div class="choose_categories">
                                        <div class="choose_categories_header">
                                            <span id="B">กรอกข้อมูลการบำรุงรักษา</span>
                                            <div class="modalClose" id="closeDetails">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="maintenace_popup">
                                            <input type="date" name="end_date" required>
                                            <input type="text" name="note" placeholder="หมายเหตุ">
                                            <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance as $row) : ?>
                                <tr>
                                    <td class="serial_number"><?= htmlspecialchars($row['s_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['categories'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?= htmlspecialchars(thai_date($row['installation_date']), ENT_QUOTES, 'UTF-8') ?><br>
                                        <?= htmlspecialchars(thai_time($row['installation_date']), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="checkbox"><label>
                                            <input type="checkbox" name="selected_ids[]" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <span class="custom-checkbox"></span>
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php else : ?>
            <div class="user_approve_not_found">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span id="B">ไม่มีอุปกรณ์ หรือเครื่องมือที่ทำการบำรุงรักษา</span>
            </div>
        <?php endif; ?>
    <?php elseif ($action === 'end_maintenance') : ?>
        <?php if (!empty($maintenance_success)) : ?>
            <form action="maintenance_complete" method="POST">
                <div class="maintenance_section">
                    <table class="table_maintenace">
                        <thead>
                            <tr>
                                <th class="serial_number"><span id="B">Serial Number</span></th>
                                <th class="sci_name"><span id="B">ชื่อ</span></th>
                                <th class="categories"><span id="B">ประเภท</span></th>
                                <th class="amount"><span id="B">จำนวน</span></th>
                                <th class="installation_date"><span id="B">เริ่มบำรุงรักษา</span></th>
                                <th>
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="maintenance_button"  name="complete_maintenance"><span id="B">การบำรุงรักษาเสร็จสิ้น</span></button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenance_success as $row) : ?>
                                <tr>
                                    <td class="serial_number"><?= htmlspecialchars($row['s_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['categories'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(thai_date($row['installation_date']), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="checkbox"><label>
                                            <input type="checkbox" name="id[]" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <span class="custom-checkbox"></span>
                                        </label></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php else : ?>
            <div class="user_approve_not_found">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span id="B">ไม่มีอุปกรณ์ หรือเครื่องมือที่ทำการบำรุงรักษา</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <script src="assets/js/maintenance.js"></script>
</body>

</html>