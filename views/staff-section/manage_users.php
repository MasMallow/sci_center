<?php
session_start();
require_once 'assets/config/config.php';
require_once 'assets/config/Database.php';
include_once 'assets/includes/thai_date_time.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($conn)) {
    die("Database connection failed");
}

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่ และดึงข้อมูลผู้ใช้
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    try {
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

if ($request_uri === '/manage_users') {
    // กำหนดค่าเริ่มต้นสำหรับการค้นหา
    $searchTitle = "";
    $searchValue = "";
    if (isset($_GET['search'])) {
        $searchValue = htmlspecialchars($_GET['search']);
        $searchTitle = "ค้นหา \"" . $searchValue . "\" | ";
        $search = "%" . $searchValue . "%";
    } else {
        $search = null;
    }

    // กำหนดค่า status เป็น 'w_approved'
    $status = 'w_approved';

    // เริ่มต้น SQL statement
    $sql = "SELECT * FROM users_db WHERE status = :status";

    // ถ้ามีการส่งค่าค้นหามา ให้เพิ่มเงื่อนไขการค้นหาใน SQL
    if ($search) {
        $sql .= " AND (userID LIKE :search OR pre LIKE :search OR firstname LIKE :search OR lastname LIKE :search)";
    }

    try {
        // เตรียม statement
        $stmt = $conn->prepare($sql);

        // bind พารามิเตอร์ status
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        // ถ้ามีการค้นหา ให้ bind พารามิเตอร์การค้นหา
        if ($search) {
            $stmt->bindParam(':search', $search, PDO::PARAM_STR);
        }

        // ดำเนินการ statement
        $stmt->execute();

        // ดึงผลลัพธ์เก็บในรูปแบบ array
        $fetchAllUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}

if ($request_uri === '/management_user') {
    // กำหนดค่าเริ่มต้นสำหรับการค้นหา
    $searchTitle = "";
    $searchValue = "";
    if (isset($_GET['search'])) {
        $searchValue = htmlspecialchars($_GET['search']);
        $searchTitle = "ค้นหา \"" . $searchValue . "\" | ";
        $searchQuery = "%" . $searchValue . "%";
    } else {
        $searchQuery = null;
    }

    // กำหนดค่าเริ่มต้นสำหรับการแบ่งหน้า
    $results_per_page = 2; // เปลี่ยนค่าตามความต้องการ
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $results_per_page;

    // สร้างคำสั่ง SQL สำหรับการดึงข้อมูล
    $query = "SELECT * FROM users_db WHERE status != :excluded_status";

    // ถ้ามีการส่งค่าค้นหามา ให้เพิ่มเงื่อนไขการค้นหาใน SQL
    if ($searchQuery) {
        $query .= " AND (userID LIKE :search OR pre LIKE :search OR firstname LIKE :search OR lastname LIKE :search)";
    }

    $query .= " ORDER BY userID ASC LIMIT :offset, :results_per_page";

    try {
        // เตรียม statement
        $stmt = $conn->prepare($query);

        // bind พารามิเตอร์ status
        $excluded_status = 'w_approved'; // สถานะที่ต้องการยกเว้น
        $stmt->bindParam(':excluded_status', $excluded_status, PDO::PARAM_STR);

        // ถ้ามีการค้นหา ให้ bind พารามิเตอร์การค้นหา
        if ($searchQuery) {
            $stmt->bindParam(':search', $searchQuery, PDO::PARAM_STR);
        }

        // bind พารามิเตอร์ limit
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':results_per_page', $results_per_page, PDO::PARAM_INT);

        // ดำเนินการ statement
        $stmt->execute();

        // ดึงผลลัพธ์เก็บในรูปแบบ array
        $fetchAllUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }

    // นับจำนวนรายการทั้งหมด
    $total_records_query = "SELECT COUNT(*) AS total FROM users_db WHERE status != :excluded_status";

    // เพิ่มเงื่อนไขการค้นหา
    if ($searchQuery) {
        $total_records_query .= " AND (userID LIKE :search OR pre LIKE :search OR firstname LIKE :search OR lastname LIKE :search)";
    }

    $stmt_count = $conn->prepare($total_records_query);

    // bind พารามิเตอร์สำหรับการนับจำนวน
    $stmt_count->bindParam(':excluded_status', $excluded_status, PDO::PARAM_STR);

    if ($searchQuery) {
        $stmt_count->bindParam(':search', $searchQuery, PDO::PARAM_STR);
    }

    $stmt_count->execute();
    $total_records = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

    // คำนวณจำนวนหน้าทั้งหมด
    $total_pages = ceil($total_records / $results_per_page);

    // การแสดงผล pagination
    $pagination_display = $total_pages > 1;

    // ลบค่าผลการค้นหาใน session
    unset($_SESSION['search_results']);
    unset($_SESSION['search_value']);
}


// กระบวนการทำงาน
include_once('models/ManageUser.php');

?>

<?php
try {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
                SELECT * FROM users_db                           
                WHERE userID = :id
            ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $detailsdataUsed = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
} ?>
<?php
try {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT * FROM logs_user
            WHERE authID = :id
            ORDER BY log_Date ASC LIMIT 10
        ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $logsUSER = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การจัดการบัญชีผู้ใช้</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/breadcrumb.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/manage_users.css">
</head>

<body>
    <header>
        <?php include 'assets/includes/navigator.php'; ?>
    </header>
    <?php
    $successMessages = [
        'approveSuccess',
        'bannedSuccess',
        'delUserSuccess'
    ];
    foreach ($successMessages as $message) :
        if (isset($_SESSION[$message])) : ?>
            <div class="toast">
                <div class="toast_section">
                    <div class="toast_content">
                        <i class="fas fa-solid fa-check check"></i>
                        <div class="toast_content_message">
                            <span class="text text_2">
                                <?php
                                echo $_SESSION[$message];
                                unset($_SESSION[$message]);
                                ?>
                            </span>
                        </div>
                        <i class="fa-solid fa-xmark close"></i>
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="manage_user">
        <div class="header_user_manage_section">
            <a class="historyBACK" href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <div class="breadcrumb">
                <a href="/">หน้าหลัก</a>
                <span>&gt;</span>
                <?php
                if ($request_uri == '/manage_users') {
                    echo '<a href="/manage_users">อนุมัติบัญชีผู้ใช้</a>
                    ';
                }
                if ($request_uri == '/management_user') {
                    echo '<a href="/management_user">ตรวจสอบบัญชี</a>
                    ';
                }
                if ($request_uri === '/management_user/details') {
                    // แสดงลิงก์ไปยังหน้าการจัดการบัญชี
                    echo '<a href="/management_user">ตรวจสอบบัญชี</a><span>&gt;</span>';
                    // ตรวจสอบว่ามีข้อมูลใน $fetchAllUser หรือไม่
                    if (!empty($detailsdataUsed)) {
                        foreach ($detailsdataUsed as $user) {
                            // สร้างลิงก์ไปยังรายละเอียดของผู้ใช้แต่ละคน
                            echo '<a href="/management_user/details?id=' . htmlspecialchars($user['userID']) . '">';
                            echo htmlspecialchars($user['pre'] . $user['firstname'] . ' ' . $user['lastname']);
                            echo '</a>';
                        }
                    } else {
                        echo 'ไม่มีข้อมูลผู้ใช้';
                    }
                }
                ?>
            </div>
        </div>
        <?php if ($request_uri !== '/management_user/details') : ?>
            <div class="user_manage_btn_section">
                <div class="user_manage_btn">
                    <a href="/manage_users" class="<?= ($request_uri == '/manage_users') ? 'active' : ''; ?> btn_user_manage_01">อมุมัติบัญชีผู้ใช้</a>
                    <a href="/management_user" class="
                <?= ($request_uri == '/management_user' || $request_uri == '/management_user/details') ? 'active' : ''; ?> btn_user_manage_02">ตรวจสอบบัญชี</a>
                </div>
                <!-- แบบฟอร์มการค้นหา -->
                <form class="user_manage_search" method="get">
                    <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                    <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($request_uri == '/manage_users' || $request_uri == '/management_user') : ?>
            <?php if (!empty($fetchAllUser)) : ?>
                <div class="manage_user_table_section">
                    <div class="user_manage_data">
                        <div class="user_manage_data_header">
                            <span>จำนวนบัญชีทั้งหมด <span id="B"><?= count($fetchAllUser); ?></span> บัญชี</span>
                        </div>
                        <?php foreach ($fetchAllUser as $user) : ?>
                            <div class="user_manage_content">
                                <div class="user_manage_content_1">
                                    <a href="<?= htmlspecialchars($base_url); ?>/management_user/details?id=<?= htmlspecialchars($user['userID']); ?>">
                                        <?= htmlspecialchars($user['userID']); ?> <i class="fa-solid fa-up-right-from-square"></i>
                                    </a>
                                    <div><?= htmlspecialchars($user['pre']) . htmlspecialchars($user['firstname']) . " " . htmlspecialchars($user['lastname']); ?></div>
                                    <span>
                                        <span id="B">ประเภทบัญชี</span>
                                        <?= htmlspecialchars($user['urole']) === 'user' ? 'ผู้ใช้ปกติ' : 'เจ้าหน้าที่'; ?>
                                    </span>
                                </div>
                                <div>
                                    <span>
                                        <span id="B">สร้างบัญชีเมื่อ</span><?= htmlspecialchars(thai_date_time_2($user['created_at'])); ?>
                                    </span>
                                    <span class="<?= htmlspecialchars($user['status']) === 'w_approved' ? 'wait_approved' : (htmlspecialchars($user['status']) === 'approved' ? 'approved' : 'n_approved'); ?>">
                                        <?= htmlspecialchars($user['status']) === 'w_approved' ? 'รอการอนุมัติบัญชี' : (htmlspecialchars($user['status']) === 'approved' ? 'บัญชีผ่านการอนุมัติ' : 'บัญชีถูกระงับ'); ?>
                                    </span>
                                </div>
                                <form method="post">
                                    <div class="btn_user_manage_section">
                                        <input type="hidden" name="userID" value="<?= htmlspecialchars($user['userID']); ?>">
                                        <?php if ($request_uri === '/manage_users') : ?>
                                            <button type="submit" class="approval_user" name="approval_user" title="อนุมัติผู้ใช้">
                                                <i class="fa-regular fa-circle-check"></i>
                                            </button>
                                        <?php elseif ($request_uri === '/management_user') : ?>
                                            <?php if ($user['status'] == 'approved') : ?>
                                                <a href="<?= htmlspecialchars($base_url); ?>/edit_user?id=<?= htmlspecialchars($user['userID']); ?>" class="edit_user" title="แก้ไขผู้ใช้">
                                                    <i class="fa-solid fa-pencil"></i>
                                                    <span>แก้ไขบัญชี</span>
                                                </a>
                                                <button class="ban_user" type="submit" name="ban_user" title="ระงับบัญชี">
                                                    <i class="fa-solid fa-user-slash"></i>
                                                    <span>ระงับบัญชี</span>
                                                </button>
                                                <span class="delete_user" data-modal="<?= htmlspecialchars($user['userID']); ?>" title="ลบบัญชี">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    <span>ลบบัญชี</span>
                                                </span>
                                                <div class="deleteAccount" id="<?= htmlspecialchars($user['userID']); ?>">
                                                    <div class="deleteAccount_section">
                                                        <div class="deleteAccount_content">
                                                            <div class="deleteAccount_sec1">
                                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                                <span id="B">แจ้งเตือนการลบบัญชีผู้ใช้</span>
                                                            </div>
                                                            <div class="deleteAccount_sec2">
                                                                <button class="deleteUser" type="submit" name="delete_user">
                                                                    <span id="B">ลบบัญชี</span>
                                                                </button>
                                                                <div class="cancel_del" id="closeDetails">
                                                                    <span id="B">ปิดหน้าต่าง</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php elseif ($user['status'] == 'n_approved') : ?>
                                                <button type="submit" class="approval_user" name="approval_user" title="อนุมัติผู้ใช้">
                                                    <i class="fa-regular fa-circle-check"></i>
                                                    <span>อนุมัติผู้ใช้</span>
                                                </button>
                                                <span class="delete_user" data-modal="<?= htmlspecialchars($user['userID']); ?>" title="ลบบัญชี">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    <span>ลบบัญชี</span>
                                                </span>
                                                <div class="deleteAccount" id="<?= htmlspecialchars($user['userID']); ?>">
                                                    <div class="deleteAccount_section">
                                                        <div class="deleteAccount_content">
                                                            <div class="deleteAccount_sec1">
                                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                                <span id="B">แจ้งเตือนการลบข้อมูล</span>
                                                            </div>
                                                            <div class="deleteAccount_sec2">
                                                            <button class="deleteUser" type="submit" name="delete_user">
                                                                    <span id="B">ลบบัญชี</span>
                                                                </button>
                                                                <div class="cancel_del" id="closeDetails">
                                                                    <span id="B">ปิดหน้าต่าง</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- PAGINATION PAGE -->
                <?php if ($request_uri == '/management_user') : ?>
                    <?php if ($pagination_display) : ?>
                        <div class="pagination">
                            <?php if ($page > 1) : ?>
                                <a href="?page=1<?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&laquo;</a>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&lsaquo;</a>
                            <?php endif; ?>

                            <?php
                            $total_pages = ceil($total_records / $results_per_page);
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $page) {
                                    echo "<a class='active'>$i</a>";
                                } else {
                                    echo "<a href='?page=$i" . ($searchValue ? '&search=' . $searchValue : '') . "'>$i</a>";
                                }
                            }
                            ?>

                            <?php if ($page < $total_pages) : ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&rsaquo;</a>
                                <a href="?page=<?php echo $total_pages; ?><?php echo $searchValue ? '&search=' . $searchValue : ''; ?>">&raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else : ?>
                <div class="user_manage_not_found">
                    <i class="fa-solid fa-user-xmark"></i>
                    <span id="B">ไม่มีพบบัญชีผู้ใช้ในระบบ</span>
                </div>
            <?php endif; ?>

            <!-- --------------- USER DEATILS PAGE ----------------- -->
        <?php elseif ($request_uri == '/management_user/details') : ?>
            <?php if (!empty($detailsdataUsed)) : ?>
                <div class="viewLogUsers">
                    <div class="viewLogUsersMain">
                        <div class="viewLogUsers_header" id="B">
                            รายละเอียด
                        </div>
                        <div class="viewLogUsers_body">
                            <?php foreach ($detailsdataUsed as $Data) : ?>
                                <div class="viewLogUsers_content">
                                    <div class="list_name">
                                        <?= $Data['userID'] ?>
                                        <?= htmlspecialchars($Data['pre'], ENT_QUOTES, 'UTF-8') . htmlspecialchars($Data['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Data['lastname'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">สร้างบัญชี</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['created_at'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        <span id="B">อีเมล</span>
                                        <?= htmlspecialchars($Data['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">เบอร์โทรศัพท์</span>
                                        <?= format_phone_number(htmlspecialchars($Data['phone_number'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">ตำแหน่ง</span>
                                        <?= htmlspecialchars($Data['role'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">หน่วยงาน</span>
                                        <?= htmlspecialchars($Data['agency'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">สถานะ</span>
                                        <?php if ($userData['status'] === 'w_approved') : ?>
                                            <span class="wait_approved">รอการอนุมัติบัญชี</span>
                                        <?php elseif ($userData['status'] === 'approved') : ?>
                                            <span class="approved">บัญชีผ่านการอนุมัติ</span>
                                        <?php elseif ($userData['status'] === 'n_approved') : ?>
                                            <span class="n_approved">บัญชีถูกระงับ</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span id="B">ผู้อนุมัติ</span>
                                        <?= htmlspecialchars($Data['approved_by'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= thai_date_time_2(htmlspecialchars($Data['approved_date'], ENT_QUOTES, 'UTF-8')) ?>
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

            <!-- ---------- LOGS USER ------------ -->
            <?php if (!empty($logsUSER)) : ?>
                <div class="viewLogUsers">
                    <div class="viewLogUsersMain">
                        <div class="viewLogUsers_header" id="B">
                            การเข้าสู่ระบบ
                        </div>
                        <div class="viewLogUsers_body">
                            <?php foreach ($logsUSER as $Data) : ?>
                                <div class="viewLogUsers_content_LOG">
                                    <div class="list_name">
                                        <?= htmlspecialchars($Data['authID'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= htmlspecialchars($Data['log_Name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        <span class="label">เข้าสู่ระบบ</span>
                                        <?= thai_date_time_2(htmlspecialchars($Data['log_Date'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        <span class="label">IP</span>
                                        <?= htmlspecialchars($Data['log_IP'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="viewNotfound">
                    <i class="fa-solid fa-database"></i>
                    <span id="B">ไม่พบประวัติการเข้าสู่ระบบ</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <!-- JavaScript -->
    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/add.js"></script>
</body>

</html>