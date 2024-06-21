<?php
session_start();
require_once '../assets/database/dbConfig.php';
date_default_timezone_set('Asia/Bangkok'); // Set timezone to Asia/Bangkok

// Check if the user is logged in
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Process form submission
if (isset($_POST['submit'])) {
    // Get form data
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

    // Check if $sci_name contains [] or ()
    if (strpos($sci_name, '[') !== false || strpos($sci_name, ']') !== false || strpos($sci_name, '(') !== false || strpos($sci_name, ')') !== false) {
        $_SESSION['error'] = "ห้ามใส่ [] หรือ () ในชื่อวิทยาศาสตร์";
        header('location: ' . $base_url . '/management/addData');
        exit();
    }

    // Handle thumbnail upload
    $img = $_FILES['img'];
    $thumbnail_extension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $folder = '../assets/uploads/';
    $thumbnail_path = $folder . uniqid() . '.' . $thumbnail_extension;

    // Allowed image types
    $allowed = array('jpg', 'jpeg', 'png');
    if (in_array($thumbnail_extension, $allowed)) {
        if ($img['size'] > 0 && $img['error'] == 0) {
            // Check if image name already exists in database
            $stmt = $conn->prepare("SELECT * FROM crud WHERE img_name = :img_name");
            $stmt->bindParam(":img_name", $img['name']);
            $stmt->execute();
            $insert_curd = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if scientific name already exists in database
            $stmt = $conn->prepare("SELECT * FROM info_sciname WHERE sci_name = :sci_name");
            $stmt->bindParam(":sci_name", $sci_name);
            $stmt->execute();
            $insert_info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($insert_curd || $insert_info) {
                $_SESSION['errorUpload'] = "ชื่อไฟล์ภาพหรือชื่อวิทยาศาสตร์นี้ถูกใช้ไปแล้ว";
                header('location: ' . $base_url . '/management/addData');
                exit();
            } else {
                // Upload the image
                if (move_uploaded_file($img['tmp_name'], $thumbnail_path)) {
                    $conn->beginTransaction(); // Start transaction

                    try {
                        // Insert data into crud table
                        $sql = $conn->prepare("INSERT INTO crud (img_name, sci_name, serial_number, amount, categories, uploaded_on) VALUES (:img_name, :sci_name, :serial_number, :amount, :categories, :uploaded)");
                        $thumbnail_new_name = basename($thumbnail_path);
                        $uploaded = date("Y-m-d H:i:s"); // Current date and time
                        $sql->bindParam(":img_name", $thumbnail_new_name);
                        $sql->bindParam(":sci_name", $sci_name);
                        $sql->bindParam(":serial_number", $serial_number);
                        $sql->bindParam(":amount", $amount);
                        $sql->bindParam(":categories", $categories);
                        $sql->bindParam(":uploaded", $uploaded);
                        $sql->execute();

                        // Insert data into info_sciname table
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

                        // Commit transaction
                        $conn->commit();

                        $_SESSION['success'] = "เพิ่มข้อมูลสำเร็จ <a href='dashboard.php'><span id='B'>กลับหน้า Dashboard</span></a>";
                        header('location: ' . $base_url . '/management/addData');
                        exit();
                    } catch (Exception $e) {
                        // Rollback transaction
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
