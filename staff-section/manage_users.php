<?php
session_start();
require_once 'assets/database/config.php';
include_once 'assets/includes/thai_date_time.php';
$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่ และดึงข้อมูลผู้ใช้
if (isset($_SESSION['staff_login'])) {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: /sign_in');
    exit;
}

// กำหนดค่าเริ่มต้น
$searchTitle = "";
$searchValue = "";
if (isset($_GET['search'])) {
    $searchTitle = "ค้นหา \"" . htmlspecialchars($_GET['search']) . "\" | ";
    $searchValue = htmlspecialchars($_GET['search']);
}

// ฟังก์ชันในการดึงข้อมูลผู้ใช้ตามเงื่อนไข
try {
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE status = 'w_approved' ");
    $stmt->execute();
    $num = $stmt->rowCount();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// ฟังก์ชันในการดึงข้อมูลผู้ใช้ตามเงื่อนไข
function fetchUsers($conn, $status, $role, $search = null)
{
    if ($search) {
        $search = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE (userID LIKE :search OR pre LIKE :search OR firstname LIKE :search OR lastname LIKE :search) AND status = :status AND urole = :role");
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE status = :status AND urole = :role");
    }

    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ตั้งค่าตำแหน่งและสถานะตามการจัดการ
$role = 'user';
$search = isset($_GET["search"]) ? $_GET["search"] : null;
if ($request_uri === '/management_user') {
    $users_approved = fetchUsers($conn, 'approved', $role, $search);
} elseif ($request_uri === '/undisapprove_user') {
    $users_banned = fetchUsers($conn, 'n_approved', $role, $search);
}

// การจัดการคำขอ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];

    // อนุมัติผู้ใช้
    if (isset($_POST['approval_user'])) {
        $staff_id = $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
        $stmt->bindParam(':userID', $staff_id, PDO::PARAM_INT);
        $stmt->execute();
        $staff_data = $stmt->fetch(PDO::FETCH_ASSOC);

        $approver = $staff_data['pre'] . $staff_data['firstname'] . ' ' . $staff_data['lastname'];
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
                UPDATE users_db 
                SET status = 'approved', 
                approved_by = :approved_by, 
                approved_date = :approved_date
                WHERE userID = :userID");

        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $stmt->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
        $stmt->execute();

        // ตั้งค่า session และรีเฟรชหน้าเว็บ
        $_SESSION['approveSuccess'] = 'อนุมัติบัญชีผู้ใช้เรียบร้อย';
        header('Location: /manage_users');
        exit;
    }
    // ระงับผู้ใช้
    elseif (isset($_POST['ban_user'])) {
        $stmt = $conn->prepare("UPDATE users_db SET status = 'n_approved' WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();

        // ตั้งค่า session และรีเฟรชหน้าเว็บ
        $_SESSION['bannedSuccess'] = 'ระงับบัญชีผู้ใช้เรียบร้อย';
        header('Location: /management_user');
        exit;
    }
    // ลบผู้ใช้
    elseif (isset($_POST['delete_user'])) {
        $stmt = $conn->prepare("DELETE FROM users_db WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();

        // ตั้งค่า session และรีเฟรชหน้าเว็บ
        $_SESSION['delUserSuccess'] = 'ลบบัญชีผู้ใช้เรียบร้อย';
        header('Location: /undisapprove_user');
        exit;
    }
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
                        <i class="fas fa-solid fa-xmark check"></i>
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
            <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">การจัดการบัญชีผู้ใช้</span>
        </div>
        <div class="user_manage_btn_section">
            <div class="user_manage_btn">
                <a href="/manage_users" class="<?= ($request_uri == '/manage_users') ? 'active' : ''; ?> btn_user_manage_01">อมุมัติบัญชีผู้ใช้</a>
                <a href="/management_user" class="<?= ($request_uri == '/management_user' || $request_uri == '/management_user/details') ? 'active' : ''; ?> btn_user_manage_02">ตรวจสอบและแก้ไขบัญชี</a>
                <a href="/undisapprove_user" class="<?= ($request_uri == '/undisapprove_user') ? 'active' : ''; ?> btn_user_manage_03">ยกเลิกระงับบัญชี</a>
            </div>
            <!-- แบบฟอร์มการค้นหา -->
            <form class="user_manage_search" method="get">
                <input type="hidden" name="manage" value="<?= htmlspecialchars($manage); ?>">
                <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
                <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
        <?php if (in_array($request_uri, ['/manage_users', '/management_user', '/undisapprove_user'])) : ?>
            <?php
            $header = '';
            $user_list = [];

            switch ($request_uri) {
                case '/manage_users':
                    $header = 'บัญชีที่รออนุมัติ';
                    $user_list = $users;
                    break;
                case '/management_user':
                    $header = 'บัญชีที่อนุมัติแล้ว';
                    $user_list = $users_approved;
                    break;
                case '/undisapprove_user':
                    $header = 'บัญชีที่ถูกระงับ';
                    $user_list = $users_banned;
                    break;
            }
            ?>
            <?php if (!empty($user_list)) : ?>
                <div class="manage_user_table_section">
                    <div class="user_manage_data_header">
                        <span>จำนวนบัญชีทั้งหมด <span id="B"><?= count($user_list); ?></span> บัญชี</span>
                    </div>
                    <table class="user_manage_data">
                        <thead>
                            <tr>
                                <th class="UID"><span id="B">UID</span></th>
                                <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                                <th class="role"><span id="B">ตำแหน่ง</span></th>
                                <th class="agency"><span id="B">สังกัด</span></th>
                                <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                                <th class="status"><span id="B">สถานะ</span></th>
                                <th class="operation"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_list as $user) : ?>
                                <tr>
                                    <td class="UID">
                                        <a href="<?= $base_url; ?>/management_user/details?id=<?= $user['userID'] ?>">
                                            <i class="open_expand_row fa-solid fa-circle-arrow-right" onclick="toggleExpandRow(this)"></i>
                                            <?= $user['userID']; ?></a>
                                    </td>
                                    <td><?= $user['pre'] . $user['firstname'] . " " . $user['lastname']; ?></td>
                                    <td><?= $user['role']; ?></td>
                                    <td><?= $user['agency']; ?></td>
                                    <td><?= thai_date_time($user['created_at']); ?></td>
                                    <td class="<?= $user['status'] === 'approved' ? 'green_text' : 'red_text'; ?>"><?= $user['status'] === 'approved' ? 'อนุมัติ' : 'ไม่ได้รับอนุมัติ'; ?></td>
                                    <td class="operation">
                                        <form method="post">
                                            <div class="btn_user_manage_section">
                                                <input type="hidden" name="userID" value="<?= $user['userID']; ?>">
                                                <?php if ($request_uri == '/manage_users') : ?>
                                                    <button type="submit" class="approval_user" name="approval_user">
                                                        <i class="fa-regular fa-circle-check"></i>
                                                    </button>
                                                <?php elseif ($request_uri == '/manage_users/management_user') : ?>
                                                    <a href="<?php echo $base_url; ?>/manage_users/management_user/edit_user" class="edit_user">
                                                        <i class="fa-solid fa-pencil"></i>
                                                    </a>
                                                    <button class="ban_user" type="submit" name="ban_user">
                                                        <i class="fa-solid fa-user-slash"></i>
                                                    </button>
                                                    <button class="delete_user" type="submit" name="delete_user">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                <?php elseif ($request_uri == '/manage_users/undisapprove_user') : ?>
                                                    <button type="submit" class="approval_user" name="approval_user">
                                                        <i class="fa-regular fa-circle-check"></i>
                                                    </button>
                                                    <span class="delete_user" data-modal="<?= $user['userID'] ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </span>
                                                    <div class="del_notification_alert" id="<?php echo htmlspecialchars($user['userID']); ?>">
                                                        <div class="del_notification_content">
                                                            <div class="del_notification_popup">
                                                                <div class="del_notification_sec01">
                                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                                    <span id="B">แจ้งเตือนการลบข้อมูล</span>
                                                                </div>
                                                                <div class="del_notification_sec02">
                                                                    <button class="delete_user" type="submit" name="delete_user">
                                                                        <i class="fa-solid fa-trash-can"></i></button>
                                                                    <div class="cancel_del" id="closeDetails">
                                                                        <span id="B">ปิดหน้าต่าง</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <tr style="display: none;">
                                    <td colspan="7">
                                        <div class="expandable_row">
                                            <div>
                                                <span id="B">เบอร์โทรศัพท์</span> <?= format_phone_number($user['phone_number']); ?>
                                            </div>
                                            <div>
                                                <span id="B">อีเมล</span> <?= $user['email']; ?>
                                            </div>
                                            <div>
                                                <span id="B">ประเภทผู้ใช้</span> <?= $user['urole'] === 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                </div>
            <?php else : ?>
                <div class="user_manage_not_found">
                    <i class="fa-solid fa-user-xmark"></i>
                    <span id="B">ไม่มีพบบัญชีผู้ใช้ในระบบ</span>
                </div>
            <?php endif; ?>
        <?php elseif ($request_uri == '/management_user/details') : ?>
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
                                    <div class="list_name">
                                        <a href="<?= $base_url; ?>/management_user/details?id=<?= $Data['userID'] ?>">
                                            <?= $Data['userID'] ?>
                                            <?= htmlspecialchars($Data['pre'], ENT_QUOTES, 'UTF-8') . htmlspecialchars($Data['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Data['lastname'], ENT_QUOTES, 'UTF-8'); ?></a>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= thai_date_time_2(htmlspecialchars($Data['created_at'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="approver">
                                        ผู้อนุมัติ
                                        <?= htmlspecialchars($Data['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= format_phone_number(htmlspecialchars($Data['phone_number'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['role'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['agency'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['approved_by'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reservation_date">
                                        ขอใช้
                                        <?= htmlspecialchars($Data['approved_date'], ENT_QUOTES, 'UTF-8') ?>
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
    <!-- JavaScript -->
    <script src="assets/js/ajax.js"></script>
</body>

</html>