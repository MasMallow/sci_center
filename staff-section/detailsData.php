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

try {
    // ตรวจสอบว่ามีค่าพารามิเตอร์ 'id' ที่ถูกส่งมาหรือไม่
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // เตรียมการดึงข้อมูลเพื่อทำการแก้ไข
        $stmt = $conn->prepare("
                SELECT * FROM crud 
                INNER JOIN info_sciname 
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
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <div class="add_MET_section_header_1">
                    <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                    <label id="B"><?php echo $detailsData['sci_name'] ?></label>
                </div>
                <div class="add_MET_section_header_2">
                    <button class="details <?php if ($request_uri == '/management/detailsData') echo 'active' ?> ">รายละเอียด</button>
                    <button class="maintenance_history <?php if ($request_uri == '/maintenance/detailsData') echo 'active' ?> ">บำรุงรักษา</button>
                </div>
            </div>
            <div class="add_MET_section_form_1 
                    <?php if ($request_uri == '/management/detailsData') echo 'active_1' ?> 
                    <?php if ($request_uri == '/maintenance/detailsData') echo '' ?>">
                <div class="form_left">
                    <div class="Img">
                        <div class="imgInput">
                            <img src="../assets/uploads/<?php echo $detailsData['img_name']; ?>" class="previewImg">
                        </div>
                    </div>
                </div>
                <div class="form_right">
                    <div class="input_Data">
                        <label for="sci_name">ชื่อ</label>
                        <span><?php echo $detailsData['sci_name'] ?></span>
                    </div>
                    <div class="input_Data">
                        <label for="serial_number">Serial Number</label>
                        <span><?php echo $detailsData['serial_number'] ?></span>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="categories">ประเภท</label>
                            <span><?php echo $detailsData['categories'] ?></span>
                        </div>
                        <div class="input_Data">
                            <label for="amount">จำนวน</label>
                            <span><?php echo $detailsData['amount'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="installation_date">วันที่ติดตั้ง</label>
                            <span><?php echo thai_date_time_3($detailsData['installation_date']) ?></span>
                        </div>
                        <div class="input_Data">
                            <label for="last_maintenance_date">วันที่บำรุงรักษาล่าสุด</label>
                            <span><?php echo thai_date_time_3($detailsData['last_maintenance_date']) ?></span>
                        </div>
                    </div>
                    <div class="input_Data">
                        <label for="company">บริษัท</label>
                        <span><?php echo $detailsData['company'] ?></span>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="contact_number">เบอร์โทรศัพท์บริษัท</label>
                            <span><?php echo $detailsData['contact_number'] ?></span>
                        </div>
                        <div class="input_Data">
                            <label for="contact">คนติดต่อ</label>
                            <span><?php echo $detailsData['contact'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="brand">ยี่ห้อ</label>
                            <span><?php echo $detailsData['brand'] ?></span>
                        </div>
                        <div class="input_Data">
                            <label for="model">รุ่น</label>
                            <span><?php echo $detailsData['model'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="details">Details</label>
                            <span><?php echo $detailsData['details'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="add_MET_section_form_2 
                    <?php if ($request_uri == '/maintenance/detailsData') echo 'active_2' ?>">
                <div class="maintenance_history">
                    <div class="maintenance_history_header">
                    <?php if (is_array($detailsMaintenance) && !empty($detailsMaintenance)) : ?>
                        <div>
                            ประวัติการบำรุงรักษาของ
                            <span id="B"><?= htmlspecialchars($detailsMaintenance[0]['sci_name'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>
                                (<?= htmlspecialchars($detailsMaintenance[0]['serial_number'] ?? '--', ENT_QUOTES, 'UTF-8'); ?>)</span>
                            <span>ได้รับการบำรุงรักษาไปทั้งหมด <?= htmlspecialchars($rowCount, ENT_QUOTES, 'UTF-8'); ?> ครั้ง</span>
                        </div>
                        <div>
                            บำรุงรักษาล่าสุดเมื่อ
                            <?php
                            if (isset($detailsMaintenance[0]['last_maintenance_date'])) {
                                echo thai_date_time_4($detailsMaintenance[0]['last_maintenance_date']);
                                $daysSinceMaintenance = calculateDaysSinceLastMaintenance($detailsMaintenance[0]['last_maintenance_date']);
                                if ($daysSinceMaintenance === "( ไม่เคยได้รับการบำรุงรักษา )") {
                                    echo $daysSinceMaintenance;
                                } else {
                                    echo "  ( ไม่ได้รับการบำรุงรักษามามากกว่า " . htmlspecialchars($daysSinceMaintenance, ENT_QUOTES, 'UTF-8') . " วัน )";
                                }
                            } else {
                                echo "( ไม่เคยได้รับการบำรุงรักษา )";
                            }
                            ?>
                            
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (is_array($detailsMaintenance) && !empty($detailsMaintenance)) : ?>
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
                        <div class="maintenance_entry_non"><span id="B">ไม่มีประวัติการบำรุงรักษา</span></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="btn_footer">
                <?php if ($request_uri == '/maintenance/detailsData') : ?>
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
                                    <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="javascript:history.back();" class="del_notification">กลับ</a>
                <?php endif; ?>
                <?php if ($request_uri == '/management/detailsData') : ?>
                    <input type="hidden" name="id" value="<?php echo $detailsData['ID']; ?>">
                    <a href="<?php echo $base_url; ?>/management/editData?id=<?= $detailsData['ID'] ?>" class="submitADD">แก้ไขข้อมูล</a>
                    <span class="del_notification" data-modal="<?= $detailsData['ID'] ?>">ลบข้อมูล</span>
                    <div class="del_notification_alert" id="<?php echo htmlspecialchars($detailsData['ID']); ?>">
                        <div class="del_notification_content">
                            <div class="del_notification_popup">
                                <div class="del_notification_sec01">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span id="B">แจ้งเตือนการลบข้อมูล</span>
                                </div>
                                <div class="del_notification_sec02">
                                    <form action="<?php echo $base_url; ?>/staff-section/deleteData.php" method="post">
                                        <input type="hidden" name="ID_deleteData" value="<?= $detailsData['ID'] ?>">
                                        <button type="submit" class="confirm_del">ยืนยัน</button>
                                    </form>
                                    <div class="cancel_del" id="closeDetails">
                                        <span id="B">ปิดหน้าต่าง</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/add.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>