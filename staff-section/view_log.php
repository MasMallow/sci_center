<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ใช้
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
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
    $UserData = $users->fetchAll(PDO::FETCH_ASSOC);
    $userCount = count($UserData); // นับจำนวนรายการ

    // ดึงข้อมูลการอนุมัติการจอง
    $used = $conn->prepare("SELECT * FROM approve_to_reserve");
    $used->execute();
    $dataUsed = $used->fetchAll(PDO::FETCH_ASSOC);
    $usedCount = count($dataUsed); // นับจำนวนรายการ

    // ดึงข้อมูลการอนุมัติการจอง
    $stmt = $conn->prepare("SELECT * FROM logs_management");
    $stmt->execute();
    $Management = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ManagementCount = count($Management); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (วัสดุ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'วัสดุ' ORDER BY serial_number");
    $stmt->execute();
    $material = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $materialCount = count($material); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (อุปกรณ์)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'อุปกรณ์' ORDER BY serial_number");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $equipmentCount = count($equipment); // นับจำนวนรายการ

    // ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ (เครื่องมือ)
    $stmt = $conn->prepare("SELECT ID FROM crud WHERE categories = 'เครื่องมือ' ORDER BY serial_number");
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $toolsCount = count($tools); // นับจำนวนรายการ

    // ดึงข้อมูลการบำรุงรักษา
    $stmt = $conn->prepare("SELECT ID FROM logs_maintenance");
    $stmt->execute();
    $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $maintenanceCount = count($maintenance); // นับจำนวนรายการ

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
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/footer.css">
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
            <a href="/view_log/management" class="<?= ($request_uri == '/view_log/management') ? 'active' : ''; ?> btn_viewLog_03">
                จัดการระบบข้อมูล</a>
        </div>

        <?php if ($request_uri == '/view_log') : ?>

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

            <!-- ------------------- MANAGEMENT CONTENT  ------------------ -->
            <div class="viewLog_request">
                <div class="viewLog_request_header">
                    <span id="B">การจัดการระบบคลัง</span>
                </div>
                <div class="viewLog_request_body">
                    <?php if (!empty($Management)) : ?>
                        <?php foreach ($Management as $Data) : ?>
                            <div class="viewLog_request_content">
                                <div class="viewLog_Management_row">
                                    <div class="log_Name">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right"></i>
                                        <?php echo htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php echo htmlspecialchars($Data['log_Role'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="log_Date">
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="log_Status">
                                        ได้ทำการ
                                        <?php
                                        if ($Data['log_Status'] === 'Add') {
                                            echo "เพิ่มข้อมูล";
                                        } elseif ($Data['log_Status'] === 'Edit') {
                                            echo "แก้ไขข้อมูล";
                                        } elseif ($Data['log_Status'] === 'Delete') {
                                            echo "ลบข้อมูล";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="viewNotfound">
                            <i class="fa-solid fa-database"></i>
                            <span id="B">ไม่พบข้อมูล</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- -------------------------- REQUEST PAGE ----------------------------- -->
        <?php elseif ($request_uri == '/view_log/approve_request') : ?>
            <?php if (!empty($dataUsed)) : ?>
                <div class="viewLog_request_PAGE">
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <span id="B">การขอใช้</span>
                        </div>
                        <div class="viewLog_request_body">
                            <?php foreach ($dataUsed as $Data) : ?>
                                <div class="viewLog_request_content">
                                    <div class="list_name">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                        <a href="<?php echo $base_url; ?>/view_log/approve_request/details?id=<?= $Data['ID'] ?>">
                                            <?php echo htmlspecialchars($Data['list_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= thai_date_time(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        ผู้อนุมัติ
                                        <?= htmlspecialchars($Data['approver'], ENT_QUOTES, 'UTF-8') ?>
                                        เมื่อ
                                        <?= thai_date_time_2(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>
        <?php elseif ($request_uri == '/view_log/approve_request/details') : ?>
            <?php
            try {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("
                    SELECT * FROM approve_to_reserve                           
                    WHERE ID = :id
                ");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $detailsdataUsed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            } ?>
            <?php if (!empty($detailsdataUsed)) : ?>
                <div class="viewLog_request_Details">
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <div class="path-indicator">
                                <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        </div>
                        <div class="viewLog_request_body">
                            <?php foreach ($detailsdataUsed as $Data) : ?>
                                <div class="viewLog_request_content">
                                    <div class="viewLog_request_content_1">
                                        <span id="B">ชื่อรายการ</span>
                                        <?= htmlspecialchars($Data['list_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?= htmlspecialchars($Data['serial_number'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="viewLog_request_content_2">
                                        <span id="B">ชื่อผู้ขอใช้</span>
                                        <?= htmlspecialchars($Data['name_user'], ENT_QUOTES, 'UTF-8') ?>
                                        <span id="B">ขอใช้</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="viewLog_request_content_3">
                                        <span id="B">สิ้นสุด</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['end_date'], ENT_QUOTES, 'UTF-8')) ?>
                                        <span id="B">วันที่คืน</span>
                                        <?php if ($Data['date_return'] === NULL) : ?>
                                            --
                                        <?php else : ?>
                                            <?= thai_date_time_2(htmlspecialchars($Data['date_return'], ENT_QUOTES, 'UTF-8')); ?>
                                        <?php endif ?>
                                    </div>
                                    <div class="viewLog_request_content_4">
                                        <span id="B">ผู้อนุมัติ</span>
                                        <?= htmlspecialchars($Data['approver'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= thai_date_time_2(htmlspecialchars($Data['approvaldatetime'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>

            <!-- -------------------------- USER PAGE -------------------------------- -->
        <?php elseif ($request_uri == '/view_log/approve_users') : ?>
            <?php if (!empty($UserData)) : ?>
                <div class="viewLog_User_PAGE">
                    <div class="viewLog_User_MAIN">
                        <div class="viewLog_User_header">
                            <span id="B">บัญชีผู้ใช้</span>
                        </div>
                        <div class="viewLog_User_body">
                            <?php foreach ($UserData as $Data) : ?>
                                <div class="viewLog_User_content">
                                    <div class="viewLog_User_content_1">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                        <a href="<?= $base_url; ?>/view_log/approve_users/details?id=<?= $Data['userID'] ?>">
                                            <?= $Data['userID'] ?>
                                            <?= htmlspecialchars($Data['pre'], ENT_QUOTES, 'UTF-8') . htmlspecialchars($Data['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Data['lastname'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                    <div class="viewLog_User_content_2">
                                        <span id="B">ตำแหน่ง</span>
                                        <?= htmlspecialchars($Data['role'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="viewLog_User_content_3">
                                        <span id="B">หน่วยงาน</span>
                                        <?= htmlspecialchars($Data['agency'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="viewLog_User_content_4">
                                        <span id="B">สถานะ</span>
                                        <?php if ($Data['status'] == 'w_approved') echo 'รอการอนุมัติ'; ?>
                                        <?php if ($Data['status'] == 'approved') echo 'อนุมัติ'; ?>
                                        <?php if ($Data['status'] == 'n_approved') echo 'ระงับการอนุมัติ'; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>
        <?php elseif ($request_uri == '/view_log/approve_users/details') : ?>
            <?php
            try {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("
                SELECT * FROM users_db                           
                WHERE userID = :id
            ");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $detailsdataUsed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            } ?>
            <?php if (!empty($detailsdataUsed)) : ?>
                <div class="viewLog_request_Details">
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <div class="path-indicator">
                                <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        </div>
                        <div class="viewLog_request_body">
                            <?php foreach ($detailsdataUsed as $Data) : ?>
                                <div class="viewLog_request_content">
                                    <div class="list_name">
                                        <a href="<?= $base_url; ?>/view_log/approve_users/details?id=<?= $Data['userID'] ?>">
                                            <?= $Data['userID'] ?>
                                            <?= htmlspecialchars($Data['pre'], ENT_QUOTES, 'UTF-8') . htmlspecialchars($Data['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Data['lastname'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= thai_date_time_2(htmlspecialchars($Data['created_at'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        ผู้อนุมัติ
                                        <?= htmlspecialchars($Data['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= format_phone_number(htmlspecialchars($Data['phone_number'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['role'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['agency'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['approved_by'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['approved_date'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>

            <!-- -------------------------- MANAGEMENT PAGE -------------------------- -->
        <?php elseif ($request_uri == '/view_log/management') : ?>
            <?php if (!empty($Management)) : ?>
                <div class="viewLog_Management_PAGE">
                    <div class="viewLog_Management_MAIN">
                        <div class="viewLog_Management_header">
                            <span id="B">การจัดการระบบคลัง</span>
                        </div>
                        <div class="viewLog_Management_body">
                            <?php foreach ($Management as $Data) : ?>
                                <div class="viewLog_Management_content">
                                    <div class="viewLog_User_content_1">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right"></i>
                                        <a href="<?= $base_url . '/view_log/management/details?id=' . htmlspecialchars($Data['ID'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8') ?>
                                            ( <?= htmlspecialchars($Data['log_Role'], ENT_QUOTES, 'UTF-8') ?>) </a>
                                    </div>
                                    <div class="viewLog_User_content_2">
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="viewLog_User_content_3">
                                        ได้ทำการ
                                        <?php
                                        switch ($Data['log_Status']) {
                                            case 'Add':
                                                echo "เพิ่มข้อมูล";
                                                break;
                                            case 'Edit':
                                                echo "แก้ไขข้อมูล";
                                                break;
                                            case 'Delete':
                                                echo "ลบข้อมูล";
                                                break;
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>
        <?php elseif ($request_uri == '/view_log/management/details') : ?>
            <?php
            try {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("SELECT * FROM logs_management WHERE ID = :id");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $detailsManagement = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            } ?>
            <?php if (!empty($detailsManagement)) : ?>
                <div class="viewLog_Management_Details">
                    <div class="viewLog_Management_MAIN">
                        <div class="viewLog_Management_header">
                            <div class="path-indicator">
                                <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        </div>
                        <div class="viewLog_Management_body">
                            <?php foreach ($detailsManagement as $Data) : ?>
                                <div class="viewLog_Management_content">
                                    <div class="viewLog_Management_content_1">
                                        <?= htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($Data['log_Role'], ENT_QUOTES, 'UTF-8') ?>)
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                        ได้ทำการ
                                        <?php
                                        switch ($Data['log_Status']) {
                                            case 'Add':
                                                echo "เพิ่มข้อมูล";
                                                break;
                                            case 'Edit':
                                                echo "แก้ไขข้อมูล";
                                                break;
                                            case 'Delete':
                                                echo "ลบข้อมูล";
                                                break;
                                        }
                                        ?>
                                    </div>
                                    <div class="viewLog_Management_content_2">
                                        <?php
                                        $logContent = json_decode($Data['log_Content'], true);
                                        if ($logContent) : ?>
                                            <div class="log-item"><span id="B">ชื่อวิทยาศาสตร์</span> <?= htmlspecialchars($logContent['sci_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="log-item"><span id="B">หมายเลขซีเรียล</span> <?= htmlspecialchars($logContent['serial_number'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php else : ?>
                                            ไม่สามารถแสดงข้อมูลได้
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <footer class="small">
        <div class="footer-content">
            <div class="footer-copyright">
                <span>Copyright © 2024 ศูนย์วิทยาศาสตร์เทคโนโลยี</span>
                <span>Designed And Developed By Puwadech and Phisitphong. All Rights Reserved</span>
            </div>
    </footer>
</body>

</html>