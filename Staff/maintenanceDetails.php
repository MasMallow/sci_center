<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'assets/includes/thai_date_time.php';

// ดึงข้อมูลผู้ใช้เพียงครั้งเดียว
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
        $id = $_GET['id'];

        // Fetch data to edit
        $stmt = $conn->prepare("SELECT * FROM crud INNER JOIN info_sciname ON crud.serial_number = info_sciname.serial_number WHERE crud.ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $detailsData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/maintenance.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                <label id="B"><?php echo $detailsData['sci_name'] ?></label>
            </div>
            <div class="add_MET_section_form">
                <div class="form_left">
                    <div class="img">
                        <img src="../assets/uploads/<?php echo $detailsData['img_name']; ?>" class="previewImg">
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
            <div class="btn_footer">
                <form action="<?php echo $base_url ?>/Staff/maintenanceProcess.php" method="post">
                    <span class="maintenance_button" id="B">บำรุงรักษา</span>
                    <div class="maintenance_popup">
                        <input type="hidden" name="selected_ids[]" value="<?= htmlspecialchars($detailsData['ID'], ENT_QUOTES, 'UTF-8') ?>">
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
                    <a href="javascript:history.back();" class="del_notification">กลับ</a>
                </form>
            </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>