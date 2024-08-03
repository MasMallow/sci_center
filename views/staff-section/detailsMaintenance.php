<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
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

try {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT * FROM crud 
            LEFT JOIN info_sciname ON crud.serial_number = info_sciname.serial_number
            LEFT JOIN logs_maintenance ON info_sciname.serial_number = logs_maintenance.serial_number 
            WHERE info_sciname.ID = :id
        ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $detailsMaintenance = $stmt->fetch(PDO::FETCH_ASSOC); // ดึงข้อมูลมาเก็บในตัวแปร
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
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/detailsMaintenance.css">
</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php') ?>
    </header>
    <main class="detailsMaintenance">
        <div class="detailsMaintenance_header">
            <div class="detailsMaintenance_header_1">
                <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                <div class="breadcrumb">
                    <a href="/">หน้าหลัก</a>
                    <span>&gt;</span>
                    <?php
                    if (!empty($detailsMaintenance)) {
                        if ($detailsMaintenance[0]['categories'] == 'วัสดุ') {
                            echo '<a href="/material">วัสดุ</a><span>&gt;</span>';
                        }
                        if ($detailsMaintenance[0]['categories'] == 'อุปกรณ์') {
                            echo '<a href="/equipment">อุปกรณ์</a><span>&gt;</span>';
                        }
                        if ($detailsMaintenance[0]['categories'] == 'เครื่องมือ') {
                            echo '<a href="/tools">เครื่องมือ</a><span>&gt;</span>';
                        }
                        echo '<a href="' . htmlspecialchars($detailsMaintenance[0]['ID'], ENT_QUOTES, 'UTF-8') . '">'
                            . htmlspecialchars($detailsMaintenance[0]['sci_name'], ENT_QUOTES, 'UTF-8') . '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="maintenance_history">
            <div class="maintenance_history_header">
                ประวัติการบำรุงรักษา
            </div>
            <div class="maintenance_history_subheader">
                <span id="B"><?= htmlspecialchars($detailsMaintenance[0]['sci_name'] ?? '--', ENT_QUOTES, 'UTF-8'); ?></span>
                บำรุงรักษาทั้งหมด <?php echo count($detailsMaintenance); ?> ครั้ง
            </div>
            <div class="maintenanceContent">
                <?php if (is_array($detailsMaintenance) && count($detailsMaintenance) > 0) : ?>
                    <?php foreach ($detailsMaintenance as $dataList) : ?>
                        <div class="maintenance_entry">
                            <div>
                                <span id="B">บำรุงรักษา</span>
                                <?= htmlspecialchars(thai_date_time_3($dataList['start_maintenance'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                ถึง <?= htmlspecialchars(thai_date_time_3($dataList['end_maintenance'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div>
                                <span id="B">ชื่อผู้ดูแล</span> <?= htmlspecialchars($dataList['name_staff'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div>
                                <span id="B">หมายเหตุ</span> <?= htmlspecialchars($dataList['note'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div>
                                <span id="B">รายละเอียดการบำรุงรักษา</span> <?= htmlspecialchars($dataList['details_maintenance'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div>
                        <i class="fa-solid fa-database"></i>
                        <p>ไม่เคยบำรุงรักษา</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="btn_footer">
            <?php if ($request_uri == '/maintenance_start/details' || $request_uri == '/management/maintenance') : ?>
                <span class="maintenance_button" id="B">บำรุงรักษา</span>
                <form class="for_Maintenance" action="<?= $base_url ?>/staff-section/maintenanceProcess.php" method="post">
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
                                <input type="hidden" name="selected_ids" value="<?= htmlspecialchars($detailsMaintenance[0]['ID'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                            </div>
                        </div>
                    </div>
                </form>
                <a href="javascript:history.back();" class="del_notification">กลับ</a>
            <?php endif; ?>
        </div>
    </main>
    <script src="<?= $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?= $base_url; ?>/assets/js/management_systems.js"></script>
    <script src="<?= $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>