<?php
session_start();
require_once 'assets/database/dbConfig.php';
include 'includes/thai_date_time.php';

// กำหนดค่าเริ่มต้น
$manage = isset($_GET['manage']) ? $_GET['manage'] : 'approval_user';
$searchTitle = "";
$searchValue = "";
if (isset($_GET['search'])) {
    $searchTitle = "ค้นหา \"" . htmlspecialchars($_GET['search']) . "\" | ";
    $searchValue = htmlspecialchars($_GET['search']);
}

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่ และดึงข้อมูลผู้ใช้
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ฟังก์ชันในการดึงข้อมูลผู้ใช้ตามเงื่อนไข
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved' ");
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
        $stmt = $conn->prepare("SELECT * FROM users WHERE (user_id LIKE :search OR pre LIKE :search OR surname LIKE :search OR lastname LIKE :search) AND status = :status AND urole = :role");
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE status = :status AND urole = :role");
    }

    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ตั้งค่าตำแหน่งและสถานะตามการจัดการ
$role = 'user';
$status = $manage === 'undisapprove_user' ? 'not_approved' : 'approved';
$search = isset($_GET["search"]) ? $_GET["search"] : null;

if ($manage === 'edit_manage' || $manage === 'manage_user') {
    $users_approved = fetchUsers($conn, 'approved', $role, $search);
} elseif ($manage === 'undisapprove_user') {
    $users_banned = fetchUsers($conn, 'not_approved', $role, $search);
}

