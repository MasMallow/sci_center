<?php
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
        header('Location: /management_user');
        exit;
    }
}
