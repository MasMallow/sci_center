<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
session_start();
require_once 'assets/database/connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $user_id = $_POST['user_id'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];

        // Update the user's information in the database
        $stmt = $conn->prepare("UPDATE users SET firstname = :firstname, lastname = :lastname, role = :role , phone = :phone WHERE user_id = :user_id");
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'บัญชีผู้ใช้ได้รับการอัปเดตเรียบร้อยแล้ว';
            header('Location: manage_users.php');
            exit;
        } else {
            $_SESSION['error'] = 'ไม่สามารถอัปเดตบัญชีผู้ใช้ได้';
            // Handle the scenario where the update fails
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>

<body>
    <h1>Edit User</h1>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo $user['firstname']; ?>"><br>

        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo $user['lastname']; ?>"><br>

        <label for="phone">phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>"><br>

        <label for="role">Role:</label>
        <select name="role" id="role">
            <option value="" disabled>เลือกคำนำหน้า</option>
            <option value="อาจารย์" <?php if ($user['role'] === 'อาจารย์') echo 'selected'; ?>>อาจารย์</option>
            <option value="บุคลากร" <?php if ($user['role'] === 'บุคลากร') echo 'selected'; ?>>บุคลากร</option>
            <option value="ผู้บริหาร" <?php if ($user['role'] === 'ผู้บริหาร') echo 'selected'; ?>>ผู้บริหาร</option>
        </select>


        <!-- <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required><br> -->

        <button type="submit" name="update">Update</button>
    </form>
</body>

</html>