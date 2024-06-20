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
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>แก้ไขรายชื่อศูนย์วิทยาศาสตร์</title>
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
                <a href="<?php echo $base_url; ?>/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <label id="B">แก้ไขรายชื่อศูนย์วิทยาศาสตร์</label>
            </div>
            <form action="<?php echo $base_url; ?>/Staff/updateData.php" method="POST" enctype="multipart/form-data">
                <div class="add_MET_section_form">
                    <div class="input">
                        <div class="img">
                            <div class="imgInput">
                                <img src="../assets/uploads/<?php echo $editData['img_name']; ?>" class="previewImg" id="previewImg">
                            </div>
                        </div>
                        <label for="imgInput" class="label-for-file">เลือกไฟล์</label>
                        <input type="file" id="imgInput" name="img" accept="image/jpeg, image/png">
                    </div>
                    <div class="col">
                        <div class="input">
                            <input type="text" hidden value="<?php echo $editData['ID']; ?>" required name="id">
                            <label for="sci_name">รูปภาพ</label>
                            <input type="text" id="imgNameInput" value="<?php echo $editData['img_name']; ?>" required name="sci_name">
                            <input type="hidden" value="<?php echo $editData['img_name']; ?>" required name="img2">
                        </div>
                    </div>
                    <script>
                        document.getElementById('imgInput').addEventListener('change', function(event) {
                            const [file] = event.target.files;
                            if (file) {
                                document.getElementById('previewImg').src = URL.createObjectURL(file);
                                document.getElementById('imgNameInput').value = file.name;
                            }
                        });
                    </script>
                    <style>
                        .input {
                            display: flex;
                            flex-direction: column;
                            margin-bottom: 15px;
                        }

                        .img {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            margin-bottom: 10px;
                        }

                        .imgInput {
                            position: relative;
                            width: 150px;
                            /* สามารถปรับตามต้องการ */
                            height: 150px;
                            /* สามารถปรับตามต้องการ */
                            border: 1px solid #ccc;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            overflow: hidden;
                        }

                        .imgInput img {
                            max-width: 100%;
                            max-height: 100%;
                        }

                        #imgInput {
                            display: none;
                        }

                        .label-for-file {
                            padding: 10px 20px;
                            background-color: #007bff;
                            color: white;
                            cursor: pointer;
                            text-align: center;
                            display: inline-block;
                            border-radius: 5px;
                        }
                    </style>
                    <div class="input_Data">
                        <label for="sci_name">ชื่อ</label>
                        <input type="text" name="sci_name" required value="<?php echo $editData['sci_name'] ?>">
                    </div>
                    <div class="input_Data">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" name="serial_number" required value="<?php echo $editData['serial_number'] ?>">
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="categories">ประเภท</label>
                            <select name="categories" required>
                                <option value="Material" <?php if ($editData['categories'] === 'วัสดุ') echo 'selected'; ?>>วัสดุ</option>
                                <option value="Equipment" <?php if ($editData['categories'] === 'อุปกรณ์') echo 'selected'; ?>>อุปกรณ์</option>
                                <option value="Tool" <?php if ($editData['categories'] === 'เครื่องมือ') echo 'selected'; ?>>เครื่องมือ</option>
                            </select>
                        </div>
                        <div class="input_Data">
                            <label for="amount">จำนวน</label>
                            <input type="number" name="amount" min="1" required value="<?php echo $editData['amount'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="installation_date">วันที่ติดตั้ง</label>
                            <input type="datetime-local" name="installation_date" value="<?php echo $editData['installation_date'] ?>">
                        </div>
                        <div class="input_Data">
                            <label for="company">บริษัท</label>
                            <input type="text" name="company" value="<?php echo $editData['company'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="contact_number">เบอร์โทรศัพท์บริษัท</label>
                            <input type="text" name="contact_number" value="<?php echo $editData['contact_number'] ?>">
                        </div>
                        <div class="input_Data">
                            <label for="contact">คนติดต่อ</label>
                            <input type="text" name="contact" value="<?php echo $editData['contact'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="brand">ยี่ห้อ</label>
                            <input type="text" name="brand" value="<?php echo $editData['brand'] ?>">
                        </div>
                        <div class="input_Data">
                            <label for="model">รุ่น</label>
                            <input type="text" name="model" value="<?php echo $editData['model'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="details">Details</label>
                            <textarea id="details" name="details"><?php echo $editData['details'] ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="btn_footer">
                    <input type="hidden" name="id" value="<?php echo $editData['ID']; ?>">
                    <button type="submit" name="update" class="submitADD">ยืนยัน</button>
                    <a href="<?php echo $base_url; ?>/management" class="resetADD">ยกเลิก</a>
                </div>
            </form>
        </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>