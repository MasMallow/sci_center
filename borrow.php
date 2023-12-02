<style>
    div.borrow {
        height: 1100px;
        /* ลบความสูงคงที่เพื่อให้เนื้อหาสามารถขยายตัวได้ */
    }

    /* เพิ่มสไตล์ที่เป็น responsive สำหรับรูปภาพ */
    .borrow img {
        max-width: 100%;
        /* กำหนดความกว้างสูงสุดของรูปภาพให้เป็น 100% ของคอนเทนเนอร์ */
        height: auto;
        /* อนุญาตให้ความสูงของรูปภาพปรับตามอัตราส่วนได้ */
    }
</style>


<?php
include_once 'db.php';
$query = $db->query("SELECT * FROM crud WHERE Type = 'วัสดุ' ORDER BY uploaded_on DESC");
if ($query->num_rows > 0) {
    // สร้างตัวแปรเพื่อเก็บรายชื่อรูปภาพที่แสดงแล้ว
    $displayedImages = array();
?>
    <p>อุปกรณ์</p>
    <div>
        <input type="text" name="search" id="searchInput" placeholder="ค้นหาสินค้า">
        <button type="button" onclick="searchProducts()">ค้นหา</button>
    </div>

    <?php
    // Check if a search query is provided
    if (isset($_GET['search'])) {
        $search_query = $_GET['search'];
        $query = $db->query("SELECT * FROM crud WHERE product_name LIKE '%$search_query%' ORDER BY uploaded_on DESC");
    } else {
        // No search query, display all images
        $query = $db->query("SELECT * FROM crud WHERE Type = 'วัสดุ' ORDER BY uploaded_on DESC");
    }

    if ($query->num_rows > 0) {
        // ... Rest of the code to display images ...
    } else {
        echo "<p>No image found...</p>";
    }
    ?>

    <div class="borrow grid grid-cols-4">
        <?php
        while ($row = $query->fetch_assoc()) {
            $imageURL = 'test/' . $row['file_name'];
            if (!in_array($imageURL, $displayedImages)) {
                $displayedImages[] = $imageURL;
        ?>
                <div class="bg-white border-black rounded-md relative text-center mt-10">
                    <a href="#" class="flex justify-center">
                        <img src="<?php echo $imageURL ?>" alt="" class="rounded-md h-40 w-32 m-1">
                    </a>
                    <div class="mas p-1">
                        <a href="#">
                            <br>
                            <p>ชื่ออุปกรณ์: <?php echo $row['product_name']; ?></p>
                            <p>จำนวนคงเหลือ: <?php echo $row['amount']; ?></p>
                        </a>
                        <?php
                        if ($row['amount'] > 0) { // แก้เงื่อนไขนี้เพื่อไม่แสดงปุ่มเมื่อจำนวนคงเหลือน้อยกว่าหรือเท่ากับ 1
                        ?>
                            <a href="cart.php?action=add&item=<?= $row['file_name'] ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                Add to Cart
                                <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
    <?php
            }
        }
    }
    ?>