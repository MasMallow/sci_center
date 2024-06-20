<?php
session_start();
require_once '../assets/database/dbConfig.php';

// Check if product ID is provided
if (!empty($_POST['ID_deleteData'])) {
    $id = $_POST['ID_deleteData'];

    $folder = 'assets/uploads/';
    
    try {
        // Fetch image name and delete image
        $stmt = $conn->prepare("SELECT * FROM crud WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            @unlink($folder . $result['img_name']);
        }

        // Delete from crud table
        $stmt = $conn->prepare("DELETE FROM crud WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        // Delete from info_sciname table
        $stmt = $conn->prepare("DELETE FROM info_sciname WHERE ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        echo "ลบสินค้าเรียบร้อยแล้ว";
        header("Location: /management");
        exit();
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการลบสินค้า: " . $e->getMessage();
    }
} else {
    echo "ไม่ได้รับ ID ของสินค้าที่ต้องการลบ";
}
?>
