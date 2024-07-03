<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

if (isset($_SESSION['user_login'])) {
    $userID = $_SESSION['user_login'];
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
    }
} catch (PDOException $e) {
    // แสดงข้อความข้อผิดพลาดถ้าเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล
    echo 'Error: ' . $e->getMessage();
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
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="DetailsPAGE">
        <!-- <------------ HEADER FORM ----------------->
        <div class="DetailsPAGE_header">
            <div class="add_MET_section_header_1">
                <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">รายละเอียด</span>
            </div>
        </div>

        <!-- <------------ DETAILS FORM ----------------->
        <div class="DetailsPAGE_content">
            <div class="form_left">
                <div class="Img">
                    <div class="imgInput">
                        <img src="<?php echo $base_url;?>/assets/uploads/<?php echo $detailsData['img_name']; ?>" class="previewImg">
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
                        <label for="details">รายละเอียด</label>
                        <span><?php echo $detailsData['details'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- <------------ FOOTER FORM ----------------->
        <div class="DetailsPAGE_footer">
            <a href="<?php echo $base_url;?>/Cart?action=add&item=<?= htmlspecialchars($detailsData['sci_name']) ?>" class="used_it">
                <i class="fa-solid fa-address-book"></i>
                <span>ทำการขอใช้</span>
            </a>
            <a href="javascript:history.back();" class="go_back">กลับ</a>
        </div>
        <!-- <------------ FOOTER FORM ----------------->
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/add.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/maintenance.js"></script>
</body>

</html>