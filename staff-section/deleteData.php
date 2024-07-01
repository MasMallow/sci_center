<?php
session_start();
require_once '../assets/database/config.php';
// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if product ID is provided
if (!empty($_POST['ID_deleteData'])) {
    $id = $_POST['ID_deleteData'];

    $folder = '../assets/uploads/';
    $log_Status = 'Edit';
    $log_Name = $userData['pre'] . $userData['firstname'] . ' ' . $userData['lastname'];
    $log_Role = $userData['role'];


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

        $_SESSION['delete_success'] = 'ลบข้อมูลสำเร็จ';
        header("Location: /management");
        exit();
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการลบสินค้า: " . $e->getMessage();
    }
} else {
    echo "ไม่ได้รับ ID ของสินค้าที่ต้องการลบ";
}
