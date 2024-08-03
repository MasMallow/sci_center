<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลผู้ใช้
$userID = $_SESSION['staff_login'];
$stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM assets");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM assets WHERE status = 1");
$stmt->execute();
$dataSelect = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบว่ามีการส่งคำขออัปเดตสถานะ
if (isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = isset($_POST['status']) ? 1 : 0;

    // เริ่มต้นการทำธุรกรรม
    $conn->beginTransaction();

    try {
        // อัปเดตสถานะของรายการที่เลือก
        $stmt = $conn->prepare("UPDATE assets SET status = :status WHERE ID = :id");
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // ทำการ commit
        $conn->commit();

        $_SESSION['success'] = "อัปเดตสถานะสำเร็จ";
    } catch (Exception $e) {
        // ยกเลิกการทำธุรกรรมหากเกิดข้อผิดพลาด
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
    }

    header('location: ' . $base_url . '/management-website');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</title>
    <link href="<?php echo $base_url; ?>/assets/img/logo/sci_center.png" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/Manage_Website.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/footer.css">

    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .select-container {
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .button-update {
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .select-option img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }
    </style>
    <style>
        .select-container {
            margin-bottom: 20px;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: white;
        }

        .button-update {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 4px;
            text-transform: uppercase;
        }
    </style>

</head>

<body>
    <header>
        <?php include('assets/includes/navigator.php'); ?>
    </header>

    <main>
        <div class="managewebsite">
            <!-- ข้อความแสดงความสำเร็จหรือข้อผิดพลาด -->
            <?php if (isset($_SESSION['Uploadsuccess'])) : ?>
                <div class="toast">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-check check"></i>
                            <div class="toast_content_message">
                                <span class="text text_2"><?php echo $_SESSION['Uploadsuccess']; ?></span>
                            </div>
                            <i class="fa-solid fa-xmark close"></i>
                            <div class="progress"></div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['Uploadsuccess']); ?>
            <?php endif ?>
            <?php if (isset($_SESSION['errorUpload'])) : ?>
                <div class="toast error">
                    <div class="toast_section">
                        <div class="toast_content">
                            <i class="fas fa-solid fa-xmark check error"></i>
                            <div class="toast_content_message">
                                <span class="text text_2"><?php echo $_SESSION['errorUpload']; ?></span>
                            </div>
                            <i class="fa-solid fa-xmark close"></i>
                            <div class="progress error"></div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['errorUpload']); ?>
            <?php endif ?>

            <div class="managewebsite_header">
                <a class="historyBACK" href="javascript:history.back()">
                    <i class="fa-solid fa-arrow-left-long"></i>
                </a>
                <div class="breadcrumb">
                    <a href="/">หน้าหลัก</a>
                    <span>&gt;</span>
                    <?php
                    if ($request_uri == '/management-website') {
                        echo '<a href="/management-website">จัดการเว็บไซต์</a>';
                    }
                    ?>
                </div>
            </div>
            <a href="/management-website/add">เพิ่มข้อมูล</a>
            <!-- ตารางแสดงข้อมูล -->
            <?php if ($request_uri == '/management-website') : ?>
                <div class="container">
                    <form method="POST" action="<?php echo $base_url; ?>/models/ManageWebsite.php">
                        <div class="select-container">
                            <select name="id" id="website-select" class="mdl-select">
                                <?php foreach ($data as $row) : ?>
                                    <option value="<?= htmlspecialchars($row['ID']); ?>">
                                        <?php if ($row['status'] == 1) :?>
                                        <?= htmlspecialchars($row['type']); ?> เลือกอยู่
                                        <?php else :?>
                                             <?= htmlspecialchars($row['type']); ?> ยังไม่เลือก
                                        <?php endif;?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="select" class="button button-update mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored">Update Website</button>
                    </form>
                    <?php foreach ($data as $row) : ?>
                        <div class="row">
                            <div class="cell"><?= htmlspecialchars($row['type']); ?></div>
                            <div class="cell"><?= htmlspecialchars($row['name01']); ?></div>
                            <div class="cell"><?= htmlspecialchars($row['name02']); ?></div>
                            <div class="cell">
                                <img src="../assets/img/logo/<?= htmlspecialchars($row['logo']); ?>" width="50" alt="Logo">
                            </div>
                            <div class="cell">
                                <img src="../assets/img/qr_code_user/<?= htmlspecialchars($row['qrUser']); ?>" width="50" alt="User Image">
                            </div>
                            <div class="cell">
                                <img src="../assets/img/qr_code_staff/<?= htmlspecialchars($row['qrStaff']); ?>" width="50" alt="Staff Image">
                            </div>
                            <div class="cell">
                                <form method="POST" action="<?php echo $base_url; ?>/models/ManageWebsite.php">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['ID']); ?>">
                                    <button type="submit" name="delete" class="button button-delete">ลบ</button>
                                </form>
                                <a href="/management-website/edit?id=<?= htmlspecialchars($row['ID']); ?>" class="button button-edit">แก้ไข</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- ฟอร์มเพิ่มข้อมูล -->
            <?php if ($request_uri == '/management-website/add') : ?>
                <form method="POST" action="<?php echo $base_url ?>/models/ManageWebsite.php" enctype="multipart/form-data">
                    <div class="managewebsite_form">
                        <!-- ข้อมูลเว็บไซต์ -->
                        <div class="inputForm">
                            <label for="type">ตั้งชื่อรูปแบบ</label>
                            <input type="text" name="type" required>
                        </div>
                        <div class="inputForm">
                            <label for="name01">ชื่อหลัก</label>
                            <input type="text" name="name01" required>
                        </div>
                        <div class="inputForm">
                            <label for="name02">ชื่อรอง</label>
                            <input type="text" name="name02" required>
                        </div>
                        <!-- การอัปโหลดรูปภาพ -->
                        <div class="inputImg">
                            <div class="inputForm img-upload">
                                <label for="logo" class="upload-label">อัปโหลดโลโก้:</label>
                                <input type="file" class="input-img" id="logo" name="logo" accept="image/jpeg, image/png" required>
                                <label for="logo" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewLogo" alt="">
                            </div>
                            <div class="inputForm img-upload">
                                <label for="qrUser" class="upload-label">อัปโหลดรูปภาพผู้ใช้:</label>
                                <input type="file" class="input-img" id="qrUser" name="qrUser" accept="image/jpeg, image/png" required>
                                <label for="qrUser" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewQrUser" alt="">
                            </div>
                            <div class="inputForm img-upload">
                                <label for="qrStaff" class="upload-label">อัปโหลดรูปภาพพนักงาน:</label>
                                <input type="file" class="input-img" id="qrStaff" name="qrStaff" accept="image/jpeg, image/png" required>
                                <label for="qrStaff" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewQrStaff" alt="">
                            </div>
                        </div>
                        <!-- ปุ่มส่งข้อมูล -->
                        <div class="form-footer">
                            <button type="submit" name="Add_Website">เพิ่มข้อมูล</button>
                            <a href="javascript:history.back();" class="btn-cancel">ยกเลิก</a>
                        </div>
                    </div>
                </form>
            <?php endif ?>
            <?php if ($request_uri == '/management-website/edit') : ?>
                <form method="POST" action="<?php echo $base_url ?>/models/ManageWebsite.php" enctype="multipart/form-data">
                    <div class="managewebsite_form">
                        <!-- ข้อมูลเว็บไซต์ -->
                        <div class="inputForm">
                            <label for="name01">ชื่อหลัก</label>
                            <input type="text" name="name01" value="<?php echo htmlspecialchars($data['name01']); ?>" required>
                        </div>
                        <div class="inputForm">
                            <label for="name02">ชื่อรอง</label>
                            <input type="text" name="name02" value="<?php echo htmlspecialchars($data['name02']); ?>" required>
                        </div>
                        <!-- การอัปโหลดรูปภาพ -->
                        <div class="inputImg">
                            <div class="inputForm img-upload">
                                <label for="logo" class="upload-label">อัปโหลดโลโก้:</label>
                                <input type="file" class="input-img" id="logo" name="logo" accept="image/jpeg, image/png">
                                <label for="logo" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewLogo" src="../assets/img/logo/<?php echo htmlspecialchars($data['logo']); ?>" alt="">
                            </div>
                            <div class="inputForm img-upload">
                                <label for="qrUser" class="upload-label">อัปโหลดรูปภาพผู้ใช้:</label>
                                <input type="file" class="input-img" id="qrUser" name="qrUser" accept="image/jpeg, image/png">
                                <label for="qrUser" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewQrUser" src="../assets/img/qr_code_user/<?php echo htmlspecialchars($data['qrUser']); ?>" alt="">
                            </div>
                            <div class="inputForm img-upload">
                                <label for="qrStaff" class="upload-label">อัปโหลดรูปภาพพนักงาน:</label>
                                <input type="file" class="input-img" id="qrStaff" name="qrStaff" accept="image/jpeg, image/png">
                                <label for="qrStaff" class="file-chosen">เลือกรูปภาพที่จะอัปโหลด</label>
                                <img loading="lazy" class="preview-img" id="previewQrStaff" src="../assets/img/qr_code_staff/<?php echo htmlspecialchars($data['qrStaff']); ?>" alt="">
                            </div>
                        </div>
                        <!-- ปุ่มส่งข้อมูล -->
                        <div class="form-footer">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['ID']); ?>">
                            <button type="submit" name="Update_Website">บันทึกการแก้ไข</button>
                            <a href="javascript:history.back();" class="btn-cancel">ยกเลิก</a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <?php include('assets/includes/footer_2.php'); ?>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function handleFileSelect(event) {
                const input = event.target;
                const file = input.files[0];
                const previewImg = document.getElementById(`preview${input.id.charAt(0).toUpperCase() + input.id.slice(1)}`);

                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        input.nextElementSibling.textContent = file.name;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                    input.nextElementSibling.textContent = 'ยังไม่ได้เลือกไฟล์';
                }
            }

            const fileInputs = document.querySelectorAll('.input-img');
            fileInputs.forEach(input => {
                input.addEventListener('change', handleFileSelect);
            });
        });
    </script>
</body>

</html>