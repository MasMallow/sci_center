<?php
session_start();
require_once 'assets/database/connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// Function to get user data
function getUserData($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get users by status and role
function getUsersByStatusAndRole($conn, $status, $role)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE status = :status AND urole = :role");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update user status
function updateUserStatus($conn, $user_id, $status, $approver = null, $approvalDateTime = null)
{
    $query = "UPDATE users SET status = :status";
    if ($approver !== null && $approvalDateTime !== null) {
        $query .= ", approved_by = :approved_by, approved_date = :approved_date";
    }
    $query .= " WHERE user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    if ($approver !== null && $approvalDateTime !== null) {
        $stmt->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $stmt->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
    }
    $stmt->execute();
}

// Function to delete user
function deleteUser($conn, $user_id)
{
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    if (isset($_POST['approval_user'])) {
        $staff_id = $_SESSION['staff_login'];
        $staff_data = getUserData($conn, $staff_id);
        $approver = $staff_data['pre'] . $staff_data['surname'] . ' ' . $staff_data['lastname'];
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');
        updateUserStatus($conn, $user_id, 'approved', $approver, $approvalDateTime);
    } elseif (isset($_POST['ban_user']) || isset($_POST['disapprove_user'])) {
        updateUserStatus($conn, $user_id, 'not_approved');
    } elseif (isset($_POST['delete_user'])) {
        deleteUser($conn, $user_id);
    }
    header('Location: user_approval');
    exit;
}

// Fetch all users for different statuses
$users_waiting_approval = getUsersByStatusAndRole($conn, 'wait_approved', 'user');
$users_banned = getUsersByStatusAndRole($conn, 'not_approved', 'user');
$users_approved = getUsersByStatusAndRole($conn, 'approved', 'user');
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
    <link rel="stylesheet" href="assets/css/user_approval.css">
</head>

<body>

    <h1>Manage Users</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>ชื่อ</th>
            <th>นามสกุล</th>
            <th>ตำแหน่ง</th>
        </tr>
        <?php foreach ($users_waiting_approval as $user) : ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo $user['surname']; ?></td>
                <td><?php echo $user['lastname']; ?></td>
                <td><?php echo $user['role']; ?></td>
                <td>
                    <a href="admin_edit_user.php?user_id=<?php echo $user['user_id']; ?>">แก้ไข</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="user_approve_data_header">
        <span>จำนวนบัญชีที่ถูกแบน <span id="B"><?php echo count($users_banned); ?></span> รายการ</span>
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
                    <td class="UID"><?php echo $banned_user['user_id']; ?></td>
                    <td><?php echo $banned_user['pre'] . $banned_user['surname'] . " " . $banned_user['lastname']; ?></td>
                    <td><?php echo $banned_user['role']; ?></td>
                    <td><?php echo $banned_user['agency']; ?></td>
                    <td><?php echo format_phone_number($banned_user['phone_number']); ?></td>
                    <td><?php echo thai_date_time($banned_user['created_at']); ?></td>
                    <td>
                        <?php echo $banned_user['urole'] == 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                    </td>
                    <td>
                        <?php echo $banned_user['status'] == 'banned' ? 'ถูกแบน' : ''; ?>
                    </td>
                    <td>
                        <form method="POST" action="user_approval">
                            <input type="hidden" name="user_id" value="<?php echo $banned_user['user_id']; ?>">
                            <button type="submit" class="approval_user" name="approval_user">
                                <i class="fa-regular fa-circle-check"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="user_approval" onsubmit="return confirmDelete('<?php echo $banned_user['user_id']; ?>')">
                            <input type="hidden" name="user_id" value="<?php echo $banned_user['user_id']; ?>">
                            <button type="submit" class="delete_user" name="delete_user">
                                <i class="fa-regular fa-circle-xmark"></i>
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

    <div class="user_approve_data_header">
        <span>จำนวนบัญชีที่ได้รับการอนุมัติ <span id="B"><?php echo count($users_approved); ?></span> รายการ</span>
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
                    <td class="UID"><?php echo $approved_user['user_id']; ?></td>
                    <td><?php echo $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                    <td><?php echo $approved_user['role']; ?></td>
                    <td><?php echo $approved_user['agency']; ?></td>
                    <td><?php echo format_phone_number($approved_user['phone_number']); ?></td>
                    <td><?php echo thai_date_time($approved_user['created_at']); ?></td>
                    <td>
                        <?php echo $approved_user['urole'] == 'user' ? 'ผู้ใช้งานทั่วไป' : 'เจ้าหน้าที่'; ?>
                    </td>
                    <td>
                        <?php echo $approved_user['status'] == 'approved' ? 'ได้รับการอนุมัติ' : ''; ?>
                    </td>
                    <td>
                        <form method="POST" action="user_approval">
                            <input type="hidden" name="user_id" value="<?php echo $approved_user['user_id']; ?>">
                            <button type="submit" class="disapprove_user" name="disapprove_user">
                                <i class="fa-regular fa-circle-xmark"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>