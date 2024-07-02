<?php
session_start();
require_once '../assets/database/config.php';
date_default_timezone_set('Asia/Bangkok'); // ตั้งค่าโซนเวลาเป็น Asia/Bangkok

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ประมวลผลเมื่อฟอร์มถูกส่ง
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
    if (strpos($sci_name, '[') !== false || strpos($sci_name, ']') !== false || strpos($sci_name, '(') !== false || strpos($sci_name, ')') !== false) {
        $_SESSION['errorUpload'] = "ห้ามใส่ [] หรือ () ในชื่อวิทยาศาสตร์";
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
                        $sql = $conn->prepare("INSERT INTO logs_management (log_Name, log_Role, log_Status, log_Content) 
                                                VALUES (:log_Name, :log_Role, :log_Status, :log_Content)");
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
} else {
    $_SESSION['errorUpload'] = "คุณไม่ได้ส่งคำขอเพิ่มข้อมูล";
    header('location: ' . $base_url . '/management/addData');
    exit();
}