// การจัดการคำขอ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // อนุมัติผู้ใช้
    if (isset($_POST['approval_user'])) {
        $staff_id = $_SESSION['staff_login'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $staff_id, PDO::PARAM_INT);
        $stmt->execute();
        $staff_data = $stmt->fetch(PDO::FETCH_ASSOC);

        $approver = $staff_data['pre'] . $staff_data['surname'] . ' ' . $staff_data['lastname'];
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE users SET status = 'approved', approved_by = :approved_by, approved_date = :approved_date WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $stmt->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
        $stmt->execute();

        // รีเฟรชหน้าเว็บ
        header('Location: manage_users?manage=undisapprove_user');
        exit;
    }
    // ระงับผู้ใช้
    elseif (isset($_POST['ban_user']) || isset($_POST['disapprove_user'])) {
        $stmt = $conn->prepare("UPDATE users SET status = 'not_approved' WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    // ลบผู้ใช้
    elseif (isset($_POST['delete_user'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // รีเฟรชหน้าเว็บ
    header('Location: manage_users');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การจัดการบัญชีผู้ใช้</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/manage_users.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="user_manage_header_section">
        <div class="header_u_manage_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">การจัดการบัญชีผู้ใช้</span>
        </div>
    </div>
    <div class="user_manage_section">
        <form class="user_manage_btn" method="get">
            <!-- ปุ่มสำหรับการจัดการต่างๆ -->
            <button type="submit" class="<?= ($manage === 'approval_user') ? 'active' : ''; ?> btn_user_manage_01" name="manage" value="approval_user">อมุมัติบัญชีผู้ใช้</button>
            <button type="submit" class="<?= ($manage === 'edit_manage') ? 'active' : ''; ?> btn_user_manage_02" name="manage" value="edit_manage">ตรวจสอบและแก้ไขบัญชี</button>
            <button type="submit" class="<?= ($manage === 'manage_user') ? 'active' : ''; ?> btn_user_manage_02" name="manage" value="manage_user">ระงับ และลบบัญชี</button>
            <button type="submit" class="<?= ($manage === 'undisapprove_user') ? 'active' : ''; ?> btn_user_manage_03" name="manage" value="undisapprove_user">ยกเลิกระงับบัญชี</button>
        </form>
        <!-- แบบฟอร์มการค้นหา -->
        <form class="user_manage_search" method="get">
            <input type="hidden" name="manage" value="<?= htmlspecialchars($manage); ?>">
            <input class="search" type="search" name="search" value="<?= htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
            <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <?php if ($manage === 'approval_user') : ?>
        <?php if (!empty($users)) : ?>
            <div class="manage_user">
                <div class="user_manage_data_header">
                    <span>จำนวนบัญชีทั้งหมด <span id="B">(<?= count($users); ?>)</span> บัญชี</span>
                </div>
                <table class="user_manage_data">
                    <thead>
                        <tr>
                            <th class="UID"><span id="B">UID</span></th>
                            <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                            <th class="role"><span id="B">ตำแหน่ง</span></th>
                            <th class="agency"><span id="B">สังกัด</span></th>
                            <th class="phone_number"><span id="B">เบอร์โทรศัพท์</span></th>
                            <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                            <th class="urole"><span id="B">ประเภท</span></th>
                            <th class="status"><span id="B">สถานะ</span></th>
                            <th class="operation"></th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td class="UID"><?php echo $user['user_id']; ?></td>
                                    <td><?php echo $user['pre'] . $user['surname'] . " " . $user['lastname']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><?php echo $user['agency']; ?></td>
                                    <td><?php echo format_phone_number($user['phone_number']); ?></td>
                                    <td><?php echo thai_date_time($user['created_at']); ?></td>
                                    <td>
                                        <?php
                                        if ($user['urole'] == 'user') {
                                            echo 'ผู้ใช้งานทั่วไป';
                                        } elseif ($user['urole'] == 'staff') {
                                            echo 'เจ้าหน้าที่';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($user['status'] == 'wait_approved') {
                                            echo 'รอการอนุมัติ';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="user_approval">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <div class="btn_appr_section">
                                                <button type="submit" class="approval_user" name="approval_user">
                                                    <i class="fa-regular fa-circle-check"></i>
                                                </button>
                                                <button type="submit" class="ban_user" name="ban_user">
                                                    <i class="fa-regular fa-circle-xmark"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    <tbody>
                        <!-- แสดงข้อมูลผู้ใช้ที่อนุมัติแล้ว -->
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td class="UID"><?= $user['user_id']; ?></td>
                                <td><?= $user['pre'] . $user['surname'] . " " . $user['lastname']; ?></td>
                                <td><?= $user['role']; ?></td>
                                <td><?= $user['agency']; ?></td>
                                <td><?= format_phone_number($user['phone_number']); ?></td>
                                <td><?= thai_date_time($user['created_at']); ?></td>
                                <td><?= $user['urole'] === 'user' ? 'ผู้ใช้งานทั่วไป' : 'อื่น ๆ'; ?></td>
                                <td class="<?= $user['status'] === 'approved' ? 'green_text' : 'red_text'; ?>"><?= $user['status'] === 'approved' ? 'อนุมัติแล้ว' : 'ไม่ได้รับอนุมัติ'; ?></td>
                                <td class="operation">
                                    <!-- ฟอร์มสำหรับการแก้ไข, ระงับ และลบผู้ใช้ -->
                                    <form method="post">
                                        <div class="btn_user_manage_section">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id']; ?>">
                                            <button class="edit_user" type="submit" name="edit_user"><i class="fa-solid fa-pencil"></i></button>
                                            <button class="ban_user" type="submit" name="ban_user"><i class="fa-solid fa-user-slash"></i></button>
                                            <button class="delete_user" type="submit" name="delete_user"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <!-- ข้อความแจ้งเตือนเมื่อไม่พบผู้ใช้ -->
            <div class="user_manage_not_found">
            <i class="fa-solid fa-user-xmark"></i>
            <span id="B">ไม่มีบัญชีที่การรออนุมัติ</span>
        </div>
        <?php endif; ?>
    <?php elseif ($manage === 'edit_manage') : ?>
        <?php if (!empty($users_approved)) : ?>
            <div class="manage_user">
                <div class="user_manage_data_header">
                    <span>จำนวนบัญชีทั้งหมด <span id="B">(<?= count($users_approved); ?>)</span> บัญชี</span>
                </div>
                <table class="user_manage_data">
                    <thead>
                        <tr>
                            <th class="UID"><span id="B">UID</span></th>
                            <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                            <th class="role"><span id="B">ตำแหน่ง</span></th>
                            <th class="agency"><span id="B">สังกัด</span></th>
                            <th class="phone_number"><span id="B">เบอร์โทรศัพท์</span></th>
                            <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                            <th class="urole"><span id="B">ประเภท</span></th>
                            <th class="status"><span id="B">สถานะ</span></th>
                            <th class="operation"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- แสดงข้อมูลผู้ใช้ที่อนุมัติแล้ว -->
                        <?php foreach ($users_approved as $approved_user) : ?>
                            <tr>
                                <td class="UID"><?= $approved_user['user_id']; ?></td>
                                <td><?= $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                                <td><?= $approved_user['role']; ?></td>
                                <td><?= $approved_user['agency']; ?></td>
                                <td><?= format_phone_number($approved_user['phone_number']); ?></td>
                                <td><?= thai_date_time($approved_user['created_at']); ?></td>
                                <td><?= $approved_user['urole'] === 'user' ? 'ผู้ใช้งานทั่วไป' : 'อื่น ๆ'; ?></td>
                                <td class="<?= $approved_user['status'] === 'approved' ? 'green_text' : 'red_text'; ?>"><?= $approved_user['status'] === 'approved' ? 'อนุมัติแล้ว' : 'ไม่ได้รับอนุมัติ'; ?></td>
                                <td class="operation">
                                    <!-- ฟอร์มสำหรับการแก้ไข, ระงับ และลบผู้ใช้ -->
                                    <form method="post">
                                        <div class="btn_user_manage_section">
                                            <input type="hidden" name="user_id" value="<?= $approved_user['user_id']; ?>">
                                            <button class="edit_user" type="submit" name="edit_user"><i class="fa-solid fa-pencil"></i></button>
                                            <button class="ban_user" type="submit" name="ban_user"><i class="fa-solid fa-user-slash"></i></button>
                                            <button class="delete_user" type="submit" name="delete_user"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <!-- ข้อความแจ้งเตือนเมื่อไม่พบผู้ใช้ -->
            <div class="user_manage_not_found">
                <i class="fa-solid fa-user-xmark"></i>
                <span id="B">ไม่มีพบบัญชีผู้ใช้ในระบบ</span>
            </div>
        <?php endif; ?>
    <?php elseif ($manage === 'manage_user') : ?>
        <?php if (!empty($users_approved)) : ?>
            <div class="manage_user">
                <div class="user_manage_data_header">
                    <span>จำนวนบัญชีทั้งหมด <span id="B">(<?= count($users_approved); ?>)</span> บัญชี</span>
                </div>
                <table class="user_manage_data">
                    <thead>
                        <tr>
                            <th class="UID"><span id="B">UID</span></th>
                            <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                            <th class="role"><span id="B">ตำแหน่ง</span></th>
                            <th class="agency"><span id="B">สังกัด</span></th>
                            <th class="phone_number"><span id="B">เบอร์โทรศัพท์</span></th>
                            <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                            <th class="urole"><span id="B">ประเภท</span></th>
                            <th class="status"><span id="B">สถานะ</span></th>
                            <th class="operation"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- แสดงข้อมูลผู้ใช้ที่อนุมัติแล้ว -->
                        <?php foreach ($users_approved as $approved_user) : ?>
                            <tr>
                                <td class="UID"><?= $approved_user['user_id']; ?></td>
                                <td><?= $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                                <td><?= $approved_user['role']; ?></td>
                                <td><?= $approved_user['agency']; ?></td>
                                <td><?= format_phone_number($approved_user['phone_number']); ?></td>
                                <td><?= thai_date_time($approved_user['created_at']); ?></td>
                                <td><?= $approved_user['urole'] === 'user' ? 'ผู้ใช้งานทั่วไป' : 'อื่น ๆ'; ?></td>
                                <td class="<?= $approved_user['status'] === 'approved' ? 'green_text' : 'red_text'; ?>"><?= $approved_user['status'] === 'approved' ? 'อนุมัติแล้ว' : 'ไม่ได้รับอนุมัติ'; ?></td>
                                <td class="operation">
                                    <!-- ฟอร์มสำหรับระงับ และลบผู้ใช้ -->
                                    <form method="post">
                                        <div class="btn_user_manage_section">
                                            <input type="hidden" name="user_id" value="<?= $approved_user['user_id']; ?>">
                                            <button class="ban_user02" type="submit" name="ban_user"><i class="fa-solid fa-user-slash"></i></button>
                                            <button class="delete_user" type="submit" name="delete_user"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <!-- ข้อความแจ้งเตือนเมื่อไม่พบผู้ใช้ -->
            <div class="user_manage_not_found">
                <i class="fa-solid fa-user-xmark"></i>
                <span id="B">ไม่มีพบบัญชีผู้ใช้ในระบบ</span>
            </div>
        <?php endif; ?>
    <?php elseif ($manage === 'undisapprove_user') : ?>
        <?php if (!empty($users_banned)) : ?>
            <div class="manage_user">
                <div class="user_manage_data_header">
                    <span>จำนวนบัญชีทั้งหมด <span id="B">(<?= count($users_banned); ?>)</span> บัญชี</span>
                </div>
                <table class="user_manage_data">
                    <thead>
                        <tr>
                            <th class="UID"><span id="B">UID</span></th>
                            <th class="name"><span id="B">ชื่อ - นามสกุล</span></th>
                            <th class="role"><span id="B">ตำแหน่ง</span></th>
                            <th class="agency"><span id="B">สังกัด</span></th>
                            <th class="phone_number"><span id="B">เบอร์โทรศัพท์</span></th>
                            <th class="created_at"><span id="B">สมัครบัญชีเมื่อ</span></th>
                            <th class="urole"><span id="B">ประเภท</span></th>
                            <th class="status"><span id="B">สถานะ</span></th>
                            <th class="operation"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- แสดงข้อมูลผู้ใช้ที่ถูกระงับ -->
                        <?php foreach ($users_banned as $banned_user) : ?>
                            <tr>
                                <td class="UID"><?= $banned_user['user_id']; ?></td>
                                <td><?= $banned_user['pre'] . $banned_user['surname'] . " " . $banned_user['lastname']; ?></td>
                                <td><?= $banned_user['role']; ?></td>
                                <td><?= $banned_user['agency']; ?></td>
                                <td><?= format_phone_number($banned_user['phone_number']); ?></td>
                                <td><?= thai_date_time($banned_user['created_at']); ?></td>
                                <td><?= $banned_user['urole'] === 'user' ? 'ผู้ใช้งานทั่วไป' : 'อื่น ๆ'; ?></td>
                                <td class="<?= $banned_user['status'] === 'approved' ? 'green_text' : 'red_text'; ?>"><?= $banned_user['status'] === 'approved' ? 'อนุมัติแล้ว' : 'ไม่ได้รับอนุมัติ'; ?></td>
                                <td class="operation">
                                    <!-- ฟอร์มสำหรับอนุมัติ และลบผู้ใช้ -->
                                    <form method="post">
                                        <div class="btn_user_manage_section">
                                            <input type="hidden" name="user_id" value="<?= $banned_user['user_id']; ?>">
                                            <button class="approval_user" type="submit" name="approval_user"><i class="fa-solid fa-user-check"></i></button>
                                            <button class="delete_user" type="submit" name="delete_user"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <!-- ข้อความแจ้งเตือนเมื่อไม่พบผู้ใช้ -->
            <div class="user_manage_not_found">
                <i class="fa-solid fa-ban"></i>
                <span id="B">ไม่มีบัญชีที่ถูกระงับ</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="assets/js/ajax.js"></script>
</body>

</html>