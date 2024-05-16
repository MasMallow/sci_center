<?php
include_once '../assets/database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['sci_name'], $_POST['quantity'], $_POST['product_type'])) {
        $id = $_POST['id'];
        $sci_name = $_POST['sci_name'];
        $quantity = $_POST['quantity'];
        $productType = $_POST['product_type'];

        // ทำการกรองข้อมูลที่รับเข้ามา
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $sci_name = filter_var($sci_name, FILTER_SANITIZE_STRING);
        $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
        $productType = filter_var($productType, FILTER_SANITIZE_STRING);

        try {
            // ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                $targetDir = "../uploads/";
                $fileName = basename($_FILES["file"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
                $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');

                if (in_array($fileType, $allowTypes)) {
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                        // อัปเดตข้อมูลสินค้าในฐานข้อมูลพร้อมกับรูปภาพใหม่
                        $stmt = $conn->prepare("UPDATE crud SET sci_name = ?, amount = ?, Type = ?, img = ? WHERE user_id = ?");
                        $stmt->execute([$sci_name, $quantity, $productType, $fileName, $id]);

                        header("Location: add-remove-update.php");
                        exit();
                    } else {
                        echo "ไม่สามารถย้ายไฟล์ที่อัปโหลดได้";
                    }
                } else {
                    echo "ประเภทไฟล์ไม่ถูกต้อง กรุณาอัปโหลดไฟล์ JPG, JPEG, PNG, GIF หรือ PDF.";
                }
            } else {
                // ไม่มีการอัปโหลดไฟล์ใหม่ อัปเดตข้อมูลสินค้าโดยไม่เปลี่ยนรูปภาพ
                $stmt = $conn->prepare("UPDATE crud SET sci_name = ?, amount = ?, Type = ? WHERE user_id = ?");
                $stmt->execute([$sci_name, $quantity, $productType, $id]);

                header("Location: add-remove-update.php");
                exit();
            }
        } catch (PDOException $e) {
            echo "ข้อผิดพลาดฐานข้อมูล: " . $e->getMessage();
        }
    } else {
        echo "ข้อมูลไม่ถูกต้อง";
    }
} else {
    echo "วิธีการร้องขอไม่ถูกต้อง";
}