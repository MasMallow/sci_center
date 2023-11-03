<?php
include_once 'db.php';

$targetDir = "test/"; // เปลี่ยนเป็นชื่อโฟลเดอร์ที่ต้องการ

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES["file"]["name"]) && isset($_POST['quantity']) && isset($_POST['product_name'])) {
        $fileName = basename($_FILES["file"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');

        // Check if the uploaded file is an allowed type
        if (in_array($fileType, $allowTypes)) {
            // Retrieve the quantity and product name from the form
            $quantity = $_POST['quantity'];
            $product_name = $_POST['product_name'];

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                $insert = $db->query("INSERT INTO image (product_name, file_name, uploaded_on, status, amount) VALUES ('$product_name', '$fileName', NOW(), 1, '$quantity')");

                if ($insert) {
                    $statusMsg = "The file <b>" . $fileName . "</b> has been uploaded successfully.";
                    header("Location: image.php");
                    exit();
                } else {
                    $statusMsg = "File upload failed. Please try again.";
                }
            } else {
                $statusMsg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $statusMsg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed to be uploaded.";
        }
    } else {
        $statusMsg = "Please select a file to upload, provide the quantity, and enter the product name.";
    }
}
?>