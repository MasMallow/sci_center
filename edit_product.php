<?php
// Include the database connection file
include_once 'db.php';

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Retrieve product information based on the ID
    $query = $db->query("SELECT * FROM crud WHERE id = $id");

    if ($query->num_rows == 1) {
        $row = $query->fetch_assoc();
        $product_name = $row['product_name'];
        $quantity = $row['amount'];
        $imageURL = 'test/' . $row['file_name'];
    } else {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Product ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- LINK -->
    <link rel="stylesheet" href="edit_product.css">
    <link rel="stylesheet" href="edit_product.js">

</head>

<body>
    <div class="main">
        <div class="display">
            <h1>แก้ไขข้อมูล</h1>
            <form action="update_product.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="Img">
                    <div class="imgInput">
                        <img src="<?php echo $imageURL; ?>" alt="Product Image">
                        <input id="file" type="file" name="file" class="form-control streched-link" accept="image/gif, image/jpeg, image/png" required id="Imginput" hidden>
                    </div>
                </div>
                <p class="upload-tip"><b>Note:</b>Only JPG, JPEG, PNG & GIF files allowed to upload.</p>
                <button class="select-image">เลือกรูปภาพที่จะอัพโหลด</button>
                <div class="input-box">
                    <label>เลขประจำตัว: </label>
                    <input type="text" name="product_name" disabled value="<?php echo $product_name; ?>">
                </div>
                <div class="input-box">
                    <label>ชื่อ: </label>
                    <input type="text" name="product_name" value="<?php echo $product_name; ?>">
                </div>
                <div class="input-box">
                    <label>จำนวน: </label>
                    <input type="number" name="quantity" value="<?php echo $quantity; ?>">
                </div>
                <div class="input-box">
                    <label>ประเภท: </label>
                    <input type="number" name="type" value="<?php echo $quantity; ?>">
                </div>
                <div class="btn">
                    <input type="submit" value="บันทึกข้อมูล">
                    <a href="/add-remove-update.php">กลับหน้าเพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ</a>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
    const selectImage = document.querySelector(".select-image");
    const inputFile = document.querySelector("#file");
    const imgInput = document.querySelector(".imgInput");

    selectImage.addEventListener("click", function() {
        inputFile.click();
    });
    inputFile.addEventListener("change", function() {
        const image = this.files[0];
        console.log(image);
        const reader = new FileReader();
        reader.onload = () => {
            const imgUrl = reader.result;
            const img = document.createElement("img");
            img.src = imgUrl;
            imgInput.appendChild(img);
        };
        reader.readAsDataURL(image);
    });
</script>

</html>