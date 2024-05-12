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
    <div class="content_area_grid">
        <?php
        if (empty($result)) { ?>
            <div class="grid_content_not_found">ไม่พบข้อมูล</div>
            <?php
        } else {
            foreach ($result as $data) {
            ?>
                <div class="grid_content">
                    <div class="grid_content_header">
                        <div class="content_img">
                            <img src="assets/uploads/<?php echo $data['img']; ?>">
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
                            <span id="B">คงเหลือ </span><?php echo $data['amount']; ?>
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
        <?php
            }
        }
        ?>
    </div>

</div>