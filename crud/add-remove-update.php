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
    <link rel="stylesheet" href="add-remove-update.js">
</head>

<body>
    <!-- Modal Popup -->
    <section class="modal-popup">
        <div class="modal-box">
            <div class="modal-head">
                <p>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</p>
                <i id="close" class="close fa-solid fa-x "></i>
            </div>
            <div class="input-form">
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="Img">
                        <div class="imgInput">
                            <input id="file" type="file" name="file" class="form-control streched-link" accept="image/gif, image/jpeg, image/png" required id="Imginput" hidden>
                            <i class="upload fa-solid fa-upload"></i>
                            <label class="img">เลือกรูปภาพที่จะอัพโหลด</label>
                        </div>
                    </div>
                    <p class="upload-tip"><b>Note:</b>Only JPG, JPEG, PNG & GIF files allowed to upload.</p>
                    <button class="select-image">เลือกรูปภาพที่จะอัพโหลด</button>
                    <div class="input-box">
                        <label for="product_name">เลขประจำตัว :</label>
                        <input type="text" id="" name="">
                    </div>
                    <div class="input-box">
                        <label for="product_name">ชื่ออุปกรณ์ :</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>
                    <div class="col">
                        <div class="input-box">
                            <label>จำนวน :</label>
                            <input type="number" id="quantity" name="quantity" min="1" required>
                        </div>
                        <div class="input-box">
                            <label for="">ประเภท :</label>
                            <select name="product_type" id="product_type">
                                <option value="" disabled selected>กรุณาเลือก</option>
                                <option value="วัสดุ">วัสดุ</option>
                                <option value="อุปกรณ์">อุปกรณ์</option>
                                <option value="เครื่องมือ">เครื่องมือ</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="product_type">ประเภทอุปกรณ์ :</label>
                        <select name="productType" id="productType">
                            <option value="วัตถุ">วัตถุ</option>
                            <option value="อุปกรณ์">อุปกรณ์</option>
                            <option value="เครื่องมือ">เครื่องมือ</option>
                        </select>
                    </div>
                    <div class="">
                        <input type="submit" name="submit" value="Upload" class="">
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
            <hr>
        </div>

    </div>
    <div class="">
        <?php
        $query = $db->query("SELECT * FROM crud ORDER BY uploaded_on DESC");
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $imageURL = 'test/' . $row['file_name'];
        ?>
                <div class="main">
                    <div class="display-crud">
                        <table class="crud-display-table">
                            <thead>
                                <tr>
                                    <td>ลำดับ</td>
                                    <td>รูปภาพ</td>
                                    <td>เลขประจำตัว</td>
                                    <td>ชื่อ</td>
                                    <td>ประเภท</td>
                                    <td colspan="2">การดำเนินการ</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <p>1</p>
                                    </td>
                                    <td><img src="<?php echo $imageURL ?>" alt="" height="100px"></td>
                                    <td>lmdsakmop123214</td>
                                    <td><?php echo $row['product_name']; ?></td>
<<<<<<< HEAD
                                    <td><?php echo $row['Type']; ?></td>
                                    <td><?php echo $row['amount']; ?></td>
                                    <td> <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
=======
                                    <td>ประเภทอะไรสักอย่าง</td>
                                    <td> <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
>>>>>>> 8e81693901e6f91825f61e831f0a7de519a4e93f
                                    </td>
                                    <td> <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
            }
        } else {
            ?>
            <!-- <p>No image found...</p> -->
        <?php
        }
        ?>
    </div>
    <!-- JavaScprti -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script>
        // Modal Popup
        const showPopup = document.querySelector(".showPopup");
        const modalpopup = document.querySelector(".modal-popup");
        const closePopup = document.querySelector("#close");

        showPopup.onclick = () => {
            modalpopup.classList.add("active");
        };

        closePopup.onclick = () => {
            modalpopup.classList.remove("active");
        }


        // IMG PREVIREW
        const selectImage = document.querySelector('.select-image');
        const inputFile = document.querySelector('#file');
        const imgInput = document.querySelector('.imgInput');

        selectImage.addEventListener('click', function() {
            inputFile.click();
        })
        inputFile.addEventListener('change', function() {
            const image = this.files[0]
            console.log(image);
            const reader = new FileReader();
            reader.onload = () => {
                const imgUrl = reader.result;
                const img = document.createElement('img');
                img.src = imgUrl
                imgInput.appendChild(img);
            }
            reader.readAsDataURL(image);
        })
    </script>
</body>

</html>