<?php
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <title>UpLoad-Image</title>
</head>

<body>
    <div class="container">
        <div class="row mt-5">
            <div class="col-12">
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
                    </div>
                </form>
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
        <div class="row g-2">
            <?php
            $query = $db->query("SELECT * FROM image ORDER BY uploaded_on DESC");
            if ($query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    $imageURL = 'test/' . $row['file_name'];
            ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card shadow m-2">
                            <img src="<?php echo $imageURL ?>" alt="" class="card-img">
                            <div class="card-body">
                                <h5 class="card-title">Product Name: <?php echo $row['product_name']; ?></h5>
                                <p class="card-text">Quantity: <?php echo $row['amount']; ?></p>
                                <!-- Edit button -->
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                                <!-- Delete button -->
                                <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <p>No image found...</p>
            <?php
            }
            ?>
        </div>

    </div>
</body>

</html>