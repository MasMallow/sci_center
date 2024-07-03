<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบว่าพนักงานเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// ดึงข้อมูลผู้ใช้หากเข้าสู่ระบบ
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("
        SELECT * 
        FROM users_db
        WHERE userID = :userID
    ");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ตั้งค่าการแบ่งหน้า
$limit = 10; // จำนวนรายการต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลการจองที่ยังไม่ได้รับการอนุมัติ
$stmt = $conn->prepare("
        SELECT * FROM approve_to_reserve 
        WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL
        ORDER BY created_at ASC 
        LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนข้อมูลทั้งหมดที่ยังไม่ได้รับการอนุมัติ
$totalStmt = $conn->prepare("
        SELECT COUNT(*) FROM approve_to_reserve 
        WHERE approvaldatetime IS NULL AND approver IS NULL AND situation IS NULL
");
$totalStmt->execute();
$totalData = $totalStmt->fetchColumn();
$totalPages = ceil($totalData / $limit);

$num = count($data); // นับจำนวนรายการ
$previousSn = '';
$previousFirstname = '';

// ตั้งค่าการแบ่งหน้าสำหรับการอนุมัติแล้ว
$usedLimit = 10;
$usedPage = isset($_GET['usedPage']) ? (int)$_GET['usedPage'] : 1;
$usedOffset = ($usedPage - 1) * $usedLimit;

// ดึงข้อมูลการอนุมัติการจอง
$used = $conn->prepare("SELECT * FROM approve_to_reserve ORDER BY ID DESC LIMIT :limit OFFSET :offset");
$used->bindParam(':limit', $usedLimit, PDO::PARAM_INT);
$used->bindParam(':offset', $usedOffset, PDO::PARAM_INT);
$used->execute();
$dataUsed = $used->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนข้อมูลทั้งหมดที่ได้รับการอนุมัติแล้ว
$totalUsedStmt = $conn->prepare("SELECT COUNT(*) FROM approve_to_reserve");
$totalUsedStmt->execute();
$totalUsedData = $totalUsedStmt->fetchColumn();
$totalUsedPages = ceil($totalUsedData / $usedLimit);

$usedCount = count($dataUsed); // นับจำนวนรายการ
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการขอใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/approval.css">
</head>
<body>

    <?php include('assets/includes/navigator.php') ?>
    <div class="approve_section">
        <div class="header_approve_section">
            <a href="javascript:history.back();">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
            <span id="B">อนุมัติการขอใช้</span>
        </div>
        <div class="approve_btn">
            <a href="/approve_request" class="<?= ($request_uri == '/approve_request') ? 'active' : ''; ?> btn_approve_01">อนุมัติการขอใช้</a>
            <a href="/approve_request/viewlog" class="<?= ($request_uri == '/approve_request/viewlog' || $request_uri == '/approve_request/viewlog/details') ? 'active' : ''; ?> btn_approve_02">ดูการขอใช้</a>
        </div>

    <?php if ($request_uri == '/approve_request') : ?>
        <?php if (isset($_SESSION['approve_success'])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-xmark check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2"><?php echo $_SESSION['approve_success']; ?></span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['approve_success']); ?>
        <?php endif ?>
        <div class="approve_table_section">
            <?php if (empty($data)) : ?>
                <div class="approve_not_found_section">
                    <i class="fa-solid fa-xmark"></i>
                    <span id="B">ไม่พบข้อมูลการขอใช้</span>
                </div>
            <?php else : ?>
                <table class="approve_table_data">
                    <div class="approve_table_header">
                        <span>รายการที่ขอใช้ทั้งหมด <span id="B"><?php echo $totalData; ?></span> รายการ</span>
                    </div>
                    <thead>
                        <tr>
                            <th class="s_number"><span id="B">หมายเลขรายการ</span></th>
                            <th class="name_use"><span id="B">ชื่อผู้ขอใช้งาน</span></th>
                            <th class="item_name"><span id="B">รายการที่ขอใช้งาน</span></th>
                            <th class="return"><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                            <th class="approval"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $previousSn = null;
                        foreach ($data as $row) :
                            if ($previousSn != $row['serial_number']) :
                        ?>
                                <tr>
                                    <td class="sn">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                        <?php echo $row['serial_number']; ?>
                                    </td>
                                    <td><?php echo $row['name_user']; ?></td>
                                    <td>
                                        <?php
                                        // แยกข้อมูล Item Borrowed
                                        $items = explode(',', $row['list_name']);
                                        // แสดงข้อมูลรายการที่ยืม
                                        foreach ($items as $item) {
                                            list($product_name, $quantity) = explode('(', $item);
                                            $product_name = trim($product_name);
                                            $quantity = str_replace(')', '', trim($quantity));
                                            echo $product_name . ' <span id="B"> ( ' . $quantity . ' รายการ )</span><br>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo thai_date_time($row['reservation_date']); ?></td>
                                    <td>
                                        <form class="approve_form" method="POST" action="<?php echo $base_url; ?>/staff-section/processRequest.php">
                                            <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                                            <input type="hidden" name="userID" value="<?php echo $row['userID']; ?>">
                                            <button class="confirm_approve" type="submit" name="confirm">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr style="display: none;">
                                    <td colspan="7">
                                        <div class="expandable_row">
                                            <div>
                                                <span id="B">วันเวลาที่ทำรายการ</span>
                                                <?php echo thai_date_time_2($row['created_at']); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                                $previousSn = $row['serial_number'];
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php if ($page > 1) : ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="prev"><i class="fa-solid fa-arrow-left"></i> หน้าก่อน</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages) : ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="next">หน้าถัดไป <i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php elseif ($request_uri == '/approve_request/viewlog') : ?>
            <?php if (!empty($dataUsed)) : ?>
                <div class="viewLog_request_PAGE">
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <span id="B">การขอใช้</span>
                        </div>
                        <div class="viewLog_request_body">
                            <?php foreach ($dataUsed as $Data) : ?>
                                <div class="viewLog_request_content">
                                    <div class="list_name">
                                        <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                        <a href="<?php echo $base_url; ?>/approve_request/viewlog/details?id=<?= $Data['ID'] ?>">
                                            <?php echo htmlspecialchars($Data['list_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= thai_date_time(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        ผู้อนุมัติ
                                        <?= htmlspecialchars($Data['approver'], ENT_QUOTES, 'UTF-8') ?>
                                        เมื่อ
                                        <?= thai_date_time_2(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>

        <?php elseif ($request_uri == '/approve_request/viewlog/details') : ?>

            <?php
            try {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $stmt = $conn->prepare("
            SELECT * FROM approve_to_reserve                           
            WHERE ID = :id
        ");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $detailsdataUsed = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            }
            ?>
            <?php if (!empty($detailsdataUsed)) : ?>
                <div class="viewLog_request_Details">
                    <div class="viewLog_request_MAIN">
                        <div class="viewLog_request_header">
                            <div class="path-indicator">
                                <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        </div>
                        <div class="viewLog_request_body">
                            <?php foreach ($detailsdataUsed as $Data) : ?>
                                <div class="viewLog_request_content">
                                    <div class="viewLog_request_content_1">
                                        <span id="B">ชื่อรายการ</span>
                                        <?= htmlspecialchars($Data['list_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?= htmlspecialchars($Data['serial_number'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="viewLog_request_content_2">
                                        <span id="B">ชื่อผู้ขอใช้</span>
                                        <?= htmlspecialchars($Data['name_user'], ENT_QUOTES, 'UTF-8') ?>
                                        <span id="B">ขอใช้</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['reservation_date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="viewLog_request_content_3">
                                        <span id="B">สิ้นสุด</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['end_date'], ENT_QUOTES, 'UTF-8')) ?>
                                        <span id="B">วันที่คืน</span>
                                        <?php if ($Data['date_return'] === NULL) : ?>
                                            --
                                        <?php else : ?>
                                            <?= thai_date_time_2(htmlspecialchars($Data['date_return'], ENT_QUOTES, 'UTF-8')); ?>
                                        <?php endif ?>
                                    </div>
                                    <div class="viewLog_request_content_4">
                                        <span id="B">ผู้อนุมัติ</span>
                                        <?= htmlspecialchars($Data['approver'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= thai_date_time_2(htmlspecialchars($Data['approvaldatetime'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบข้อมูล</span>
                </div>
            <?php endif; ?>

    <?php endif; ?>
</div>

<script>
    function toggleExpandRow(element) {
        var row = element.closest('tr').nextElementSibling;
        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            element.classList.remove('fa-circle-arrow-right');
            element.classList.add('fa-circle-arrow-down');
        } else {
            row.style.display = 'none';
            element.classList.remove('fa-circle-arrow-down');
            element.classList.add('fa-circle-arrow-right');
        }
    }
</script>
<script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>
</html>
