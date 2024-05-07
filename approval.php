<?php
    session_start();
    include_once 'assets/database/connect.php';
    if (!isset($_SESSION['admin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('Location: auth/sign_in.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE ApprovalDateTime IS NULL AND Approver IS NULL");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($data as $row): ?>
        <div class="row">
            <span class="info">ID:</span> <?php echo $row['id']; ?><br>
            <span class="info">First Name:</span> <?php echo $row['FirstName']; ?><br>
            <span class="info">Item Borrowed:</span> <?php echo $row['ItemBorrowed']; ?><br>
            <span class="info">Borrow DateTime:</span> <?php echo $row['BorrowDateTime']; ?><br>
            <span class="info">Return Date:</span> <?php echo $row['ReturnDate']; ?><br>
            <form method="POST" action="process_return.php">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="submit" name="confirm" value="ยืนยันการยืม">
            </form><br><br>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>