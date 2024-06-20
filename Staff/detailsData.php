<?php
session_start();
require_once 'assets/database/dbConfig.php';

if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db 
        LEFT JOIN users_info_db 
        ON users_db.userID = users_info_db.userID 
        WHERE users_db.userID = :userID
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
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <a href="javascript.history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
                <label id="B"><?php echo $detailsData['sci_name'] ?></label>
            </div>
            <div class="add_MET_section_form">
                <div class="input">
                    <div class="img">
                        <div class="imgInput">
                            <img src="../assets/uploads/<?php echo $detailsData['img_name']; ?>" class="previewImg" id="previewImg">
                        </div>
                    </div>
                </div>
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
                        <span><?php echo $detailsData['installation_date'] ?></span>
                    </div>
                    <div class="input_Data">
                        <label for="company">บริษัท</label>
                        <span><?php echo $detailsData['company'] ?></span>
                    </div>
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
            <div class="btn_footer">
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
                                <form action="<?php echo $base_url;?>/Staff/deleteData.php" method="post">
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
            </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/add.js"></script>
</body>

</html>