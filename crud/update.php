<?php
include_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['product_name'], $_POST['quantity'], $_POST['product_type'])) {
        $id = $_POST['id'];
        $product_name = $_POST['product_name'];
        $quantity = $_POST['quantity'];
        $productType = $_POST['product_type']; // Ensure this matches the form input name
        $fileName = basename($_FILES["file"]["name"]);

        // Check if a new image is uploaded
        if (isset($_FILES['file']["name"])) {
            $targetDir = "../uploads/"; // เปลี่ยนเป็นชื่อโฟลเดอร์ที่ต้องการ
            $fileName = basename($_FILES["file"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
            if (!in_array($fileType, $allowTypes)) {
                echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
                exit();
            }
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                    // Update product information in the database with the new image
                    $stmt = $db->prepare("UPDATE crud SET product_name = ?, amount = ?, Type = ?, file_name = ? WHERE id = ?");
                    $stmt->bind_param('sissi', $product_name, $quantity, $productType, $fileName, $id);
                    $stmt->execute();

                    header("Location: add-remove-update.php");
                    exit();
                } else {
                    echo "Failed to move the uploaded file.";
                }
            } else {
                echo "Invalid file type. Please upload an image.";
            }
        } else {
            // No new image uploaded; update product information without changing the image
            $stmt = $db->prepare("UPDATE crud SET product_name = ?, amount = ?, Type = ? WHERE id = ?");
            $stmt->bind_param('sisi', $product_name, $quantity, $productType, $id);
            $stmt->execute();

            header("Location: add-remove-update.php");
            exit();
        }
    } else {
        echo "Invalid data received.";
    }
} else {
    echo "Invalid request method.";
}
