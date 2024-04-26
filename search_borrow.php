<link rel="stylesheet" href="search.css">
<link rel="stylesheet" href="ajax.css">

<?php
include_once 'assets/database/connect.php';
$query = $conn->query("SELECT * FROM crud ORDER BY uploaded_on DESC");
if ($query->rowCount() > 0) {
    // สร้างตัวแปรเพื่อเก็บรายชื่อรูปภาพที่แสดงแล้ว
    $displayedImages = array();
?>
    <?php
    // Check if a search query is provided
    if (isset($_GET['search'])) {
        $search_query = $_GET['search'];
        $query = $conn->query("SELECT * FROM crud WHERE product_name LIKE '%$search_query%' ORDER BY uploaded_on DESC");
    } else {
        // No search query, display all images
        $query = $conn->query("SELECT * FROM crud ORDER BY uploaded_on DESC");
    }

    if ($query->rowCount() > 0) {
        // ... Rest of the code to display images ...
    } else {
        echo "<p>No image found...</p>";
    }
    ?>

    <div class="display-system">
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
            while ($row = $query->fetch()) {
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
                                <p>คงเหลือ : <?php echo $row['amount']; ?></p>
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
                            <td><?php if ($row['amount'] >= 1) {
                                ?>
                                    <div class="button">
                                        <button onclick="location.href='cart.php?action=add&item=<?= $row['file_name'] ?>'" class="use-it"><i class="icon fa-solid fa-arrow-up"></i>
                                            <p>ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ</p>
                                        </button>
                                    </div>
                                <?php } elseif ($row['amount'] <= 0) { ?>
                                    <div class="button">
                                        <button class="out-of">
                                            <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                            <p>วัสดุ อุปกรณ์ และเครื่องมือ "หมด"</p>
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