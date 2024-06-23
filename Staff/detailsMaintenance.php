<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

$detailsMaintenance = [];

try {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id']; // Cast to int to ensure it's a number
        $stmt = $conn->prepare("
            SELECT * FROM info_sciname 
            INNER JOIN logs_maintenance
            ON info_sciname.serial_number = logs_maintenance.serial_number 
            WHERE info_sciname.ID = :id
        ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $rowCount = $stmt->rowCount(); // นับจำนวนคอลัมน์
        $detailsMaintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($detailsMaintenance)) {
            echo "No maintenance details found.";
            exit;
        }
    } else {
        echo "ID parameter is missing.";
        exit;
    }
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?= $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/maintenance.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <div class="add_MET_section_header_1">
                    <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                    <label id="B"><?= htmlspecialchars($detailsMaintenance[0]['sci_name'] ?? '--', ENT_QUOTES, 'UTF-8'); ?></label>
                    <span>ได้รับการบำรุงรักษาไปทั้งหมด <?= htmlspecialchars($rowCount, ENT_QUOTES, 'UTF-8'); ?> ครั้ง</span>
                </div>
                <div class="add_MET_section_header_2">
                    <span id="details">ดูรายละเอียด</span><span></span>
                </div>
            </div>
            <div class="add_MET_section_form">
                <div class="maintenance_history">
                    <label for="sci_name">
                        ประวัติการบำรุงรักษาของ
                        <span id="B"><?= htmlspecialchars($detailsMaintenance[0]['sci_name'] ?? '--', ENT_QUOTES, 'UTF-8'); ?></span>
                    </label>
                    <?php if (is_array($detailsMaintenance)) : ?>
                        <?php foreach ($detailsMaintenance as $dataList) : ?>
                            <div class="maintenance_entry">
                                <span>
                                    <span id="B">เริ่มการบำรุงรักษาตั้งแต่</span>
                                    <?= htmlspecialchars(thai_date_time_3($dataList['start_maintenance'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    ถึง <?= htmlspecialchars(thai_date_time_3($dataList['end_maintenance'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span>
                                    <span id="B">ชื่อผู้ดูแล</span> <?= htmlspecialchars($dataList['name_staff'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span>
                                    <span id="B">หมายเหตุ</span> <?= htmlspecialchars($dataList['note'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span>
                                    <span id="B">รายละเอียดการบำรุงรักษา</span> <?= htmlspecialchars($dataList['details_maintenance'] ?? '--', ENT_QUOTES, 'UTF-8'); ?> </span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No maintenance history available.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="btn_footer">
                <?php if ($request_uri == '/maintenance/detailsMaintenance') : ?>
                    <span class="maintenance_button" id="B">บำรุงรักษา</span>
                    <form class="for_Maintenance" action="<?= $base_url ?>/Staff/maintenanceProcess.php" method="post">
                        <div class="maintenance_popup">
                            <div class="maintenance_popup_content">
                                <div class="maintenance_section_header">
                                    <span id="B">กรอกข้อมูลการบำรุงรักษา</span>
                                    <div class="modalClose" id="closeMaintenance">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="maintenace_popup">
                                    <div class="inputMaintenance">
                                        <label for="start_maintenance">วันเริ่มต้นการบำรุงรักษา</label>
                                        <input type="date" id="start_maintenance" name="start_maintenance" required>
                                    </div>
                                    <div class="inputMaintenance">
                                        <label for="end_maintenance">วันสิ้นสุดการบำรุงรักษา</label>
                                        <input type="date" id="end_maintenance" name="end_maintenance" required>
                                    </div>
                                    <div class="inputMaintenance">
                                        <label for="note">หมายเหตุ</label>
                                        <input type="text" id="note" name="note" placeholder="หมายเหตุ">
                                    </div>
                                    <div class="inputMaintenance">
                                        <label for="name_staff">ชื่อ - นามสกุล ผู้ดูแล</label>
                                        <input type="text" id="name_staff" name="name_staff" placeholder="ชื่อ - นามสกุล ผู้ดูแล">
                                    </div>
                                    <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="javascript:history.back();" class="del_notification">กลับ</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script src="<?= $base_url; ?>/assets/js/navigator.js"></script>
    <script src="<?= $base_url; ?>/assets/js/management_systems.js"></script>
    <script src="<?= $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>