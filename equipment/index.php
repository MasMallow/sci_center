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
        try {
            $query = $conn->query("SELECT * FROM crud WHERE categories = 'อุปกรณ์' ORDER BY uploaded_on DESC");
            $displayedImages = array(); // สร้างอาร์เรย์เพื่อเก็บ URL รูปภาพที่แสดงแล้ว
            while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
                $imageURL = 'uploads/' . $data['img'];
                if (!in_array($imageURL, $displayedImages)) { // ตรวจสอบว่า URL รูปภาพนี้ถูกแสดงแล้วหรือไม่
                    $displayedImages[] = $imageURL; // เพิ่ม URL รูปภาพนี้เข้าไปในอาร์เรย์

                    // แสดงรายการสินค้า
        ?>
                    <div class="grid_content">
                        <div class="grid_content_header">
                            <div class="content_img">
                                <img src="<?php echo $imageURL ?>" alt="">
                            </div>
                        </div>
                        <div class="content_status">
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
                                <?php
                                // แสดงปุ่มขอใช้วัสดุ อุปกรณ์ และเครื่องมือ หรือแสดงข้อความเมื่อสินค้าหมด
                                if ($data['amount'] >= 1) {
                                ?>
                                    <div class="button">
                                        <button onclick="location.href='cart.php?action=add&item=<?= $data['img'] ?>'" class="use-it">
                                            <i class="icon fa-solid fa-ardata-up"></i>
                                            <span>ขอใช้วัสดุ อุปกรณ์ และเครื่องมือ</ห>
                                        </button>
                                    </div>
                                <?php } else { ?>
                                    <div class="button">
                                        <button class="out-of">
                                            <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                            <span>วัสดุ อุปกรณ์ และเครื่องมือ "หมด"</span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
        <?php
                }
            }
        } catch (PDOException $e) {
            echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
        ?>
    </div>

</div>