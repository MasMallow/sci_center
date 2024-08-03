<?php
session_start();
require_once '../assets/config/config.php';
require_once '../assets/config/Database.php';

// <-------------- ADD ---------------->
if (isset($_POST['Add_Website'])) {
    // รับข้อมูลจากฟอร์ม
    $type = trim($_POST['type']);
    $name01 = trim($_POST['name01']);
    $name02 = trim($_POST['name02']);

    // จัดการการอัปโหลดรูปภาพ
    $logo = $_FILES['logo'];
    $qrUser = $_FILES['qrUser'];
    $qrStaff = $_FILES['qrStaff'];

    // ประเภทของรูปภาพที่อนุญาต
    $allowed = array('jpg', 'jpeg', 'png');

    // ตรวจสอบการอัปโหลดและประเภทไฟล์ของ logo
    $logo_extension = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
    if (in_array($logo_extension, $allowed) && $logo['size'] > 0 && $logo['error'] == 0) {
        // ตรวจสอบขนาดของไฟล์ภาพ (ไม่เกิน 3MB)
        if ($logo['size'] > 3 * 1024 * 1024) {
            $_SESSION['errorUpload'] = "ขนาดของไฟล์ภาพเกิน 3MB";
            header('location: ' . $base_url . '/management-website');
            exit();
        }

        // ตั้งค่า path และตรวจสอบว่าชื่อรูปภาพมีอยู่ในฐานข้อมูลหรือไม่
        $logo_path = '../assets/img/logo/' . uniqid() . '.' . $logo_extension;
        $stmt = $conn->prepare("SELECT * FROM assets WHERE logo = :logo");
        $stmt->bindParam(":logo", $logo_path);
        $stmt->execute();
        $insert_curd = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($insert_curd) {
            $_SESSION['errorUpload'] = "ชื่อไฟล์ภาพนี้ถูกใช้ไปแล้ว";
            header('location: ' . $base_url . '/management-website');
            exit();
        } else {
            // ตรวจสอบการอัปโหลดและประเภทไฟล์ของ qrUser และ qrStaff
            $qrUser_extension = strtolower(pathinfo($qrUser['name'], PATHINFO_EXTENSION));
            $qrStaff_extension = strtolower(pathinfo($qrStaff['name'], PATHINFO_EXTENSION));

            if (
                in_array($qrUser_extension, $allowed) && $qrUser['size'] > 0 && $qrUser['error'] == 0 &&
                in_array($qrStaff_extension, $allowed) && $qrStaff['size'] > 0 && $qrStaff['error'] == 0
            ) {

                // ตั้งค่า path สำหรับ qrUser และ qrStaff
                $qrUser_path = '../assets/img/qr_code_user/' . uniqid() . '.' . $qrUser_extension;
                $qrStaff_path = '../assets/img/qr_code_staff/' . uniqid() . '.' . $qrStaff_extension;

                if (
                    move_uploaded_file($logo['tmp_name'], $logo_path) &&
                    move_uploaded_file($qrUser['tmp_name'], $qrUser_path) &&
                    move_uploaded_file($qrStaff['tmp_name'], $qrStaff_path)
                ) {

                    $conn->beginTransaction(); // เริ่มต้นการทำธุรกรรม

                    try {
                        // ใส่ข้อมูลลงในตาราง assets
                        $sql = $conn->prepare("INSERT INTO assets (type, name01, name02, logo, qrUser, qrStaff, status) 
                        VALUES (:type, :name01, :name02, :logo, :qrUser, :qrStaff, :status)");
                        $status = 'active'; // กำหนดค่า status ตามที่ต้องการ
                        $sql->bindParam(":type", $type);
                        $sql->bindParam(":name01", $name01);
                        $sql->bindParam(":name02", $name02);
                        $sql->bindParam(":logo", basename($logo_path));
                        $sql->bindParam(":qrUser", basename($qrUser_path));
                        $sql->bindParam(":qrStaff", basename($qrStaff_path));
                        $sql->bindParam(":status", $status);
                        $sql->execute();

                        $conn->commit();

                        $_SESSION['Uploadsuccess'] = "เพิ่มข้อมูลสำเร็จ";
                        header('location: ' . $base_url . '/management-website');
                        exit();
                    } catch (Exception $e) {
                        // ยกเลิกการทำธุรกรรม
                        $conn->rollBack();
                        $_SESSION['errorUpload'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                        header('location: ' . $base_url . '/management-website');
                        exit();
                    }
                } else {
                    $_SESSION['errorUpload'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ภาพ";
                    header('location: ' . $base_url . '/management-website');
                    exit();
                }
            } else {
                $_SESSION['errorUpload'] = "ประเภทของไฟล์ภาพไม่ถูกต้อง (รูปภาพ: jpg, jpeg, png)";
                header('location: ' . $base_url . '/management-website');
                exit();
            }
        }
    } else {
        $_SESSION['errorUpload'] = "ประเภทของไฟล์ภาพไม่ถูกต้อง (รูปภาพ: jpg, jpeg, png)";
        header('location: ' . $base_url . '/management-website');
        exit();
    }
}

// <-------------- UPDATE ---------------->
if (isset($_POST['Update_Website'])) {
    $id = trim($_POST['id']);
    $type = trim($_POST['type']);
    $name01 = trim($_POST['name01']);
    $name02 = trim($_POST['name02']);

    $logo = $_FILES['logo'];
    $qrUser = $_FILES['qrUser'];
    $qrStaff = $_FILES['qrStaff'];

    $allowed = array('jpg', 'jpeg', 'png');

    $updateLogo = false;
    $updateQrUser = false;
    $updateQrStaff = false;

    // ดึงข้อมูลรูปภาพปัจจุบันจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT logo, qrUser, qrStaff FROM assets WHERE ID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($logo['size'] > 0 && in_array(strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION)), $allowed)) {
        $logo_path = '../assets/img/logo/' . uniqid() . '.' . strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
        if (move_uploaded_file($logo['tmp_name'], $logo_path)) {
            // ลบไฟล์รูปภาพโลโก้เดิม
            if (file_exists('../assets/img/logo/' . $row['logo'])) {
                unlink('../assets/img/logo/' . $row['logo']);
            }
            $updateLogo = true;
        }
    }

    if ($qrUser['size'] > 0 && in_array(strtolower(pathinfo($qrUser['name'], PATHINFO_EXTENSION)), $allowed)) {
        $qrUser_path = '../assets/img/qr_code_user/' . uniqid() . '.' . strtolower(pathinfo($qrUser['name'], PATHINFO_EXTENSION));
        if (move_uploaded_file($qrUser['tmp_name'], $qrUser_path)) {
            // ลบไฟล์รูปภาพผู้ใช้เดิม
            if (file_exists('../assets/img/qr_code_user/' . $row['qrUser'])) {
                unlink('../assets/img/qr_code_user/' . $row['qrUser']);
            }
            $updateQrUser = true;
        }
    }

    if ($qrStaff['size'] > 0 && in_array(strtolower(pathinfo($qrStaff['name'], PATHINFO_EXTENSION)), $allowed)) {
        $qrStaff_path = '../assets/img/qr_code_staff/' . uniqid() . '.' . strtolower(pathinfo($qrStaff['name'], PATHINFO_EXTENSION));
        if (move_uploaded_file($qrStaff['tmp_name'], $qrStaff_path)) {
            // ลบไฟล์รูปภาพพนักงานเดิม
            if (file_exists('../assets/img/qr_code_staff/' . $row['qrStaff'])) {
                unlink('../assets/img/qr_code_staff/' . $row['qrStaff']);
            }
            $updateQrStaff = true;
        }
    }

    try {
        $conn->beginTransaction();
        $sql = "UPDATE assets SET type = :type, name01 = :name01, name02 = :name02";

        if ($updateLogo) {
            $sql .= ", logo = :logo";
        }
        if ($updateQrUser) {
            $sql .= ", qrUser = :qrUser";
        }
        if ($updateQrStaff) {
            $sql .= ", qrStaff = :qrStaff";
        }

        $sql .= " WHERE ID = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":name01", $name01);
        $stmt->bindParam(":name02", $name02);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($updateLogo) {
            $stmt->bindParam(":logo", basename($logo_path));
        }
        if ($updateQrUser) {
            $stmt->bindParam(":qrUser", basename($qrUser_path));
        }
        if ($updateQrStaff) {
            $stmt->bindParam(":qrStaff", basename($qrStaff_path));
        }

        $stmt->execute();
        $conn->commit();

        $_SESSION['Uploadsuccess'] = "บันทึกการแก้ไขข้อมูลสำเร็จ";
        header('location: ' . $base_url . '/management-website');
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errorUpload'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        header('location: ' . $base_url . '/management-website');
        exit();
    }
}

// <-------------- DELETE ---------------->
// ตรวจสอบว่ามีการส่งคำขอลบข้อมูล
if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // ดึงข้อมูลรูปภาพจากฐานข้อมูลก่อนลบ
    $stmt = $conn->prepare("SELECT logo, qrUser, qrStaff FROM assets WHERE ID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // ลบข้อมูลตาม ID ที่ได้รับ
        $stmt = $conn->prepare("DELETE FROM assets WHERE ID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // ลบไฟล์รูปภาพออกจากโฟลเดอร์
        $logo_path = '../assets/img/logo/' . $row['logo'];
        $qrUser_path = '../assets/img/qr_code_user/' . $row['qrUser'];
        $qrStaff_path = '../assets/img/qr_code_staff/' . $row['qrStaff'];

        if (file_exists($logo_path)) {
            unlink($logo_path);
        }
        if (file_exists($qrUser_path)) {
            unlink($qrUser_path);
        }
        if (file_exists($qrStaff_path)) {
            unlink($qrStaff_path);
        }

        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
    } else {
        $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
    }

    header('location: /management-website');
    exit();
}

// <-------------- UPDATE STATUS ---------------->
if (isset($_POST['select'])) {
    $id = $_POST['id'];

    try {
        $conn->beginTransaction();

        // Set all statuses to 0
        $stmt = $conn->prepare("UPDATE assets SET status = 0");
        $stmt->execute();

        // Set the selected website status to 1
        $stmt = $conn->prepare("UPDATE assets SET status = 1 WHERE ID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();

        $_SESSION['success'] = "อัปเดตเว็บไซต์สำเร็จ";
        header('location: ' . $base_url . '/management-website');
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        header('location: ' . $base_url . '/management-website');
        exit();
    }
}
