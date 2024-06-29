<?php
session_start();
require_once('../assets/database/dbConfig.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ตรวจสอบว่ามีการส่งข้อมูลการอัปเดทเข้ามาหรือไม่
if (isset($_POST['update'])) {
    // รับข้อมูลจากฟอร์มและตัดช่องว่างหน้า-หลังออก
    $id = $_POST['id'];
    $sci_name = trim($_POST['sci_name']);
    $serial_number = trim($_POST['serial_number']);
    $amount = trim($_POST['amount']);
    $categories = trim($_POST['categories']);
    $details = trim($_POST['details']);
    $installation_date = trim($_POST['installation_date']);
    $company = trim($_POST['company']);
    $contact_number = trim($_POST['contact_number']);
    $contact = trim($_POST['contact']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $img = $_FILES['img'];
    $img2 = $_POST['img2'];
    $upload = $_FILES['img']['name'];

    // ตั้งค่าค่าเริ่มต้นสำหรับ log
    $log_Status = 'Edit';
    $log_Name = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
    $log_Role = $userData['role'];
    $fileNew = $img2; // ใช้รูปภาพเดิมเป็นค่าเริ่มต้น

    // ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
    if ($upload != '') {
        $allow = array('jpg', 'jpeg', 'png');
        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
        $fileActExt = strtolower($extension);
        $fileNew = rand() . "." . $fileActExt;
        $folder = '../assets/uploads/';
        $filePath = $folder . $fileNew;

        // ตรวจสอบชนิดไฟล์และขนาดไฟล์
        if (in_array($fileActExt, $allow) && $img['size'] > 0 && $img['error'] == 0) {
            if (!move_uploaded_file($img['tmp_name'], $filePath)) {
                $_SESSION['error'] = "Failed to upload the file";
                header("Location: /management");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size";
            header("Location: /management");
            exit;
        }
    }

    // ดึงข้อมูลที่มีอยู่เพื่อตรวจสอบการอัปเดทรูปภาพ
    $sql = $conn->prepare("SELECT * FROM crud WHERE ID = :ID");
    $sql->bindParam(":ID", $id);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    // ลบรูปภาพเดิมหากมีการอัปโหลดรูปภาพใหม่
    if ($upload != '') {
        @unlink($folder . $result['img_name']);
    } else {
        $fileNew = $result['img_name'];
    }

    // อัปเดตตาราง crud
    $update_sql = $conn->prepare("UPDATE crud SET sci_name = :sci_name, amount = :amount, categories = :categories, img_name = :img_name WHERE ID = :ID");
    $update_sql->bindParam(":ID", $id);
    $update_sql->bindParam(":sci_name", $sci_name);
    $update_sql->bindParam(":amount", $amount);
    $update_sql->bindParam(":categories", $categories);
    $update_sql->bindParam(":img_name", $fileNew);

    // อัปเดตตาราง info_sciname
    $info_update_sql = $conn->prepare("UPDATE info_sciname SET 
        sci_name = :sci_name, 
        serial_number = :serial_number, 
        installation_date = :installation_date, 
        details = :details, 
        brand = :brand, 
        model = :model, 
        company = :company, 
        contact_number = :contact_number, 
        contact = :contact 
        WHERE ID = :ID");
    $info_update_sql->bindParam(":ID", $id);
    $info_update_sql->bindParam(":sci_name", $sci_name);
    $info_update_sql->bindParam(":serial_number", $serial_number);
    $info_update_sql->bindParam(":installation_date", $installation_date);
    $info_update_sql->bindParam(":details", $details);
    $info_update_sql->bindParam(":brand", $brand);
    $info_update_sql->bindParam(":model", $model);
    $info_update_sql->bindParam(":company", $company);
    $info_update_sql->bindParam(":contact_number", $contact_number);
    $info_update_sql->bindParam(":contact", $contact);

    // ใส่ข้อมูลลงในตาราง logs_management
    $log_Content = json_encode([
        'sci_name' => $sci_name,
        'serial_number' => $serial_number,
    ], JSON_UNESCAPED_UNICODE);
    $log_sql = $conn->prepare("INSERT INTO logs_management (log_Name, log_Role, log_Status, log_Content) 
                            VALUES (:log_Name, :log_Role, :log_Status, :log_Content)");
    $log_sql->bindParam(":log_Name", $log_Name);
    $log_sql->bindParam(":log_Role", $log_Role);
    $log_sql->bindParam(":log_Status", $log_Status);
    $log_sql->bindParam(":log_Content", $log_Content);

    // Execute all queries and check results
    $conn->beginTransaction(); // Begin transaction
    try {
        $update_sql->execute();
        $info_update_sql->execute();
        $log_sql->execute();
        $conn->commit(); // Commit transaction
        $_SESSION['updateData_success'] = "อัปเดทข้อมูลสำเร็จ";
    } catch (Exception $e) {
        $conn->rollBack(); // Rollback transaction if any query fails
        $_SESSION['updateData_error'] = "อัปเดทข้อมูลไม่สำเร็จ: " . $e->getMessage();
    }

    header("Location: /management");
    exit;
}
?>
