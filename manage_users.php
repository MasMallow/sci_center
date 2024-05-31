<?php
session_start();
require_once 'assets/database/connect.php';
include 'includes/thai_date_time.php';

if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}
// ตรวจสอบค่าจาก URL และกำหนดค่าเริ่มต้น
$manage = isset($_GET['manage']) ? $_GET['manage'] : 'edit_manage';

// กำหนดค่าเริ่มต้นของคำค้นหา
$searchTitle = "";
$searchValue = "";
if (isset($_GET['search'])) {
    $searchTitle = "ค้นหา \"" . htmlspecialchars($_GET['search']) . "\" | ";
    $searchValue = htmlspecialchars($_GET['search']);
}

// Function to fetch users based on conditions
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

// Define role
$role = 'user';

if ($manage === 'edit_manage' || $manage === 'manage_user') {
    $status = 'approved';
    $search = isset($_GET["search"]) ? $_GET["search"] : null;
    $users_approved = fetchUsers($conn, $status, $role, $search);
} elseif ($manage === 'undisapprove_user') {
    $status = 'not_approved';
    $search = isset($_GET["search"]) ? $_GET["search"] : null;
    $users_banned = fetchUsers($conn, $status, $role, $search);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    if (isset($_POST['approval_user'])) {
        $staff_id = $_SESSION['staff_login'];

        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $staff_id, PDO::PARAM_INT);
        $stmt->execute();
        $staff_data = $stmt->fetch(PDO::FETCH_ASSOC);

        $approver = $staff_data['pre'] . $staff_data['surname'] . ' ' . $staff_data['lastname'];
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        // Update user status
        $status = 'approved';
        $query = "UPDATE users SET status = :status, approved_by = :approved_by, approved_date = :approved_date WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $stmt->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
        $stmt->execute();
    } elseif (isset($_POST['ban_user']) || isset($_POST['disapprove_user'])) {
        // Update user status
        $status = 'not_approved';
        $query = "UPDATE users SET status = :status WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
    } elseif (isset($_POST['delete_user'])) {
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

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
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/manage_users.css">
</head>

<body>
    <header>
        <?php
        include 'includes/header.php';
        ?>
    </header>
    <div class="user_manage_header_section">
        <div class="header_u_manage_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">การจัดการบัญชีผู้ใช้</span>
        </div>
    </div>
    <div class="user_manage_section">
        <form class="user_manage_btn" method="get">
            <button type="submit" class="<?= ($manage === 'edit_manage') ? 'active' : ''; ?> btn_maintenance_01" name="manage" value="edit_manage">ตรวจสอบและแก้ไขบัญชี</button>
            <button type="submit" class="<?= ($manage === 'manage_user') ? 'active' : ''; ?> btn_maintenance_02" name="manage" value="manage_user">ระงับ และลบบัญชี</button>
            <button type="submit" class="<?= ($manage === 'undisapprove_user') ? 'active' : ''; ?> btn_maintenance_02" name="manage" value="undisapprove_user">ยกเลิกระงับบัญชี</button>
        </form>
        <form class="user_manage_search" method="get">
            <input type="hidden" name="action" value="<?php echo htmlspecialchars($manage); ?>">
            <input class="search" type="search" name="search" value="<?php echo htmlspecialchars($searchValue); ?>" placeholder="ค้นหา">
            <button class="search" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <?php
    if ($manage == 'edit_manage') {
    ?>
        <div class="manage_user">
            <div class="user_approve_data_header">
                <span>จำนวนบัญชีทั้งหมด <span id="B"><?= count($users_approved); ?></span> บัญชี</span>
            </div>
            <table class="user_approve_data">
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
                    <?php foreach ($users_approved as $approved_user) : ?>
                        <tr>
                            <td class="UID"><?= $approved_user['user_id']; ?></td>
                            <td><?= $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                            <td><?= $approved_user['role']; ?></td>
                            <td><?= $approved_user['agency']; ?></td>
                            <td><?= format_phone_number($approved_user['phone_number']); ?></td>
                            <td><?= thai_date_time($approved_user['created_at']); ?></td>
                            <td>
                                <?= $approved_user['urole'] == 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                            </td>
                            <td>
                                <?= $approved_user['status'] == 'approved' ? 'ได้รับการอนุมัติ' : 'ระงับบัญชี'; ?>
                            </td>
                            <td>
                                <span class="maintenance_button" id="B">แก้ไขบัญชี</span>
                                <div class="choose_categories_popup">
                                    <div class="choose_categories">
                                        <div class="choose_categories_header">
                                            <span id="B">กรอกข้อมูลการบำรุงรักษา</span>
                                            <div class="modalClose" id="closeDetails">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="maintenace_popup">
                                            <div class="edit_profile_body">
                                                <form action="process/update_profile.php" method="post">
                                                    <div class="col_edit">
                                                        <div class="input_edit">
                                                            <span>USER ID</span>
                                                            <div class="show_password">
                                                                <input type="text" name="password" value="<?= $approved_user['user_id']; ?> " disabled>
                                                            </div>
                                                        </div>
                                                        <div class="input_edit">
                                                            <span>Status Account</span>
                                                            <div class="show_password">
                                                                <input type="text" name="confirm_password" value="<?= $approved_user['status'] == 'approved' ? 'ได้รับการอนุมัติ' : 'ระงับบัญชี'; ?> " disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col_edit">
                                                        <div class="input_edit">
                                                            <span>คำนำหน้า</span>
                                                            <select name="pre">
                                                                <?php
                                                                $prefixes = ['นาย', 'นาง', 'นางสาว', 'อ.', 'ผศ.ดร.'];
                                                                foreach ($prefixes as $prefix) {
                                                                    $selected = ($userData['pre'] == $prefix) ? "selected" : "";
                                                                    echo "<option value='$prefix' $selected>$prefix</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="input_edit">
                                                            <span>ชื่อ</span>
                                                            <input type="text" name="surname" value="<?= $userData['surname']; ?>">
                                                        </div>
                                                        <div class="input_edit">
                                                            <span>นามสกุล</span>
                                                            <input type="text" name="lastname" value="<?= $userData['lastname']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col_edit">
                                                        <div class="input_edit">
                                                            <span>ตำแหน่ง</span>
                                                            <select name="role">
                                                                <?php
                                                                $rolefixes = ['อาจารย์', 'บุคลากร', 'เจ้าหน้าที่'];
                                                                foreach ($rolefixes as $rolefix) {
                                                                    $selected = ($userData['role'] == $rolefix) ? "selected" : "";
                                                                    echo "<option value='$rolefix' $selected>$rolefix</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="input_edit">
                                                            <span>สังกัด</span>
                                                            <select name="agency">
                                                                <?php
                                                                $sql = "SELECT DISTINCT agency FROM users";
                                                                $stmt = $conn->query($sql);
                                                                while ($row = $stmt->fetch()) {
                                                                    $agencyfix = $row['agency'];
                                                                    $selected = ($userData['agency'] == $agencyfix) ? "selected" : "";
                                                                    echo "<option value='$agencyfix' $selected>$agencyfix</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="input_edit">
                                                            <span>เบอร์โทรศัพท์</span>
                                                            <input type="text" name="phone_number" value="<?= $userData['phone_number']; ?>"><br><br>
                                                        </div>
                                                    </div>
                                                    <div class="edit_profile_footer">
                                                        <button type="submit" class="submit">ยืนยัน</button>
                                                        <a href="../" class="cancel">ยกเลิก</a>
                                                    </div>
                                                </form>
                                            </div>
                                            <button type="submit" class="confirm_maintenance" name="confirm"><span>ยืนยัน</span></button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
    } elseif ($manage == 'manage_user') {
    ?>
        <div class="manage_user">
            <div class="user_approve_data_header">
                <span>จำนวนบัญชีทั้งหมด <span id="B"><?= count($users_approved); ?></span> บัญชี</span>
            </div>
            <table class="user_approve_data">
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
                    <?php foreach ($users_approved as $approved_user) : ?>
                        <tr>
                            <td class="UID"><?= $approved_user['user_id']; ?></td>
                            <td><?= $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                            <td><?= $approved_user['role']; ?></td>
                            <td><?= $approved_user['agency']; ?></td>
                            <td><?= format_phone_number($approved_user['phone_number']); ?></td>
                            <td><?= thai_date_time($approved_user['created_at']); ?></td>
                            <td>
                                <?= $approved_user['urole'] == 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                            </td>
                            <td>
                                <?= $approved_user['status'] == 'approved' ? 'ได้รับการอนุมัติ' : 'ระงับบัญชี'; ?>
                            </td>
                            <td>
                                <form method="POST" action="manage_users">
                                    <input type="hidden" name="user_id" value="<?= $approved_user['user_id']; ?>">
                                    <button type="submit" class="disapprove_user" name="disapprove_user">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                        <span>ระงับบัญชี</span>
                                    </button>
                                </form>
                                <form method="POST" action="manage_users" onsubmit="return confirmDelete('<?= $approved_user['user_id']; ?>')">
                                    <input type="hidden" name="user_id" value="<?= $approved_user['user_id']; ?>">
                                    <button type="submit" class="disapprove_user" name="delete_user">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                        <span>ลบบัญชี</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <script>
                function confirmDelete(userId) {
                    return confirm("Are you sure you want to delete user with ID " + userId + "?");
                }
            </script>
        </div>
    <?php
    } elseif ($manage == 'undisapprove_user') {
    ?>
        <div class="manage_user">
            <div class="user_approve_data_header">
                <span>จำนวนบัญชีที่ถูกแบน <span id="B"><?= count($users_banned); ?></span> รายการ</span>
            </div>
            <table class="user_approve_data">
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
                    <?php foreach ($users_banned as $banned_user) : ?>
                        <tr>
                            <td class="UID"><?= $banned_user['user_id']; ?></td>
                            <td><?= $banned_user['pre'] . $banned_user['surname'] . " " . $banned_user['lastname']; ?></td>
                            <td><?= $banned_user['role']; ?></td>
                            <td><?= $banned_user['agency']; ?></td>
                            <td><?= format_phone_number($banned_user['phone_number']); ?></td>
                            <td><?= thai_date_time($banned_user['created_at']); ?></td>
                            <td>
                                <?= $banned_user['urole'] == 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                            </td>
                            <td>
                                <?= $banned_user['status'] == 'not_approved' ? 'ระงับบัญชี' : ''; ?>
                            </td>
                            <td>
                                <form method="POST" action="manage_users">
                                    <input type="hidden" name="user_id" value="<?= $banned_user['user_id']; ?>">
                                    <button type="submit" class="approval_user" name="approval_user">
                                        <i class="fa-regular fa-circle-check"></i>
                                        <span>ยกเลิกระงับบัญชี</span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <!-- Remove this empty cell if not needed -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
    }
    ?>
    <script src="assets/js/maintenance.js"></script>
</body>

</html>