<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit();
}

try {
    $userID = $_SESSION['staff_login'];

    // ดึงข้อมูลผู้ใช้
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // ดึงข้อมูลการใช้งาน
    $users = $conn->prepare("SELECT * FROM users_db");
    $users->execute();
    $data = $users->fetchAll(PDO::FETCH_ASSOC);
    $userCount = count($data); // นับจำนวนรายการ

    // ดึงข้อมูลการอนุมัติการจอง
    $used = $conn->prepare("SELECT * FROM approve_to_reserve");
    $used->execute();
    $dataUsed = $used->fetchAll(PDO::FETCH_ASSOC);
    $usedCount = count($dataUsed); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (วัสดุ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'วัสดุ' ORDER BY serial_number");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $materialCount = count($data); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (อุปกรณ์)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'อุปกรณ์' ORDER BY serial_number");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $equipmentCount = count($data); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (เครื่องมือ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'เครื่องมือ' ORDER BY serial_number");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $toolsCount = count($data); // นับจำนวนรายการ

    // ดึงข้อมูลการบำรุงรักษา
    $stmt = $conn->prepare("SELECT ID FROM logs_maintenance");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $maintenanceCount = count($data); // นับจำนวนรายการ

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบันทึกข้อมูล</title>
    <link href="<?php echo $base_url ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/viewLog.css">
</head>

<body>
    <header>
        <?php include_once 'assets/includes/navigator.php'; ?>
    </header>
    <main class="viewLOG">
        <div class="header_viewLog">
            <a href="javascript:history.back()"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">ระบบบันทึก LOG </span>
        </div>
        <div class="btn_viewLog_all">
            <a href="/view_log" class="<?= ($request_uri == '/view_log') ? 'active' : ''; ?> btn_viewLog_01">
                แดชบอร์ด</a>
            <a href="/view_log/approve_request" class="<?= ($request_uri == '/view_log/approve_request') ? 'active' : ''; ?> btn_viewLog_02">
                การขอใช้</a>
            <a href="/view_log/approve_users" class="<?= ($request_uri == '/view_log/approve_users') ? 'active' : ''; ?> btn_viewLog_02">
                บัญชีผู้ใช้</a>
            <a href="/view_log/management" class="<?= ($request_uri == '/view_log/management') ? 'active' : ''; ?> btn_viewLog_02">
                จัดการระบบข้อมูล</a>
            <a href="/view_log/maintenance" class="<?= ($request_uri == '/view_log/maintenance') ? 'active' : ''; ?> btn_viewLog_02">
                การบำรุงรักษา</a>
            <a href="/view_log/view_report" class="<?= ($request_uri == '/view_log/view_report') ? 'active' : ''; ?> btn_viewLog_03">
                ประวัติการขอใช้</a>
        </div>

        <!-- ------------------ VIEW_LOG CONTENT ------------------ -->

        <div class="viewLog_content_MAIN">
            <div class="viewLog_content_1">
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนบัญชีทั้งหมดในระบบ</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $userCount; ?></span>บัญชี
                    </div>
                </div>
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนการขอใช้ทั้งหมด</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $usedCount; ?></span>ครั้ง
                    </div>
                </div>
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนการบำรุงรักษาทั้งหมด</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $maintenanceCount; ?></span>ครั้ง
                    </div>
                </div>
            </div>
            <div class="viewLog_content_1">
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนวัสดุทั้งหมด</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $userCount; ?></span>จำนวน
                    </div>
                </div>
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนอุปกรณ์ทั้งหมด</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $usedCount; ?></span>จำนวน
                    </div>
                </div>
                <div class="Content_1">
                    <div class="Content_1_header">
                        <span id="B">จำนวนเครื่องมือทั้งหมด</span>
                    </div>
                    <div class="Content_1_body">
                        <span id="B"><?php echo $maintenanceCount; ?></span>จำนวน
                    </div>
                </div>
            </div>
        </div>

        <!-- ------------------- REQUEST CONTENT  ------------------ -->

        <div class="viewLog_request">
            <div class="viewLog_request_header">
                <span id="B">การขอใช้</span>
            </div>
            <div class="viewLog_request_body">
                <?php foreach ($usedCount as $Data) : ?>
                    <div class="approve_row">
                        <div class="defualt_row">
                            <div class="serial_number">
                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                <?php echo htmlspecialchars($row['serial_number']); ?>
                            </div>
                            <div class="items">
                                <a href="<?php echo $base_url; ?>/maintenance/detailsMaintenance?id=<?= $row['ID'] ?>">
                                    <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                            <div class="reservation_date">
                                <?php
                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($row['last_maintenance_date']);
                                if ($daysSinceMaintenance === "ไม่เคยได้รับการบำรุงรักษา") {
                                    echo $daysSinceMaintenance;
                                } else {
                                    echo "ไม่ได้รับการบำรุงรักษามามากกว่า " . $daysSinceMaintenance . " วัน";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ------------------- REQUEST CONTENT  ------------------ -->

        <div class="viewLog_request">
            <div class="viewLog_request_header">
                <span id="B">การขอใช้</span>
            </div>
            <div class="viewLog_request_body">
                <?php foreach ($usedCount as $Data) : ?>
                    <div class="approve_row">
                        <div class="defualt_row">
                            <div class="serial_number">
                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                <?php echo htmlspecialchars($row['serial_number']); ?>
                            </div>
                            <div class="items">
                                <a href="<?php echo $base_url; ?>/maintenance/detailsMaintenance?id=<?= $row['ID'] ?>">
                                    <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                            <div class="reservation_date">
                                <?php
                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($row['last_maintenance_date']);
                                if ($daysSinceMaintenance === "ไม่เคยได้รับการบำรุงรักษา") {
                                    echo $daysSinceMaintenance;
                                } else {
                                    echo "ไม่ได้รับการบำรุงรักษามามากกว่า " . $daysSinceMaintenance . " วัน";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ------------------- REQUEST CONTENT  ------------------ -->

        <div class="viewLog_request">
            <div class="viewLog_request_header">
                <span id="B">การขอใช้</span>
            </div>
            <div class="viewLog_request_body">
                <?php foreach ($usedCount as $Data) : ?>
                    <div class="approve_row">
                        <div class="defualt_row">
                            <div class="serial_number">
                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                <?php echo htmlspecialchars($row['serial_number']); ?>
                            </div>
                            <div class="items">
                                <a href="<?php echo $base_url; ?>/maintenance/detailsMaintenance?id=<?= $row['ID'] ?>">
                                    <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                            <div class="reservation_date">
                                <?php
                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($row['last_maintenance_date']);
                                if ($daysSinceMaintenance === "ไม่เคยได้รับการบำรุงรักษา") {
                                    echo $daysSinceMaintenance;
                                } else {
                                    echo "ไม่ได้รับการบำรุงรักษามามากกว่า " . $daysSinceMaintenance . " วัน";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ------------------- REQUEST CONTENT  ------------------ -->

        <div class="viewLog_request">
            <div class="viewLog_request_header">
                <span id="B">การขอใช้</span>
            </div>
            <div class="viewLog_request_body">
                <?php foreach ($usedCount as $Data) : ?>
                    <div class="approve_row">
                        <div class="defualt_row">
                            <div class="serial_number">
                                <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                <?php echo htmlspecialchars($row['serial_number']); ?>
                            </div>
                            <div class="items">
                                <a href="<?php echo $base_url; ?>/maintenance/detailsMaintenance?id=<?= $row['ID'] ?>">
                                    <?= htmlspecialchars($row['sci_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                            <div class="reservation_date">
                                <?php
                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($row['last_maintenance_date']);
                                if ($daysSinceMaintenance === "ไม่เคยได้รับการบำรุงรักษา") {
                                    echo $daysSinceMaintenance;
                                } else {
                                    echo "ไม่ได้รับการบำรุงรักษามามากกว่า " . $daysSinceMaintenance . " วัน";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>

</html>