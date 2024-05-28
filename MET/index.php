<?php
$pageCategorys = '';

$searchTitle = "";
$searchValue = "";
if (isset($_GET['search'])) {
    $searchTitle = "ค้นหา \"" . $_GET['search'] . "\" | ";
    $searchValue = $_GET['search'];
}
?>
<?php
try {
    $sql = "SELECT * FROM crud";
    if (isset($_GET["search"]) && !empty($_GET["search"])) {
        $search = $_GET["search"];
        $sql .= " WHERE sci_name LIKE '%$search%'";
    }
    $sql .= " ORDER BY RAND() LIMIT 50;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}
?>

<div class="content_area">
    <nav class="content_area_nav">
        <div class="section_1">
            <div class="section_1_btn_1">
                <a href="cart">
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
    <div class="content_area_header">
        <form>
            <input class="search" type="search" name="search" value="<?php echo $searchValue ?>" placeholder="ค้นหา">
            <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <div class="content_area_all">
        <?php if (empty($result)) : ?>
            <div class="grid_content_not_found">
                <span id="B">ไม่พบข้อมูลที่ค้นหา</span><br>
                <span id="B"> ( </span> <?= htmlspecialchars($_GET["search"]) ?> <span id="B"> ) </span>
            </div>
        <?php else : ?>
            <div class="content_area_grid">
                <?php foreach ($result as $data) : ?>
                    <div class="grid_content">
                        <div class="grid_content_header">
                            <div class="content_img">
                                <img src="assets/uploads/<?= htmlspecialchars($data['img']) ?>">
                            </div>
                        </div>
                        <div class="content_status_details">
                            <?php if ($data['amount'] >= 50) : ?>
                                <div class="ready-to-use">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">พร้อมใช้งาน</span>
                                </div>
                            <?php elseif ($data['amount'] <= 30 && $data['amount'] >= 1) : ?>
                                <div class="moderately">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <span id="B">ความพร้อมปานกลาง</span>
                                </div>
                            <?php elseif ($data['amount'] == 0) : ?>
                                <div class="not-available">
                                    <i class="fa-solid fa-ban"></i>
                                    <span id="B">ไม่พร้อมใช้งาน</span>
                                </div>
                            <?php endif; ?>
                            <div class="content_details">
                                <button class="details_btn" data-modal="<?= htmlspecialchars($data['id']) ?>">
                                    <i class="fa-solid fa-circle-info"></i>
                                </button>
                            </div>
                            <div class="content_details_popup" id="<?= htmlspecialchars($data['id']) ?>">
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
                                                    <?php if ($data['amount'] >= 50) : ?>
                                                        <div class="ready-to-use">
                                                            <i class="fa-solid fa-circle-check"></i>
                                                            <span id="B">พร้อมใช้งาน</span>
                                                        </div>
                                                    <?php elseif ($data['amount'] <= 30 && $data['amount'] >= 1) : ?>
                                                        <div class="moderately">
                                                            <i class="fa-solid fa-circle-exclamation"></i>
                                                            <span id="B">ความพร้อมปานกลาง</span>
                                                        </div>
                                                    <?php elseif ($data['amount'] == 0) : ?>
                                                        <div class="not-available">
                                                            <i class="fa-solid fa-ban"></i>
                                                            <span id="B">ไม่พร้อมใช้งาน</span>
                                                        </div>
                                                    <?php endif; ?>
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
                                                    <?= htmlspecialchars($data['sci_name']) ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">ประเภท</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <?= htmlspecialchars($data['categories']) ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="details_content_1">
                                                    <span id="B">จำนวน</span>
                                                </div>
                                                <div class="details_content_2">
                                                    <?= htmlspecialchars($data['amount']) ?>
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
                                                <?php if ($data['amount'] >= 1) : ?>
                                                    <div class="button">
                                                        <button onclick="location.href='cart.php?action=add&item=<?= htmlspecialchars($data['img']) ?>'" class="use-it">
                                                            <i class="icon fa-solid fa-arrow-up"></i>
                                                            <span>ขอใช้</span>
                                                        </button>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="button">
                                                        <button class="out-of">
                                                            <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                                            <span>ไม่สามารถขอใช้ได้</span>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid_content_body">
                            <div class="content_name">
                                <span id="B">ชื่อ </span><?= htmlspecialchars($data['sci_name']) ?>
                            </div>
                            <div class="content_categories">
                                <span id="B">ประเภท </span><?= htmlspecialchars($data['categories']) ?>
                            </div>
                            <div class="content_amount">
                                <span id="B">คงเหลือ </span><?= htmlspecialchars($data['amount']) ?>
                            </div>
                        </div>
                        <div class="grid_content_footer">
                            <div class="content_btn">
                                <?php if ($data['amount'] >= 1) : ?>
                                    <a href="cart.php?action=add&item=<?= htmlspecialchars($data['img']) ?>" class="used_it">
                                        <i class="icon fa-solid fa-arrow-up"></i>
                                        <span>ขอใช้อุปกรณ์</span>
                                    </a>
                                <?php else : ?>
                                    <div class="button">
                                        <button class="out-of">
                                            <div class="icon"><i class="icon fa-solid fa-ban"></i></div>
                                            <span>ไม่สามารถขอใช้ได้</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($data['categories'] == 'อุปกรณ์' || $data['categories'] == 'เครื่องมือ') : ?>
                                    <?php if ($data['amount'] >= 1) : ?>
                                        <a href="reserve_cart.php?action=add&item=<?= htmlspecialchars($data['img']) ?>" class="reserved_it">
                                            <i class="fa-solid fa-address-book"></i>
                                            <span>จองอุปกรณ์</span>
                                        </a>
                                    <?php else : ?>
                                        <div class="not_available">
                                            <i class="fa-solid fa-check"></i>
                                            <span>อุปกรณ์ "ไม่พร้อมใช้งาน"</span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>