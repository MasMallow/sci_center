<?php
session_start();
require_once '../assets/database/dbConfig.php';
date_default_timezone_set('Asia/Bangkok'); // ตั้งค่าโซนเวลาเป็น Asia/Bangkok

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

    // Upload Thumbnail
    $img = $_FILES['img'];
    $thumbnail_extension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $folder = '../assets/uploads/';
    $thumbnail_path = $folder . uniqid() . '.' . $thumbnail_extension;

    // ตรวจสอบประเภทของไฟล์ภาพ
    $allow = array('jpg', 'jpeg', 'png');
    if (in_array($thumbnail_extension, $allow)) {
        if ($img['size'] > 0 && $img['error'] == 0) {

            // ตรวจสอบว่ามีชื่อไฟล์ภาพอยู่ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM crud WHERE img_name = :img_name");
            $stmt->bindParam(":img_name", $img['name']);
            $stmt->execute();
            $Insert_curd = $stmt->fetch(PDO::FETCH_ASSOC);

            // ตรวจสอบว่ามีชื่อวิทยาศาสตร์อยู่ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM info_sciname WHERE sci_name = :sci_name");
            $stmt->bindParam(":sci_name", $sci_name);
            $stmt->execute();
            $Insert_info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($Insert_curd || $Insert_info) {
                $_SESSION['errorUpload'] = "ชื่อไฟล์ภาพหรือชื่อวิทยาศาสตร์นี้ถูกใช้ไปแล้ว";
                header('location: ' . $base_url . '/management/addData');
                exit();
            } else {
                // อัปโหลดไฟล์ภาพ
                if (move_uploaded_file($img['tmp_name'], $thumbnail_path)) {
                    $conn->beginTransaction(); // เริ่มการทำธุรกรรม

                    try {
                        // เพิ่มข้อมูลลงในฐานข้อมูล crud
                        $sql = $conn->prepare("INSERT INTO crud (img_name, sci_name, serial_number, amount, categories, uploaded_on) 
                        VALUES(:img_name, :sci_name, :serial_number, :amount, :categories, :uploaded)");
                        $thumbnail_new_name = basename($thumbnail_path);
                        $uploaded = date("Y-m-d H:i:s"); // ใส่วันที่และเวลาปัจจุบัน
                        $sql->bindParam(":img_name", $thumbnail_new_name);
                        $sql->bindParam(":sci_name", $sci_name);
                        $sql->bindParam(":serial_number", $serial_number);
                        $sql->bindParam(":amount", $amount);
                        $sql->bindParam(":categories", $categories);
                        $sql->bindParam(":uploaded", $uploaded);
                        $sql->execute();

                        // เพิ่มข้อมูลลงในฐานข้อมูล info_sciname
                        $sql = $conn->prepare("INSERT INTO info_sciname (sci_name, serial_number, installation_date, details, brand, model, company, contact_number, contact) 
                        VALUES(:sci_name, :serial_number, :installation_date, :details, :brand, :model, :company, :contact_number, :contact)");
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

                        // ยืนยันการทำธุรกรรม
                        $conn->commit();

                        $_SESSION['success'] = "เพิ่มข้อมูลสำเร็จ <a href='dashboard.php'><span id='B'>กลับหน้า Dashboard</span></a>";
                        header('location: ' . $base_url . '/management/addData');
                        exit();
                    } catch (Exception $e) {
                        // ยกเลิกการทำธุรกรรม
                        $conn->rollBack();
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                        header('location: ' . $base_url . '/management/addData');
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ภาพ";
                    header('location: ' . $base_url . '/management/addData');
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = "ขนาดของไฟล์ภาพหรือข้อผิดพลาดในการอัปโหลด";
            header('location: ' . $base_url . '/management/addData');
            exit();
        }
    } else {
        $_SESSION['error'] = "ประเภทของไฟล์ภาพไม่ถูกต้อง (รูปภาพ: jpg, jpeg, png)";
        header('location: ' . $base_url . '/management/addData');
        exit();
    }
} else {
    $_SESSION['error'] = "คุณไม่ได้ส่งคำขอเพิ่มข้อมูล";
    header('location: ' . $base_url . '/management/addData');
    exit();
}
?>
