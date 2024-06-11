<?php
include_once '../assets/database/dbConfig.php';

// ฟังก์ชันลดขนาดรูปภาพ
function reduceImageSize($source_path, $destination_path, $extension)
{
    $quality = 50; // คุณภาพของรูปภาพ (ระหว่าง 0 ถึง 100)

    // ตรวจสอบประเภทของไฟล์รูปภาพและโหลดรูปภาพ
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'png':
            $source_image = imagecreatefrompng($source_path);
            break;
        default:
            // ประเภทไฟล์รูปภาพไม่ถูกต้อง
            return false;
    }

    // รับขนาดของรูปภาพเดิม
    $source_width = imagesx($source_image);
    $source_height = imagesy($source_image);

    // ตั้งค่าขนาดรูปภาพใหม่ (สามารถปรับได้ตามต้องการ)
    $new_width = $source_width; // คงความกว้างเดิม
    $new_height = $source_height; // คงความสูงเดิม

    // สร้างรูปภาพใหม่ด้วยขนาดที่ระบุ
    $destination_image = imagecreatetruecolor($new_width, $new_height);

    // คัดลอกรูปและเปลี่ยนขนาดรูปภาพไปยังรูปใหม่
    imagecopyresampled($destination_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);

    // บันทึกรูปภาพใหม่ลงในไฟล์
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($destination_image, $destination_path, $quality);
            break;
        case 'png':
            imagepng($destination_image, $destination_path, floor(9 * $quality / 100)); // ใช้ imagepng() พร้อมการปรับคุณภาพจาก 0 ถึง 9
            break;
    }

    // ทำลายรูปภาพจากหน่วยความจำ
    imagedestroy($destination_image);
    imagedestroy($source_image);

    return $destination_path;
}

// ตรวจสอบโลจิกหลัก
if (isset($_POST['submit'])) {
    // ดึงข้อมูลจากแบบฟอร์ม
    $sci_name = $_POST['sci_name'];
    $s_number = $_POST['s_number'];
    $amount = $_POST['amount'];
    $categories = $_POST['categories'];
    $installation_date = $_POST['installation_date'];
    $company = $_POST['company'];
    $contact_number = $_POST['contact_number'];
    $contact = $_POST['contact'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];

    // อัปโหลดรูปภาพ
    $img = $_FILES['img'];
    $thumbnail_extension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    $thumbnail_path = '../assets/uploads/' . uniqid() . '.' . $thumbnail_extension;

    // ตรวจสอบประเภทของไฟล์รูปภาพ
    $allow = array('jpg', 'jpeg', 'png');
    if (in_array($thumbnail_extension, $allow)) {
        if ($img['size'] > 0 && $img['error'] == 0) {
            // ตรวจสอบว่าชื่อไฟล์รูปภาพมีอยู่ในฐานข้อมูลแล้วหรือไม่
            $stmt = $conn->prepare("SELECT 1 FROM crud WHERE img = :img");
            $stmt->bindParam(":img", $img['name']);
            $stmt->execute();
            if ($stmt->fetchColumn()) {
                $_SESSION['error'] = "ชื่อไฟล์รูปภาพนี้ถูกใช้งานแล้ว";
                header("location: add");
                exit();
            } else {
                // ลดขนาดของรูปภาพ
                $reduced_image_path = reduceImageSize($img['tmp_name'], $thumbnail_path, $thumbnail_extension);

                // อัปโหลดไฟล์รูปภาพ
                if ($reduced_image_path) {
                    // เพิ่มข้อมูลลงในฐานข้อมูล
                    $thumbnail_new_name = basename($reduced_image_path);
                    date_default_timezone_set('Asia/Bangkok');
                    $uploaded = date("Y-m-d H:i:s");
                    $sql = $conn->prepare("INSERT INTO crud (img, sci_name, s_number, amount, categories, installation_date, company, contact_number, contact, brand, model, uploaded_on) 
                            VALUES (:img, :sci_name, :s_number, :amount, :categories, :installation_date, :company, :contact_number, :contact, :brand, :model, :uploaded)");
                    $sql->bindParam(":img", $thumbnail_new_name);
                    $sql->bindParam(":sci_name", $sci_name);
                    $sql->bindParam(":s_number", $s_number);
                    $sql->bindParam(":amount", $amount);
                    $sql->bindParam(":categories", $categories);
                    $sql->bindParam(":installation_date", $installation_date);
                    $sql->bindParam(":company", $company);
                    $sql->bindParam(":contact_number", $contact_number);
                    $sql->bindParam(":contact", $contact);
                    $sql->bindParam(":brand", $brand);
                    $sql->bindParam(":model", $model);
                    $sql->bindParam(":uploaded", $date_uploaded);
                    $sql->execute();


                    if ($sql) {
                        $_SESSION['success'] = "เพิ่มหนังสือเรียบร้อยแล้ว <a href='dashboard.php'><span id='B'>กลับสู่แดชบอร์ด</span></a>";
                        header("location: add");
                        exit();
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                        header("location: add");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์รูปภาพ";
                    header("location: add.php");
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = "ขนาดไฟล์รูปภาพไม่ถูกต้องหรือมีข้อผิดพลาดในการอัปโหลด";
            header("location: add");
            exit();
        }
    } else {
        $_SESSION['error'] = "ประเภทไฟล์รูปภาพไม่ถูกต้อง (เฉพาะ jpg, jpeg, png เท่านั้น)";
        header("location: add");
        exit();
    }
} else {
    $_SESSION['error'] = "คุณไม่ได้ส่งคำขอข้อมูล";
    header("location: add");
    exit();
}

if(isset($_POST["submit"]) && !$_FILES['file']['error']) {
    $file = $_FILES['file']['tmp_name'];
    $sourceProperties = getimagesize($file);
    $fileNewName=time();
    $folderPath = "uploads/";
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $imageType = $sourceProperties[2];
    
    switch ($imageType) {
    
    case IMAGETYPE_PNG:
    $imageResourceId = imagecreatefrompng($file);
    $targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
    imagepng($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
    break;
    
    case IMAGETYPE_GIF:
    $imageResourceId = imagecreatefromgif($file);
    $targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
    imagegif($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
    break;
    
    case IMAGETYPE_JPEG:
    $imageResourceId = imagecreatefromjpeg($file);
    $targetLayer = imageResize($imageResourceId, $sourceProperties[0],$sourceProperties[1]);
    imagejpeg($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
    break;
    
    default:
    o "Invalid Image type.":
    echo"T

}

default:
echo "Invalid Image type.";
exit;
break;

move_uploaded_file($file, $folderPath. $fileNewName. "_origin.". $ext);
} else {
header("location:

./");

function imageResize($imageResourceId,$width,$height) {
$targetWidth = $width < 1280 ? $width : 1280 ;
$targetHeight = ($height/$width)* $targetWidth;
$targetLayer = imagecreatetruecolor($targetWidth,$targetHeight);
imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
return $targetLayer;

/ ** show details */
function size_as_kb($size = 0) {
if($size < 1048576) {
$size_kb = round($size / 1024, 2);
return "{$size_kb} KB";
} else {
$size_mb = round($size / 1048576, 2);
return "{$size_mb} MB";