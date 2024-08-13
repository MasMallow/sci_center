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
    // ตรวจสอบว่ามีค่าพารามิเตอร์ 'id' ที่ถูกส่งมาหรือไม่
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // เตรียมการดึงข้อมูลเพื่อทำการแก้ไข
        $stmt = $conn->prepare("
                SELECT * FROM crud 
                LEFT JOIN info_sciname 
                ON crud.serial_number = info_sciname.serial_number 
                WHERE crud.ID = :id");

        // ผูกค่าพารามิเตอร์ ':id' กับตัวแปร $id
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        // ทำการ execute คำสั่ง SQL
        $stmt->execute();

        // ดึงข้อมูลที่ได้จากการ execute มาเก็บในตัวแปร $detailsData
        $detailsData = $stmt->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่า $detailsData ไม่เป็น false
        if ($detailsData === false) {
            echo "No data found for ID: $id in first query.";
        }
    }
} catch (PDOException $e) {
    // แสดงข้อความข้อผิดพลาดถ้าเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล
    echo 'Error: ' . $e->getMessage();
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
    <title>รายละเอียดวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/DetailsCRUD.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="detailsData">
        <!-- <------------ HEADER FORM ----------------->
        <div class="detailsData_header">
            <div class="detailsData_header_1">
                <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                <div class="breadcrumb">
                    <a href="/">หน้าหลัก</a>
                    <span>&gt;</span>
                    <?php
                    if ($detailsData['categories'] == 'วัสดุ') {
                        echo '<a href="/material">วัสดุ</a>
                    <span>&gt;</span>';
                    }
                    if ($detailsData['categories'] == 'อุปกรณ์') {
                        echo '<a href="/equipment">อุปกรณ์</a>
                    <span>&gt;</span>';
                    }
                    if ($detailsData['categories'] == 'เครื่องมือ') {
                        echo '<a href="/tools">เครื่องมือ</a>
                    <span>&gt;</span>';
                    }
                    ?>
                    <a href="<?php echo $detailsData['ID']; ?>"><?php echo $detailsData['sci_name']; ?></a>
                </div>
            </div>
            <div class="detailsData_header_2">
                <a href="/management/maintenance?id=<?php echo $detailsData['ID']; ?>">ประวัติการบำรุงรักษา</a>
            </div>
        </div>
        <!-- <------------ FORM ----------------->
        <div class="DetailsPAGE_content">
            <div class="form_left">
                <div class="Img">
                    <div class="imgInput">
                        <img src="<?php echo $base_url; ?>/assets/uploads/<?php echo $detailsData['img_name']; ?>" class="previewImg">
                    </div>
                </div>
            </div>
            <div class="form_right">
                <div>
                    <div class="formHEADER">รายละเอียด</div>
                    <div class="form_right_1">
                        <div class="headerNAME">
                            <span id="B"><?php echo $detailsData['sci_name'] ?></span>
                            <span class="serialNumber">(<?php echo $detailsData['serial_number'] ?>)</span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">ประเภท</span>
                            <span class="Data2"><?php echo $detailsData['categories'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">จำนวน</span>
                            <span class="Data2"><?php echo $detailsData['amount'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">วันที่ติดตั้ง</span>
                            <span class="Data2"><?php echo thai_date_time_3($detailsData['installation_date']) ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">วันที่บำรุงรักษาล่าสุด</span>
                            <span class="Data2"><?php echo thai_date_time_3($detailsData['last_maintenance_date']) ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">บริษัท</span>
                            <span class="Data2"><?php echo $detailsData['company'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">เบอร์โทรศัพท์บริษัท</span>
                            <span class="Data2"><?php echo $detailsData['contact_number'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">คนติดต่อ</span>
                            <span class="Data2"><?php echo $detailsData['contact'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">ยี่ห้อ</span>
                            <span class="Data2"><?php echo $detailsData['brand'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">รุ่น</span>
                            <span class="Data2"><?php echo $detailsData['model'] ?></span>
                        </div>
                        <div class="DataDisplay">
                            <span class="Data1">รายละเอียด</span>
                            <span class="Data2"><?php echo $detailsData['details'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <------------ FOOTER FORM ----------------->
        <div class="btn_footer">
            <?php if ($detailsData['availability'] == 0) : ?>
                <div class="MaintenanceButton">
                    <span class="maintenance_button" data-modal="<?php echo $detailsData['serial_number']; ?>">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </span>
                    <form action="<?php echo $base_url ?>/models/maintenanceProcess.php" method="post" class="maintenance_form">
                        <div class="maintenance_popup" id="<?php echo $detailsData['serial_number']; ?>">
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
                                    <input type="text" name="serialNumber" value="<?= htmlspecialchars($detailsData['serial_number']); ?>">
                                    <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Edit Section -->
            <div>
                <input type="hidden" name="id" value="<?= htmlspecialchars($detailsData['ID']); ?>">
                <a href="<?= $base_url ?>/management/edit?id=<?= htmlspecialchars($detailsData['ID']); ?>" class="submitADD">แก้ไขข้อมูล</a>
            </div>

            <!-- Delete Section -->
            <div>
                <span class="del_notification" data-modal="<?= htmlspecialchars($detailsData['ID']); ?>">ลบข้อมูล</span>
                <div class="del_notification_alert" data-id="<?= htmlspecialchars($detailsData['ID']); ?>">
                    <div class="del_notification_content">
                        <div class="del_notification_popup">
                            <div class="del_notification_sec01">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span id="B">แจ้งเตือนการลบข้อมูล</span>
                            </div>
                            <div class="del_notification_sec02">
                                <form action="<?= $base_url ?>/models/CRUD.php" method="post">
                                    <input type="hidden" name="ID_deleteData" value="<?= htmlspecialchars($detailsData['ID']); ?>">
                                    <button type="submit" name="deleteDATA" class="confirm_del">ยืนยัน</button>
                                </form>
                                <div class="cancel_del closeDetails" data-modal="<?= htmlspecialchars($detailsData['ID']); ?>">
                                    <span>ปิดหน้าต่าง</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/previewImg_popup.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>