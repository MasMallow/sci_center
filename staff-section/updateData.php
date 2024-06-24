<?php
session_start();
require_once('../assets/database/dbConfig.php');

if (isset($_POST['update'])) {
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

    $fileNew = $img2; // Default to previous image

    if ($upload != '') {
        $allow = array('jpg', 'jpeg', 'png');
        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
        $fileActExt = strtolower($extension);
        $fileNew = rand() . "." . $fileActExt;
        $folder = '../assets/uploads/';
        $filePath = $folder . $fileNew;

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

    // Fetch existing data
    $sql = $conn->prepare("SELECT * FROM crud WHERE ID = :ID");
    $sql->bindParam(":ID", $id);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if ($upload != '') {
        @unlink($folder . $result['img_name']);
    } else {
        $fileNew = $result['img_name'];
    }

    // Update crud table
    $update_sql = $conn->prepare("UPDATE crud SET sci_name = :sci_name, amount = :amount, categories = :categories, img_name = :img_name WHERE ID = :ID");
    $update_sql->bindParam(":ID", $id);
    $update_sql->bindParam(":sci_name", $sci_name);
    $update_sql->bindParam(":amount", $amount);
    $update_sql->bindParam(":categories", $categories);
    $update_sql->bindParam(":img_name", $fileNew);

    // Update info_sciname table
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

    // Execute both updates and check results
    if ($update_sql->execute() && $info_update_sql->execute()) {
        $_SESSION['updateData_success'] = "อัปเดทข้อมูลสำเร็จ";
    } else {
        $_SESSION['updateData_error'] = "อัปเดทข้อมูลไม่สำเร็จ";
    }

    header("Location: /management");
    exit;
}
?>