<?php
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- ส่วนของ Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="add-remove-update.css">
    <link rel="stylesheet" href="/add-remove-update.js">
</head>

<body>
    <!-- Modal Popup -->
    <section class="modal-popup">
        <div class="modal-box">
            <div class="modal-head">
                <p>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</p>
                <i class="close fa-solid fa-x "></i>
            </div>
            <div class="input-form">
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="Imginput">
                        <label class="img">เลือกรูปภาพที่จะอัพโหลด</label>
                        <input type="file" name="file" class="form-control streched-link" accept="image/gif, image/jpeg, image/png" required id="Imginput">
                        <img id="previewImg" alt="">
                        <p class=""><b>Note:</b>Only JPG, JPEG, PNG & GIF files allowed to upload.</p>
                    </div>
                    <div class="input-box">
                        <label for="product_name">เลขประจำตัว :</label>
                        <input type="text" id="" name="">
                    </div>
                    <div class="input-box">
                        <label for="product_name">ชื่ออุปกรณ์ :</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>
                    <div class="input-box">
                        <label for="quantity">จำนวนอุปกรณ์ :</label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="input-box">
                        <label for="product_type"> ประเภทอุปกรณ์ :</label>
                        <select name="productType" id="productType">
                            <option value="วัตถุ">วัตถุ</option>
                            <option value="อุปกรณ์">อุปกรณ์</option>
                            <option value="เครื่องมือ">เครื่องมือ</option>
                        </select>
                    </div>
                    <div class="">
                        <input type="submit" name="submit" value="Upload" class="btn btn-sm btn-primary mb-3">
                        <a href="ajax.php">กลับหน้าหลัก</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Header -->
    <header>
        เพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
    </header>
    <div class="main">
        <div class="container">
            <div class="head-section">
                <div class="head-name">
                    ระบบเพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
                </div>
                <div class="btn-add">
                    <button class="showPopup">เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</button>
                </div>
            </div>
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
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script>
        const showPopup = document.querySelector(".showPopup");
        const modalpopup = document.querySelector(".modal-popup");

        showPopup.onclick = () => {
            modalpopup.classList.add("active");
        };
    </script>
</body>

</html>