<?php
include_once '../assets/database/connect.php';

if (isset($_POST['submit'])) {
    // รับข้อมูลจากฟอร์ม
    $sci_name = $_POST['sci_name'];
    $s_number = $_POST['s_number'];
    $amount = $_POST['amount'];
    $categories = $_POST['categories'];
    $details = $_POST['details'];
    $installation_date = $_POST['installation_date'];
    $company = $_POST['company'];
    $contact_number = $_POST['contact_number'];
    $contact = $_POST['contact'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];

    // Upload Thumbnail
    $img = $_FILES['img'];
    $thumbnail_extension = pathinfo($img['name'], PATHINFO_EXTENSION);
    $thumbnail_path = '../assets/uploads/' . uniqid() . '.' . $thumbnail_extension;

    // ตรวจสอบประเภทของไฟล์ภาพ
    $allow = array('jpg', 'jpeg', 'png');
    if (in_array($thumbnail_extension, $allow)) {
        if ($img['size'] > 0 && $img['error'] == 0) {

            // ตรวจสอบว่ามีชื่อไฟล์ภาพอยู่ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM crud WHERE img = :img");
            $stmt->bindParam(":img", $img['name']);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $_SESSION['error'] = "ชื่อไฟล์ภาพนี้ถูกใช้ไปแล้ว";
                header("location: add");
                exit();
            } else {
                // อัปโหลดไฟล์ภาพ
                if (move_uploaded_file($img['tmp_name'], $thumbnail_path)) {
                    // เพิ่มข้อมูลลงในฐานข้อมูล
                    $thumbnail_new_name = basename($thumbnail_path);
                    date_default_timezone_set('Asia/Bangkok'); // ตั้งค่าโซนเวลาเป็น Asia/Bangkok
                    $uploaded = date("Y-m-d H:i:s"); // ใส่วันที่และเวลาปัจจุบัน
                    $sql = $conn->prepare("INSERT INTO crud (img, sci_name, s_number, amount, categories, details, installation_date, company, contact_number, contact, brand, model, uploaded_on) 
                        VALUES(:img, :sci_name, :s_number, :amount, :categories, :details, :installation_date, :company, :contact_number, :contact, :brand, :model, :uploaded)");
                    $sql->bindParam(":img", $thumbnail_new_name);
                    $sql->bindParam(":sci_name", $sci_name);
                    $sql->bindParam(":s_number", $s_number);
                    $sql->bindParam(":amount", $amount);
                    $sql->bindParam(":categories", $categories);
                    $sql->bindParam(":details", $details);
                    $sql->bindParam(":installation_date", $installation_date);
                    $sql->bindParam(":company", $company);
                    $sql->bindParam(":contact_number", $contact_number);
                    $sql->bindParam(":contact", $contact);
                    $sql->bindParam(":brand", $brand);
                    $sql->bindParam(":model", $model);
                    $sql->bindParam(":uploaded", $uploaded);
                    $sql->execute();

                    if ($sql) {
                        $_SESSION['success'] = "เพิ่มข้อมูลสำเร็จ <a href='dashboard.php'><span id='B'>กลับหน้า Dashboard</span></a>";
                        header("location: add");
                        exit();
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                        header("location: add");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ภาพ";
                    header("location: add");
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = "ขนาดของไฟล์ภาพหรือข้อผิดพลาดในการอัปโหลด";
            header("location: add");
            exit();
        }
    } else {
        $_SESSION['error'] = "ประเภทของไฟล์ภาพไม่ถูกต้อง (รูปภาพ: jpg, jpeg, png)";
        header("location: add");
        exit();
    }
} else {
    $_SESSION['error'] = "คุณไม่ได้ส่งคำขอเพิ่มข้อมูล";
    header("location: add");
    exit();
}
?>