<?php
session_start();
require_once '../assets/database/config.php';
include_once '../assets/includes/thai_date_time.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: sign_in');
    exit;
}

// Fetch user data from database
try {
    $userID = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE userID = :userID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Fetch historical data from logs_maintenance table
try {
    $historyStmt = $conn->prepare("SELECT * FROM logs_maintenance ORDER BY start_maintenance DESC");
    $historyStmt->execute();
    $historyData = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการบำรุงรักษา</title>
    <link href="<?php echo $base_url ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/notification_popup.css">
    <link rel="stylesheet" href="<?php echo $base_url ?>/assets/css/maintenance.css">
</head>

<body>
    <header>
        <?php include_once '../assets/includes/navigator.php'; ?>
    </header>
    <div class="maintenance">
        <!-- Historical Data Section -->
        <div class="history">
            <h2>ประวัติการบำรุงรักษา</h2>
            <table>
                <thead>
                    <tr>
                        <th>วันที่เริ่มต้น</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>ชื่อวิทยาศาสตร์</th>
                        <th>หมายเหตุ</th>
                        <th>รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($historyData) > 0) : ?>
                        <?php foreach ($historyData as $row) : ?>
                            <tr>
                                <td><?php echo thai_date_time($row["start_maintenance"]); ?></td>
                                <td><?php echo thai_date_time($row["end_maintenance"]); ?></td>
                                <td><?php echo htmlspecialchars($row["sci_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["note"]); ?></td>
                                <td><?php echo htmlspecialchars($row["details_maintenance"]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="text-align: center">ไม่พบประวัติการบำรุงรักษา</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add button for generating PDF report -->
        <div class="report-button">
            <form action="generate_report_maintenance.php" method="get">
                <label for="start_date">วันที่เริ่มต้น:</label>
                <input type="date" id="start_date" name="start_date" value="">
                <label for="end_date">วันที่สิ้นสุด:</label>
                <input type="date" id="end_date" name="end_date" value="">
                <button type="submit" class="generate-report-btn">สร้างรายงาน PDF</button>
            </form>
        </div>
    </div>
</body>

</html>
