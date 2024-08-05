<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
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
    $uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $id = end($uriSegments);

    if (is_numeric($id)) {
        $stmt = $conn->prepare("
            SELECT * FROM crud 
            LEFT JOIN info_sciname 
            ON crud.serial_number = info_sciname.serial_number 
            WHERE crud.ID = :id");

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
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
    <title>รายละเอียดวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">
    <style>
        .form_right_2 {
            display: none;
        }
    </style>
</head>

<body>
    <?php include('assets/includes/navigator.php') ?>
    <main class="DetailsPAGE">
        <div class="DetailsPAGE_header">
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
                    <div class="formHEADER">
                        รายละเอียด
                        <button id="toggleButton">ตรวจสอบการขอใช้</button>
                    </div>
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
                    <div class="form_right_2">
                        <div class="headerNAME">
                            <span id="B"><?php echo $detailsData['sci_name'] ?></span>
                            <span class="serialNumber">(<?php echo $detailsData['serial_number'] ?>)</span>
                        </div>
                        <div class="DataDisplay">
                            <div class="reseration">
                                <span id="B">ขอใช้งาน </span>
                                <span>วันที่ 5 ส.ค. พ.ศ. 2567 เวลา 09:00 น.</span>
                            </div>
                            <div class="endDate">
                                <span id="B">ถึง </span>
                                <span>วันที่ 5 ส.ค. พ.ศ. 2567 เวลา 12:00 น.</span>
                            </div>
                        </div>
                        <div class="DataDisplay">
                            <div class="reseration">
                                <span id="B">ขอใช้งาน </span>
                                <span>วันที่ 7 ส.ค. พ.ศ. 2567 เวลา 09:00 น.</span>
                            </div>
                            <div class="endDate">
                                <span id="B">ถึง </span>
                                <span>วันที่ 7 ส.ค. พ.ศ. 2567 เวลา 12:00 น.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="DetailsPAGE_footer">
            <?php if ($detailsData['amount'] >= 1  && ($detailsData['availability'] == 0)) : ?>
                <a href="<?php echo $base_url; ?>/cart?action=add&item=<?= htmlspecialchars($detailsData['sci_name']) ?>" class="used_it">
                    <i class="fa-solid fa-address-book"></i>
                    <span>ขอใช้</span>
                </a>
            <?php else : ?>
                <div class="notAvailable">
                    <i class="fa-solid fa-ban"></i>
                    <span>ไม่พร้อมใช้งาน</span>
                </div>
            <?php endif; ?>
            <a href="javascript:history.back();" class="go_back"><i class="fa-solid fa-arrow-left-long"></i><span>กลับ</span></a>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formRight1 = document.querySelector('.form_right_1');
            const formRight2 = document.querySelector('.form_right_2');
            const toggleButton = document.getElementById('toggleButton');

            // รีเซ็ตค่า formState เป็น form1 เมื่อเริ่มต้นหน้าเว็บถ้า formState ยังไม่ได้ตั้งไว้
            if (!localStorage.getItem('formState')) {
                localStorage.setItem('formState', 'form1');
            }

            function setFormVisibility() {
                const formState = localStorage.getItem('formState');
                if (formState === 'form1') {
                    formRight1.style.display = 'block';
                    formRight2.style.display = 'none';
                    toggleButton.innerHTML = '<i class="fa-solid fa-check-to-slot"></i> ตรวจสอบการขอใช้';
                } else {
                    formRight1.style.display = 'none';
                    formRight2.style.display = 'block';
                    toggleButton.innerHTML = '<i class="fa-solid fa-circle-info"></i> รายละเอียด';
                }
            }

            toggleButton.addEventListener('click', function() {
                const formState = localStorage.getItem('formState') === 'form1' ? 'form2' : 'form1';
                localStorage.setItem('formState', formState);
                setFormVisibility();
            });

            setFormVisibility();
        });
    </script>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/add.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/maintenance.js"></script>
    <?php include('assets/includes/footer.php') ?>
</body>

</html>