<?php
session_start();
require_once '../assets/config/config.php';
require_once '../assets/config/Database.php';
date_default_timezone_set('Asia/Bangkok'); // ตั้งค่าโซนเวลาเป็น Asia/Bangkok

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// <-------------- ADD ---------------->
if (isset($_POST['submit'])) {
    // รับข้อมูลจากฟอร์ม
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

    $log_Status = 'Add';

    // ตรวจสอบว่า $sci_name ไม่มี [] หรือ ()
    if (
        strpos($sci_name, '[') !== false || strpos($sci_name, ']') !== false || strpos($sci_name, '(') !== false || strpos($sci_name, ')') !== false
        || strpos($sci_name, ',') !== false
    ) {
        $_SESSION['errorUpload'] = "ห้ามใส่ [] () และ , ในชื่อวิทยาศาสตร์";
        header('location: ' . $base_url . '/management/addData');
        exit();
    }

    // จัดการการอัปโหลดรูปภาพ
    $img = $_FILES['img'];
    $thumbnail_extension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $folder = '../assets/uploads/';
    $thumbnail_path = $folder . uniqid() . '.' . $thumbnail_extension;

    // ประเภทของรูปภาพที่อนุญาต
    $allowed = array('jpg', 'jpeg', 'png');
    if (in_array($thumbnail_extension, $allowed)) {
        if ($img['size'] > 0 && $img['error'] == 0) {
            // ตรวจสอบขนาดของไฟล์ภาพ (ไม่เกิน 3MB)
            if ($img['size'] > 3 * 1024 * 1024) {
                $_SESSION['errorUpload'] = "ขนาดของไฟล์ภาพเกิน 3MB";
                header('location: ' . $base_url . '/management/addData');
                exit();
            }

            // ตรวจสอบว่าชื่อรูปภาพมีอยู่ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM crud WHERE img_name = :img_name");
            $stmt->bindParam(":img_name", $img['name']);
            $stmt->execute();
            $insert_curd = $stmt->fetch(PDO::FETCH_ASSOC);

            $log_Name = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
            $log_Role = $userData['role'];

            if ($insert_curd) {
                $_SESSION['errorUpload'] = "ชื่อไฟล์ภาพนี้ถูกใช้ไปแล้ว";
                header('location: ' . $base_url . '/management/addData');
                exit();
            } else {
                // อัปโหลดรูปภาพ
                if (move_uploaded_file($img['tmp_name'], $thumbnail_path)) {
                    $conn->beginTransaction(); // เริ่มต้นการทำธุรกรรม

                    try {
                        // ใส่ข้อมูลลงในตาราง crud
                        $sql = $conn->prepare("INSERT INTO crud (img_name, sci_name, serial_number, amount, categories, uploaded_on) VALUES (:img_name, :sci_name, :serial_number, :amount, :categories, :uploaded)");
                        $thumbnail_new_name = basename($thumbnail_path);
                        $uploaded = date("Y-m-d H:i:s"); // วันที่และเวลาปัจจุบัน
                        $sql->bindParam(":img_name", $thumbnail_new_name);
                        $sql->bindParam(":sci_name", $sci_name);
                        $sql->bindParam(":serial_number", $serial_number);
                        $sql->bindParam(":amount", $amount);
                        $sql->bindParam(":categories", $categories);
                        $sql->bindParam(":uploaded", $uploaded);
                        $sql->execute();

                        // ใส่ข้อมูลลงในตาราง info_sciname
                        $sql = $conn->prepare("INSERT INTO info_sciname (sci_name, serial_number, installation_date, details, brand, model, company, contact_number, contact) VALUES (:sci_name, :serial_number, :installation_date, :details, :brand, :model, :company, :contact_number, :contact)");
                        $sql->bindParam(":sci_name", $sci_name);
                        $sql->bindParam(":serial_number", $serial_number);
                        $sql->bindParam(":installation_date", $installation_date);
                        $sql->bindParam(":details", $details);
                        $sql->bindParam(":brand", $brand);
                        $sql->bindParam(":model", $model);
                        $sql->bindParam(":company", $company);
                        $sql->bindParam(":contact_number", $contact_number);
                        $sql->bindParam(":contact", $contact);
                        $sql->execute();

                        // ใส่ข้อมูลลงในตาราง logs_management
                        $log_Content = json_encode([
                            'sci_name' => $sci_name,
                            'serial_number' => $serial_number,
                        ], JSON_UNESCAPED_UNICODE);
                        $sql = $conn->prepare("INSERT INTO logs_management (log_Name, log_Role, log_Status, log_Content) VALUES (:log_Name, :log_Role, :log_Status, :log_Content)");
                        $sql->bindParam(":log_Name", $log_Name);
                        $sql->bindParam(":log_Role", $log_Role);
                        $sql->bindParam(":log_Status", $log_Status);
                        $sql->bindParam(":log_Content", $log_Content);
                        $sql->execute();

                        // ยืนยันการทำธุรกรรม
                        $conn->commit();

                        $_SESSION['Uploadsuccess'] = "เพิ่มข้อมูลสำเร็จ";
                        header('location: ' . $base_url . '/management/addData');
                        exit();
                    } catch (Exception $e) {
                        // ยกเลิกการทำธุรกรรม
                        $conn->rollBack();
                        $_SESSION['errorUpload'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                        header('location: ' . $base_url . '/management/addData');
                        exit();
                    }
                } else {
                    $_SESSION['errorUpload'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ภาพ";
                    header('location: ' . $base_url . '/management/addData');
                    exit();
                }
            }
        } else {
            $_SESSION['errorUpload'] = "ขนาดของไฟล์ภาพหรือข้อผิดพลาดในการอัปโหลด";
            header('location: ' . $base_url . '/management/addData');
            exit();
        }
    } else {
        $_SESSION['errorUpload'] = "ประเภทของไฟล์ภาพไม่ถูกต้อง (รูปภาพ: jpg, jpeg, png)";
        header('location: ' . $base_url . '/management/addData');
        exit();
    }
}

// <-------------- EDIT ---------------->
elseif (isset($_POST['update'])) {
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
        $filePath = '../assets/uploads/' . $fileNew;

        // ตรวจสอบว่าประเภทไฟล์ถูกต้องและขนาดไม่เกิน 3MB
        if (in_array($fileActExt, $allow) && $img['size'] <= 3 * 1024 * 1024) {
            // ลบไฟล์เก่าถ้ามีการอัปโหลดไฟล์ใหม่
            if (file_exists('../assets/uploads/' . $img2)) {
                unlink('../assets/uploads/' . $img2);
            }
            move_uploaded_file($img['tmp_name'], $filePath);
        } else {
            $_SESSION['updateData_error'] = "ประเภทไฟล์หรือขนาดไฟล์ไม่ถูกต้อง";
            header("location: " . $base_url . "/management/editData?id=" . $id);
            exit();
        }
    }

    // อัปเดตข้อมูลในตาราง crud
    $sql = $conn->prepare("UPDATE crud SET sci_name = :sci_name, serial_number = :serial_number, amount = :amount, categories = :categories, img_name = :img_name WHERE id = :id");
    $sql->bindParam(":sci_name", $sci_name);
    $sql->bindParam(":serial_number", $serial_number);
    $sql->bindParam(":amount", $amount);
    $sql->bindParam(":categories", $categories);
    $sql->bindParam(":img_name", $fileNew);
    $sql->bindParam(":id", $id);
    $sql->execute();

    // อัปเดตข้อมูลในตาราง info_sciname
    $sql = $conn->prepare("UPDATE info_sciname SET sci_name = :sci_name, serial_number = :serial_number, installation_date = :installation_date, details = :details, brand = :brand, model = :model, company = :company, contact_number = :contact_number, contact = :contact WHERE id = :id");
    $sql->bindParam(":sci_name", $sci_name);
    $sql->bindParam(":serial_number", $serial_number);
    $sql->bindParam(":installation_date", $installation_date);
    $sql->bindParam(":details", $details);
    $sql->bindParam(":brand", $brand);
    $sql->bindParam(":model", $model);
    $sql->bindParam(":company", $company);
    $sql->bindParam(":contact_number", $contact_number);
    $sql->bindParam(":contact", $contact);
    $sql->bindParam(":id", $id);
    $sql->execute();

    // ใส่ข้อมูลลงในตาราง logs_management
    $log_Content = json_encode([
        'sci_name' => $sci_name,
        'serial_number' => $serial_number,
    ], JSON_UNESCAPED_UNICODE);
    $sql = $conn->prepare("INSERT INTO logs_management (log_Name, log_Role, log_Status, log_Content) VALUES (:log_Name, :log_Role, :log_Status, :log_Content)");
    $sql->bindParam(":log_Name", $log_Name);
    $sql->bindParam(":log_Role", $log_Role);
    $sql->bindParam(":log_Status", $log_Status);
    $sql->bindParam(":log_Content", $log_Content);
    $sql->execute();

    $_SESSION['updateData_success'] = "อัปเดตข้อมูลสำเร็จ";
    header("location: /management/edit?id=" . $id);
    exit();
}

// <-------------- DELETE ---------------->
elseif (isset($_POST['ID_deleteData'])) {
    // รับ ID ของรายการที่จะลบ
    $id = $_POST['ID_deleteData'];

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM crud WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['delete_error'] = "ไม่พบข้อมูลที่จะลบ";
        header("location: /management");
        exit();
    }

    $sci_name = $row['sci_name'];
    $serial_number = $row['serial_number'];

    // ลบไฟล์รูปภาพออกจากโฟลเดอร์
    $path = '../assets/uploads/' . $row['img_name'];
    if (file_exists($path)) {
        if (!unlink($path)) {
            $_SESSION['delete_error'] = "ไม่สามารถลบไฟล์รูปภาพได้";
            header("location: /management");
            exit();
        }
    }

    // ลบข้อมูลจากตาราง crud
    $stmt = $conn->prepare("DELETE FROM crud WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();

    // ลบข้อมูลจากตาราง info_sciname
    $stmt = $conn->prepare("DELETE FROM info_sciname WHERE sci_name = :sci_name AND serial_number = :serial_number");
    $stmt->bindParam(":sci_name", $sci_name);
    $stmt->bindParam(":serial_number", $serial_number);
    $stmt->execute();

    // ใส่ข้อมูลลงในตาราง logs_management
    $log_Name = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
    $log_Role = $userData['role'];
    $log_Status = 'Delete';
    $log_Content = json_encode([
        'sci_name' => $sci_name,
        'serial_number' => $serial_number,
    ], JSON_UNESCAPED_UNICODE);
    $stmt = $conn->prepare("INSERT INTO logs_management (log_Name, log_Role, log_Status, log_Content) VALUES (:log_Name, :log_Role, :log_Status, :log_Content)");
    $stmt->bindParam(":log_Name", $log_Name);
    $stmt->bindParam(":log_Role", $log_Role);
    $stmt->bindParam(":log_Status", $log_Status);
    $stmt->bindParam(":log_Content", $log_Content);
    $stmt->execute();

    $_SESSION['delete_success'] = "ลบข้อมูลสำเร็จ";
    header("location: /management");
    exit();
}
