<?php
session_start();
include_once '../assets/database/connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- ส่วนของ Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="add-remove-update.js">
    <link rel="stylesheet" href="add-remove-update.css">
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
                        <label for="product_name">ชื่อ :</label>
                        <input type="text" id="product_name" name="product_name" required placeholder="กรุณาระบุชื่อของวัสดุ อุปกรณ์ และเครื่องมือ">
                    </div>
                    <div class="col">
                        <div class="input-box">
                            <label>จำนวน :</label>
                            <input type="number" id="quantity" name="quantity" min="1" required placeholder="กรุณาระบุจำนวน">
                        </div>
                        <div class="input-box">
                            <label for="">ประเภท :</label>
                            <select name="productType" id="productType">
                                <option value="" disabled selected>กรุณาเลือก</option>
                                <option value="วัสดุ">วัสดุ</option>
                                <option value="อุปกรณ์">อุปกรณ์</option>
                                <option value="เครื่องมือ">เครื่องมือ</option>
                            </select>
                        </div>
                    </div>
                    <div class="btn">
                        <button type="submit" name="submit" value="Upload" class="">ยืนยัน</button>
                        <button type="reset" class="reset">ล้างข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Header -->
    <div class="main">
        <div class="container">
            <div class="head-section">
                <div class="head-name">
                    ระบบเพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
                </div>
                <div class="head-btn">
                    <button class="cancel" onclick="window.location.href='../index.php';"><i class="icon fa-solid fa-xmark"></i>ยกเลิกการเพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</button>
                    <button class="showPopup add"><i class="icon fa-solid fa-plus"></i>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</button>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div class="main-1">
        <div class="display-crud">
            <table class="crud-display-table">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>รูปภาพ</th>
                        <th>ชื่อ</th>
                        <th>ประเภท</th>
                        <th>จำนวนคงเหลือ</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <div class="line"></div>
                <tbody>
                    <?php
                    $num = 1;
                    $query = $conn->query("SELECT * FROM crud ORDER BY uploaded_on DESC");
                    if ($query) {
                        while ($row = $query->fetch()) {
                            $imageURL = '../uploads/' . $row['file_name'];
                    ?>
                            <tr>
                                <td>
                                    <p><?php echo $num ?></p>
                                </td>
                                <td>
                                    <div class="img"><img src="<?php echo $imageURL ?>" alt=""></div>
                                </td>
                                <td class="product-name"><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['Type']; ?></td>
                                <td>
                                    <p>คงเหลือ : <?php echo $row['amount']; ?></p>
                                </td>
                                <td class="process">
                                    <div class="btn-process">
                                        <button onclick="window.location.href='edit.php?id=<?php echo $row['id']; ?>'" class="Edit"><i class="icon fa-solid fa-pen-to-square"></i><span>Edit</span></button>
                                        <button onclick="window.location.href='delete.php?id=<?php echo $row['id']; ?>'" class="Delete"><i class="icon fa-solid fa-trash"></i><span>Delete</span></button>
                                    </div>
                                </td>
                                <td>
                            </tr>
                        <?php
                            $num++;
                        }
                    } else {
                        ?>
                        <!-- <tr><td colspan="7">ไม่พบข้อมูล</td></tr> -->
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
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