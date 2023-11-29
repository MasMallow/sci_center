<?php
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productType']) && isset($_POST['quantity']) && isset($_POST['product_name'])) {
    $productType = $_POST['productType'];
    $quantity = $_POST['quantity'];
    $productName = $_POST['product_name'];

    $targetDir = "test/"; // เปลี่ยนเป็นชื่อโฟลเดอร์ที่ต้องการ

    $fileName = basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');

    // ตรวจสอบว่าไฟล์ที่อัปโหลดเป็นชนิดที่อนุญาตหรือไม่
    if (!in_array($fileType, $allowTypes)) {
        echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
        exit();
    }

    // ทำการอัปโหลดไฟล์
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        // เพิ่มข้อมูลลงในฐานข้อมูล
        $insert = $db->query("INSERT INTO crud (product_name, file_name, uploaded_on, status, amount, Type) VALUES ('$productName', '$fileName', NOW(), 1, '$quantity', '$productType')");

        if ($insert) {
            header("Location: add-remove-update.php");
            exit();
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "Please select a file to upload, provide the quantity, and enter the product name.";
}
?>
