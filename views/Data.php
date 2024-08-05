<div class="content_area">
    <!-- ----------------- SEARCH SECTION ----------------- -->
    <div class="content_area_header">
        <form class="contentSearch" method="get">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page); ?>">
            <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
            <button class="search_btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <div class="content_area_nav">
            <div class="date" id="date"></div>
            <div class="time" id="time"></div>
        </div>
    </div>
    <!-- ----------------- CONTENT ------------------ -->
    <div class="content_area_all">
        <?php if (empty($results)) : ?>
            <div class="grid_content_not_found">
                <span id="B">ไม่พบข้อมูลที่ค้นหา</span>
            </div>
        <?php else : ?>
            <div class="content_area_grid">
                <?php foreach ($results as $data) : ?>
                    <div class="grid_content">
                        <div class="grid_content_header">
                            <div class="content_img">
                                <img src="<?= htmlspecialchars($base_url); ?>/assets/uploads/<?= htmlspecialchars($data['img_name']) ?>" alt="Image">
                            </div>
                        </div>
                        <div class="content_status_details">
                            <?php if ($data['categories'] !== 'วัสดุ' && $data['availability'] == 0) : ?>
                                <div class="ready-to-use">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">พร้อมใช้งาน</span>
                                </div>
                            <?php elseif ($data['categories'] !== 'วัสดุ' && $data['availability'] !== 0) : ?>
                                <div class="moderately">
                                    <i class="fa-solid fa-ban"></i>
                                    <span id="B">บำรุงรักษา</span>
                                </div>
                            <?php elseif ($data['categories'] == 'วัสดุ' && $data['amount'] <= 15) : ?>
                                <div class="not-available">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">วัสดุใกล้หมด</span>
                                </div>
                            <?php elseif ($data['categories'] == 'วัสดุ' && $data['amount'] > 15) : ?>
                                <div class="ready-to-use">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span id="B">พร้อมใช้งาน</span>
                                </div>
                            <?php endif ?>
                            <div class="content_details">
                                <a href="/details/<?= htmlspecialchars($data['ID']) ?>" class="details_btn">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                        </div>
                        <div class="grid_content_body">
                            <div class="content_name">
                                <?= htmlspecialchars($data['sci_name']) ?> (<?= htmlspecialchars($data['serial_number']) ?>)
                            </div>
                            <div class="content_categories">
                                <div>
                                    <span id="B">ประเภท : </span><?= htmlspecialchars($data['categories']) ?>
                                </div>
                                <div>
                                    <span id="B">คงเหลือ : </span><?= htmlspecialchars($data['amount']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="grid_content_footer">
                            <div class="content_btn">
                                <?php if ($data['amount'] >= 1 && $data['availability'] == 0) : ?>
                                    <a href="cart?action=add&item=<?= htmlspecialchars($data['serial_number']) ?>" class="used_it">
                                        <i class="fa-solid fa-address-book"></i>
                                        <span>ขอใช้</span>
                                    </a>
                                <?php else : ?>
                                    <div class="not_available">
                                        <i class="fa-solid fa-ban"></i>
                                        <span>ไม่พร้อมใช้งาน</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- PAGINATION PAGE -->
    <?php if ($pagination_display) : ?>
        <div class="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page=1<?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&laquo;</a>
                <a href="?page=<?= $page - 1; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&lsaquo;</a>
            <?php endif; ?>

            <?php
            $total_pages = ceil($total_records / $results_per_page);
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo "<a class='active'>$i</a>";
                } else {
                    echo "<a href='?page=$i" . ($searchValue ? '&search=' . htmlspecialchars($searchValue) : '') . "'>$i</a>";
                }
            }
            ?>

            <?php if ($page < $total_pages) : ?>
                <a href="?page=<?= $page + 1; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&rsaquo;</a>
                <a href="?page=<?= $total_pages; ?><?= $searchValue ? '&search=' . htmlspecialchars($searchValue) : ''; ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>