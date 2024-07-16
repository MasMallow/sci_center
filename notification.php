<main class="notification_page">
    <div class="notification_header">
        <span id="B">แจ้งเตือนการขอใช้</span>
    </div>
    <div class="notification_section">
        <?php if (!empty($data)) : ?>
            <?php foreach ($data as $row) : ?>
                <div class="notification">
                    <div class="notification_HEADER_ICON">
                        <div class="notification_title">
                            <i class="icon fas fa-bell"></i>
                            <div class="titleSN">
                                <span id="B">หมายเลขรายการ </span><?= htmlspecialchars($row['serial_number'] ?? $row['serial_number']); ?>
                            </div>
                        </div>
                        <div class="status">
                            <?php
                            $situation = $row['situation'];
                            if ($situation === null) {
                                echo '<div class="status_pending">ยังไม่ได้รับอนุมัติ</div>';
                            } elseif ($situation == 1) {
                                echo '<div class="status_approved">ได้รับอนุมัติ</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="notification_details">
                        <?php
                        $items = explode(',', $row['list_name'] ?? $row['list_name']);
                        foreach ($items as $item) {
                            $item_parts = explode('(', $item);
                            $product_name = trim($item_parts[0]);
                            $quantity = isset($item_parts[1]) ? str_replace(')', '', $item_parts[1]) : 'ไม่ระบุ';
                            echo htmlspecialchars($product_name) . ' <span id="B">( ' . htmlspecialchars($quantity) . ' รายการ )</span><br>';
                        }
                        ?>
                    </div>
                    <div class="notification_footer">
                        <div class="subtext"><span id="B">ขอใช้งาน </span><?= thai_date_time_2($row['created_at']); ?>
                            <span id="B">ถึง </span> <?= thai_date_time_2($row['reservation_date']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="notification_not_found">
                <i class="icon fa-solid fa-address-book"></i>
                <span id="B">ไม่มีแจ้งเตือนการขอใช้งาน</span>
            </div>
        <?php endif; ?>
    </div>
</main>