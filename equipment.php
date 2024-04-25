<link rel="stylesheet" href="search.css">
<link rel="stylesheet" href="ajax.css">

<?php
include_once 'db.php';
$query = $db->query("SELECT * FROM crud WHERE Type = 'อุปกรณ์' ORDER BY uploaded_on DESC");
if ($query->num_rows > 0) {
    // สร้างตัวแปรเพื่อเก็บรายชื่อรูปภาพที่แสดงแล้ว
    $displayedImages = array();
?>
    <header class="head-search">
        <div class="name">
            <h2>หมวดอุปกรณ์</h2>
        </div>
        <div class="search">
            <input type="text" name="search" id="searchInput" placeholder="ป้อนชื่ออุปกรณ์เพื่อค้นหา" class="search">
            <button type="button" onclick="searchProducts()" class="search"><i class="icon fa-solid fa-magnifying-glass">
                    <p>ค้นหา</p>
                </i></button>
        </div>
    </header>

    <?php
    // Check if a search query is provided
    if (isset($_GET['search'])) {
        $search_query = $_GET['search'];
        $query = $db->query("SELECT * FROM crud WHERE product_name LIKE '%$search_query%' ORDER BY uploaded_on DESC");
    } else {
        // No search query, display all images
        $query = $db->query("SELECT * FROM crud WHERE Type = 'อุปกรณ์' ORDER BY uploaded_on DESC");
    }

    if ($query->num_rows > 0) {
        // ... Rest of the code to display images ...
    } else {
        echo "<p>No image found...</p>";
    }
    ?>

    <!-- ส่วนแสดงตาราง -->
    <div class="display-system borrow">
        <table class="display-system-table">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อ</th>
                    <th>ประเภท</th>
                    <th>จำนวนคงเหลือ</th>
                    <th>สถานะ</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <?php
            while ($row = $query->fetch_assoc()) {
                $imageURL = 'uploads/' . $row['file_name'];
                if (!in_array($imageURL, $displayedImages)) {
                    $displayedImages[] = $imageURL;
            ?>
                    <tbody>
                        <tr>
                            <td>
                                <div class="img">
                                    <img src="<?php echo $imageURL ?>" alt="">
                                </div>
                            </td>
                            <td class="product-name">
                                <p><?php echo $row['product_name']; ?></p>
                            </td>
                            <td><?php echo $row['Type']; ?></td>
                            <td>
                                <p>คงเหลือ: <?php echo $row['amount']; ?></p>
                            </td>
                            <td>
                                <?php
                                if ($row['amount'] >= 50) {
                                ?>
                                    <div class="status">
                                        <div class="ready-to-use">
                                            <p>พร้อมใช้งาน</p>
                                        </div>
                                    </div>
                                <?php } elseif ($row['amount'] <= 30 && $row['amount'] >= 1) { ?>
                                    <div class="status">
                                        <div class="moderately">
                                            <p>ความพร้อมปานกลาง</p></i>
                                        </div>
                                    </div>
                                <?php
                                } elseif ($row['amount'] == 0) { ?>
                                    <div class="status">
                                        <div class="not-available">
                                            <p>ไม่พร้อมใช้งาน</p></i>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </td>
                            <td><?php
                                if ($row['amount'] >= 1) {
                                ?>
                                    <div class="button">
                                        <button onclick="location.href='cart.php?action=add&item=<?= $row['file_name'] ?>'" class="use-it"><i class="icon fa-solid fa-arrow-up"></i>
                                            <p>ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ</p>
                                        </button>
                                    </div>
                                    <div class="button">
                                        <button onclick="location.href='reserve_cart.php?action=add&item=<?= $row['file_name'] ?>'" class="reserve-it">
                                            <i class="icon fa-solid fa-check"></i>
                                            <p>จองวัสดุ อุปกรณ์ และเครื่องมือ</p>
                                        </button>
                                    </div>
                                <?php } elseif ($row['amount'] <= 0) { ?>
                                    <div class="button">
                                        <button class="out-of">
                                            <div class="icon"><i class="icon fa-solid fa-check"></i>
                                                <p>วัสดุ อุปกรณ์ และเครื่องมือ "หมด"</p></i>
                                            </div>

                                        </button>
                                    </div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
        <?php
                }
            }
        }
        ?>
        </table>
    </div>