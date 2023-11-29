<?php
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script> -->
    <title>เพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- ส่วนของ Link -->
    <link rel="stylesheet" href="add-remove-update.css">
</head>

<body>
    <header>
        เพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
    </header>
    <div class="main">
        <div class="btn-add">
            เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ
        </div>
        <div class="container">
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="text-center justify-content-center align-items-center p-4 border-2 border-dashed rounded-3">
                    <h6 class="my-2">Select image file to upload</h6>
                    <input type="file" name="file" class="form-control streched-link" accept="image/gif, image/jpeg, image/png">
                    <p class="small md-0 mt-2"><b>Note:</b>Only JPG, JPEG, PNG & GIF files allowed to upload.</p>
                    <label for="product_name">ชื่ออุปกรณ์:</label>
                    <input type="text" id="product_name" name="product_name" required>
                    <label for="quantity">จำนวนอุปกรณ์:</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                    <label for="product_type"> ประเภทอุปกรณ์:</label>
                    <select name="productType" id="productType">
                        <option value="วัตถุ">วัตถุ</option>
                        <option value="อุปกรณ์">อุปกรณ์</option>
                        <option value="เครื่องมือ">เครื่องมือ</option>
                    </select>
                </div>
                <div class="d-sm-flex justify-content-end mt-2">
                    <input type="submit" name="submit" value="Upload" class="btn btn-sm btn-primary mb-3">
                    <a href="ajax.php">กลับหน้าหลัก</a>
                </div>
            </form>
        </div>
        <div class="row">
            <?php
            if (!empty($statusMsg)) { ?>
                <div class="alert alert-secondary" role="alert">
                    <?php
                    echo $statusMsg;
                    ?>
                </div>
            <?php }
            ?>
        </div>
        
        </div>
    </div>

</body>

</html>