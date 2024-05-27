<!-- <header class="head-search">
    <div class="name">
        <h2>หมวดวัสดุ</h2>
    </div>
    <div class="search">
        <input type="text" name="search" id="searchInput" placeholder="ป้อนชื่อวัสดุเพื่อค้นหา" class="search">
        <button type="button" onclick="searchProducts()" class="search"><i class="icon fa-solid fa-magnifying-glass">
                <p>ค้นหา</p>
            </i></button>
    </div>
</header> -->
<?php
try {
    $query = $conn->query("SELECT * FROM crud WHERE categories = 'อุปกรณ์' ORDER BY RAND() DESC");
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $displayedImages = array();
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>

<div class="content_area">
    <nav class="content_area_nav">
        <div class="section_1">
            <div class="section_1_btn_1">
                <a href="cart.php">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>รายการที่เลือกทั้งหมด</span>
                </a>
            </div>
            <div class="section_1_btn_2">
                <a href="reserve_cart.php">
                    <i class="fa-solid fa-thumbtack"></i>
                    <span>รายการที่จอง</span>
                </a>
            </div>
            <div class="section_1_btn_3">
                <a href="booking_log.php">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>ดูประวัติการจองก่อนยืมใช้</span>
                </a>
            </div>
        </div>
        <div class="section_2">
            <div class="date" id="date"></div>
            <div class="time" id="time"></div>
        </div>
    </nav>
    <div class="content_area_grid">
        <?php
        foreach ($results as $data) {
            $imageURL = 'assets/uploads/' . $data['img'];
            if (!in_array($imageURL, $displayedImages)) {
                $displayedImages[] = $imageURL;
        ?>
                <div class="grid_content">
                    <div class="grid_content_header">
                        <div class="content_img">
                            <img src="<?php echo $imageURL ?>" alt="">
                        </div>
                    </div>
                    <div class="content_status_details">
                        <?php
                        if ($data['amount'] >= 50) {
                        ?>
                            <div class="ready-to-use">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="B">พร้อมใช้งาน</span>
                            </div>
                        <?php } elseif ($data['amount'] <= 30 && $data['amount'] >= 1) { ?>
                            <div class="moderately">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="B">ความพร้อมปานกลาง</span>
                            </div>
                        <?php } elseif ($data['amount'] == 0) { ?>
                            <div class="not-available">
                                <i class="fa-solid fa-ban"></i>
                                <span id="B">ไม่พร้อมใช้งาน</span>
                            </div>
                        <?php } ?>
                        <div class="content_details">
                            <button class="details_btn" data-modal="<?php echo $data['id']; ?>">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                        </div>
                        <div class="content_details_popup" id="<?php echo $data['id']; ?>">
                            <div class="details">
                                <div class="details_header">
                                    <span id="B">รายละเอียด</span>
                                    <div class="modalClose" id="closeDetails">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="details_content">
                                    <ul class="details_content_li">
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">สถานะ</span>
                                            </div>
                                            <div class="details_content_2">
                                                <?php if ($data['amount'] >= 50) { ?>
                                                    <div class="ready-to-use">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                        <span id="B">พร้อมใช้งาน</span>
                                                    </div>
                                                <?php } elseif ($data['amount'] <= 30 && $data['amount'] >= 1) { ?>
                                                    <div class="moderately">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                        <span id="B">ความพร้อมปานกลาง</span>
                                                    </div>
                                                <?php } elseif ($data['amount'] == 0) { ?>
                                                    <div class="not-available">
                                                        <i class="fa-solid fa-ban"></i>
                                                        <span id="B">ไม่พร้อมใช้งาน</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">Serial Number</span>
                                            </div>
                                            <div class="details_content_2">
                                                <span>190605002DZ12P054</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">ชื่อ</span>
                                            </div>
                                            <div class="details_content_2">
                                                <?php echo $data['sci_name']; ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">ประเภท</span>
                                            </div>
                                            <div class="details_content_2">
                                                <?php echo $data['categories']; ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">จำนวน</span>
                                            </div>
                                            <div class="details_content_2">
                                                <?php echo $data['amount']; ?>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">รุ่น</span>
                                            </div>
                                            <div class="details_content_2">
                                                <span>BK-FD12P</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">ยี่ห้อ</span>
                                            </div>
                                            <div class="details_content_2">
                                                <span>BIOBASE</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="details_content_1">
                                                <span id="B">บริษัท</span>
                                            </div>
                                            <div class="details_content_2">
                                                <span>BIOBASE BIODUSTRY(SHANDONG) CO.,LTD</span>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="details_content_footer">
                                        <div class="content_btn">
                                            <?php if ($data['amount'] >= 1) { ?>
                                                <div class="button">
                                                    <button onclick="location.href='cart.php?action=add&item=<?php echo $data['img']; ?>'" class="use-it">
                                                        <i class="icon fa-solid fa-arrow-up"></i>
                                                        <span>ขอใช้</span>
                                                    </button>
                                                </div>
                                            <?php } else { ?>
                                                <div class="button">
                                                    <button class="out-of">
                                                        <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                                        <span>ไม่สามารถขอใช้ได้</span>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid_content_body">
                        <div class="content_name">
                            <span id="B">ชื่อ </span><?php echo $data['sci_name']; ?>
                        </div>
                        <div class="content_categories">
                            <span id="B">ประเภท </span><?php echo $data['categories']; ?>
                        </div>
                        <div class="content_amount">
                            <span>คงเหลือ : <?php echo $data['amount']; ?></span>
                        </div>
                    </div>
                    <div class="grid_content_footer">
                        <div class="content_btn">
                            <?php if ($data['amount'] >= 1) { ?>
                                <a href="cart.php?action=add&item=<?= $data['img'] ?>" class="used_it">
                                    <i class="icon fa-solid fa-arrow-up"></i>
                                    <span>ขอใช้อุปกรณ์</span>
                                </a>
                            <?php } else { ?>
                                <div class="not_available">
                                    <i class="icon fa-solid fa-ban"></i>
                                    <span>อุปกรณ์ "ไม่พร้อมใช้งาน"</span>
                                </div>
                            <?php } ?>
                            <?php if ($data['amount'] >= 1) { ?>
                                <a href="reserve_cart.php?action=add&item=<?= $data['img'] ?>" class="reserved_it">
                                    <i class="fa-solid fa-address-book"></i>
                                    <span>จองอุปกรณ์</span>
                                </a>
                            <?php } else { ?>
                                <div class="not_available">
                                    <i class="icon fa-solid fa-check"></i>
                                    <span>อุปกรณ์ "ไม่พร้อมใช้งาน"</span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>
</div>