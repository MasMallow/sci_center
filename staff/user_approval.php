<?php
session_start();
require_once 'assets/database/dbConfig.php';
include_once 'includes/thai_date_time.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// ดึงข้อมูลผู้ใช้หากเข้าสู่ระบบ
if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['user_login'] ?? $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE status = 'wait_approved' ");
    $stmt->execute();
    $num = $stmt->rowCount();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approval_user'])) {
        $user_id = $_POST['user_id'];
        $staff_id = $_SESSION['staff_login'];

        $user_query = $conn->prepare("SELECT pre, firstname, lastname FROM users_db WHERE user_ID = :staff_id");
        $user_query->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_query->execute();
        $staff_name = $user_query->fetch(PDO::FETCH_ASSOC);
        $approver = $staff_name['pre'] . $staff_name['firstname'] . ' ' . $staff_name['lastname'];

        $status = 'approved';
        date_default_timezone_set('Asia/Bangkok');
        $approvalDateTime = date('Y-m-d H:i:s');

        $update_status_user = $conn->prepare("UPDATE users SET status = :status, approved_by = :approved_by, approved_date = :approved_date WHERE user_ID = :user_id");
        $update_status_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $update_status_user->bindParam(':status', $status, PDO::PARAM_STR);
        $update_status_user->bindParam(':approved_by', $approver, PDO::PARAM_STR);
        $update_status_user->bindParam(':approved_date', $approvalDateTime, PDO::PARAM_STR);
        $update_status_user->execute();

        header('Location: user_approval');
        exit;
    }
} ?>

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
            <?php
            if (!empty($users)) { ?>
                <div class="user_approve_section_body">
                    <div class="user_approve_data_header">
                        <span>จำนวนบัญชีที่รออนุมัติ <span id="B">(<?php echo $num; ?>)</span> รายการ</span>
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
                                    <td><?php echo $user['pre'] . $user['firstname'] . " " . $user['lastname']; ?></td>
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
                </div>
        </section>
    <?php
            } else { ?>
        <div class="user_approve_not_found">
            <i class="fa-solid fa-user-xmark"></i>
            <span id="B">ไม่มีบัญชีที่การรออนุมัติ</span>
        </div>
    <?php
            } ?>
    </main>

</body>

</html>