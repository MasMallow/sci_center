<?php
session_start();
include_once '../assets/database/connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php
try {
    // สร้าง SQL เพื่อดึงข้อมูลจากฐานข้อมูล
    $sql = "SELECT * FROM crud";
    if (isset($_GET["search"]) && !empty($_GET["search"])) {
        $search = $_GET["search"];
        $sql .= " WHERE sci_name LIKE :search";
    }
    $sql .= " ORDER BY uploaded_on DESC";
    $stmt = $conn->prepare($sql);

    // ถ้ามีการค้นหา ให้ผูกค่าพารามิเตอร์
    if (isset($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num = 1;
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการวัสดุ อุปกรณ์ และเครื่องมือ</title>

    <!-- ส่วนของ Link -->
    <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/navigator.css">
    <link rel="stylesheet" href="add-remove-update.css">
</head>

<body>
    <!-- Header -->
    <?php
    include('header.php')
    ?>
    <div class="main">
        <div class="container">
            <div class="head-section">
                <div class="head-name">
                    ระบบเพิ่ม ลบ แก้ไข วัสดุ อุปกรณ์ และเครื่องมือ
                </div>
                <div class="head-btn">
                    <button class="cancel" onclick="window.location.href='../home.php';">
                        <i class="icon fa-solid fa-xmark"></i>ยกเลิกการเพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</button>
                    <a class="showPopup add" href="add.php"><i class="icon fa-solid fa-plus"></i>เพิ่มวัสดุ อุปกรณ์ และเครื่องมือ</a>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div class="content_area_grid">
        <?php
        if (empty($result)) { ?>
            <div class="grid_content_not_found">ไม่พบข้อมูล</div>
            <?php
        } else {
            foreach ($result as $results) {
            ?>
                <div class="grid_content">
                    <div class="grid_content_header">
                        <div class="content_img">
                            <img src="../assets/uploads/<?php echo $results['img']; ?>">
                        </div>
                    </div>
                    <div class="content_status_details">
                        <?php
                        if ($results['amount'] >= 50) {
                        ?>
                            <div class="ready-to-use">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="B">พร้อมใช้งาน</span>
                            </div>
                        <?php } elseif ($results['amount'] <= 30 && $results['amount'] >= 1) { ?>
                            <div class="moderately">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="B">ความพร้อมปานกลาง</span>
                            </div>
                        <?php } elseif ($results['amount'] == 0) { ?>
                            <div class="not-available">
                                <i class="fa-solid fa-ban"></i>
                                <span id="B">ไม่พร้อมใช้งาน</span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="grid_content_body">
                        <div class="content_name">
                            <span id="B">ชื่อ </span><?php echo $results['sci_name']; ?>
                        </div>
                        <div class="content_categories">
                            <span id="B">ประเภท </span><?php echo $results['categories']; ?>
                        </div>
                        <div class="content_amount">
                            <span id="B">คงเหลือ </span><?php echo $results['amount']; ?>
                        </div>
                    </div>
                    <div class="grid_content_footer">
                        <div class="content_btn">
                            <div class="btn-process">
                                <a href="edit.php?id=<?php echo $results['id']; ?>" class="Edit"> <!-- ลิงก์แก้ไขสินค้า -->
                                    <i class="icon fa-solid fa-pen-to-square"></i><span>Edit</span>
                                </a>
                                <a href="delete.php?id=<?php echo $results['id']; ?>" class="Delete"> <!-- ลิงก์ลบสินค้า -->
                                    <i class="icon fa-solid fa-trash"></i><span>Delete</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>
    <div class="management">
        <div class="count_list">
            <div class="count_list_1">
                <span>รายการที่เลือกทั้งหมด </span>
                <?php echo count($_SESSION['cart']); ?><span> รายการ</span>
            </div>
        </div>
        <table class="management_section_table">
            <thead>
                <tr>
                    <th class="th_num"><span id="B">ลำดับ</span></th>
                    <th class="th_img"></th>
                    <th class="th_name"><span id="B">ชื่อรายการ</span></th>
                    <th class="th_categories"><span id="B">ประเภท</span></th>
                    <th class="th_amount"><span id="B">จำนวน</span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = 1;
                // ดึงรายละเอียดสินค้าจากฐานข้อมูล
                $query = $conn->prepare("SELECT * FROM crud ORDER BY uploaded_on DESC");
                $query->execute();
                $products = $query->fetchAll(PDO::FETCH_ASSOC); // ดึงข้อมูลทั้งหมดและเก็บในตัวแปรอาร์เรย์
                if (!empty($products)) {
                    foreach ($products as $product) { // ลูปผ่านข้อมูลสินค้าแต่ละรายการ
                        // กำหนดตัวแปรข้อมูลสินค้าจากฐานข้อมูล
                        $categories = $product['categories'];
                        $productName = $product['sci_name'];
                        $imageURL = '../assets/uploads/' . $product['img'];
                ?>
                        <tr>
                            <td>
                                <p><?php echo $num; ?></p> <!-- แสดงลำดับที่ -->
                            </td>
                            <td>
                                <img src="<?php echo $imageURL; ?>" alt=""> <!-- แสดงรูปภาพสินค้า -->
                            </td>
                            <td><?php echo $productName; ?></td> <!-- แสดงชื่อสินค้า -->
                            <td><?php echo $categories; ?></td> <!-- แสดงหมวดหมู่สินค้า -->
                            <td>
                                <p><?php echo $product['amount']; ?></p>
                                <div class="btn-process">
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="Edit"> <!-- ลิงก์แก้ไขสินค้า -->
                                        <i class="icon fa-solid fa-pen-to-square"></i><span>Edit</span>
                                    </a>
                                    <a href="delete.php?id=<?php echo $product['id']; ?>" class="Delete"> <!-- ลิงก์ลบสินค้า -->
                                        <i class="icon fa-solid fa-trash"></i><span>Delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php
                        $num++; // เพิ่มลำดับที่
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7">ไม่พบข้อมูล</td>
                    </tr> <!-- แสดงข้อความเมื่อไม่มีข้อมูลสินค้า -->
                <?php
                }
                ?>
            </tbody>
        </table>
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
            reader.readAsresultsURL(image);
        })
    </script>
</body>

</html>