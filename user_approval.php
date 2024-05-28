<?php
session_start();
include_once 'assets/database/connect.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ดึงข้อมูลผู้ใช้หากเข้าสู่ระบบ
if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['user_login'] ?? $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval_user'])) {
        $user_id = $_POST['user_id'];
        $staff_id = $_SESSION['staff_login'];

        $user_query = $conn->prepare("SELECT pre, surname, lastname FROM users WHERE user_id = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $staff_name = $user_query->fetch(PDO::FETCH_ASSOC);
        $approver = $staff_name['pre'] . $staff_name['surname'] . ' ' . $staff_name['lastname'];

        $status = 'approved';
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        $update_status_user = $conn->prepare("UPDATE users SET status = :status, approved_by = :approved_by, approved_date = :approved_date WHERE user_id = :user_id");
        $update_status_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $update_status_user->bindParam(':status', $status, PDO::PARAM_STR);
        $update_status_user->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $update_status_user->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
        $update_status_user->execute();

        header('Location: user_approval');
        exit;
    }

    if (isset($_POST['ban_user'])) {
        $user_id = $_POST['user_id'];

        $status = 'not_approved';
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        $update_status_user = $conn->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
        $update_status_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $update_status_user->bindParam(':status', $status, PDO::PARAM_STR);
        $update_status_user->execute();

        header('Location: user_approval');
        exit;
    }

    if (isset($_POST['disapprove_user'])) {
        $user_id = $_POST['user_id'];
        $status = 'not_approved';

        $update_status_user = $conn->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
        $update_status_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $update_status_user->bindParam(':status', $status, PDO::PARAM_STR);
        $update_status_user->execute();

        header('Location: user_approval');
        exit;
    }
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        // Delete the user from the database
        $delete_user_query = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
        $delete_user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $delete_user_query->execute();

        header('Location: user_approval');
        exit;
    }
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved' AND urole = 'user'");
    $stmt->execute();
    $num = $stmt->rowCount();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_banned = $conn->prepare("SELECT * FROM users WHERE status = 'not_approved' AND urole = 'user'");
    $stmt_banned->execute();
    $num_banned = $stmt_banned->rowCount();
    $banned_users = $stmt_banned->fetchAll(PDO::FETCH_ASSOC);

    $stmt_approved = $conn->prepare("SELECT * FROM users WHERE status = 'approved' AND urole = 'user'");
    $stmt_approved->execute();
    $num_approved = $stmt_approved->rowCount();
    $approved_users = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

include_once 'includes/thai_date_time.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติผู้สร้างบัญชี</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/user_approval.css">
</head>

<body>
    <header>
        <?php include('includes/header.php') ?>
    </header>
    <main>
        <section class="user_approve_section">
            <div class="user_approve_header_section">
                <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <span id="B">อนุมัติผู้สร้างบัญชี</span>
            </div>
            <div class="user_approve_section_body">
                <div class="user_approve_data_header">
                    <span>จำนวนบัญชีที่รออนุมัติ <span id="B"><?php echo $num; ?></span> รายการ</span>
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
                </table>

                <div class="user_approve_data_header">
                    <span>จำนวนบัญชีที่ถูกแบน <span id="B"><?php echo $num_banned; ?></span> รายการ</span>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banned_users as $banned_user) : ?>
                            <tr>
                                <td class="UID"><?php echo $banned_user['user_id']; ?></td>
                                <td><?php echo $banned_user['pre'] . $banned_user['surname'] . " " . $banned_user['lastname']; ?></td>
                                <td><?php echo $banned_user['role']; ?></td>
                                <td><?php echo $banned_user['agency']; ?></td>
                                <td><?php echo format_phone_number($banned_user['phone_number']); ?></td>
                                <td><?php echo thai_date_time($banned_user['created_at']); ?></td>
                                <td>
                                    <?php
                                    if ($banned_user['urole'] == 'user') {
                                        echo 'ผู้ใช้งานทั่วไป';
                                    } elseif ($banned_user['urole'] == 'staff') {
                                        echo 'เจ้าหน้าที่';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($banned_user['status'] == 'banned') {
                                        echo 'ถูกแบน';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="user_approval">
                                        <input type="hidden" name="user_id" value="<?php echo $banned_user['user_id']; ?>">
                                        <div class="btn_appr_section">
                                            <button type="submit" class="approval_user" name="approval_user">
                                                <i class="fa-regular fa-circle-check"></i>
                                            </button>
                                        </div>
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
                    <span>จำนวนบัญชีที่ได้รับการอนุมัติ <span id="B"><?php echo $num_approved; ?></span> รายการ</span>
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
                        <?php foreach ($approved_users as $approved_user) : ?>
                            <tr>
                                <td class="UID"><?php echo $approved_user['user_id']; ?></td>
                                <td><?php echo $approved_user['pre'] . $approved_user['surname'] . " " . $approved_user['lastname']; ?></td>
                                <td><?php echo $approved_user['role']; ?></td>
                                <td><?php echo $approved_user['agency']; ?></td>
                                <td><?php echo format_phone_number($approved_user['phone_number']); ?></td>
                                <td><?php echo thai_date_time($approved_user['created_at']); ?></td>
                                <td>
                                    <?php
                                    if ($approved_user['urole'] == 'user') {
                                        echo 'ผู้ใช้งานทั่วไป';
                                    } elseif ($approved_user['urole'] == 'staff') {
                                        echo 'เจ้าหน้าที่';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($approved_user['status'] == 'approved') {
                                        echo 'ได้รับการอนุมัติ';
                                    }
                                    ?>
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

            </div>
        </section>
    </main>

</body>

</html>