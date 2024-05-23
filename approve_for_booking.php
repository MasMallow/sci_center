<?php
session_start();
include_once 'assets/database/connect.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

// Retrieve user data if logged in
if (isset($_SESSION['user_login']) || isset($_SESSION['staff_login'])) {
    $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['staff_login'];

    // Prepare SQL statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to convert date and time to Thai format
function thai_date_time($datetime)
{
    $thai_month_arr = array(
        "1" => "ม.ค.",
        "2" => "ก.พ.",
        "3" => "มี.ค.",
        "4" => "เม.ย.",
        "5" => "พ.ค.",
        "6" => "มิ.ย.",
        "7" => "ก.ค.",
        "8" => "ส.ค.",
        "9" => "ก.ย.",
        "10" => "ต.ค.",
        "11" => "พ.ย.",
        "12" => "ธ.ค."
    );

    $day = date("w", strtotime($datetime));
    $date = date("j", strtotime($datetime));
    $month = date("n", strtotime($datetime));
    $year = date("Y", strtotime($datetime)) + 543;
    $time = date("H:i น.", strtotime($datetime));

    return "วัน" . "ที่ " . $date . " " . $thai_month_arr[$month] . " พ.ศ." . $year . " <br> เวลา " . $time;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการจอง</title>
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/approval.css">
</head>

<body>
    <?php include('includes/header.php') ?>
    <div class="appr_use">
        <?php
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE approvaldatetime IS NULL AND approver IS NULL AND situation = 0 ORDER BY serial_number");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="table_section_appr_use">
            <div class="table_appr_use_header">
                <span id="B">อนุมัติการขอจอง</span>
            </div>
            <?php if (empty($data)) { ?>
                <div class="table_appr_not_found">
                    <p>ไม่มีข้อมูลการจอง</p>
                </div>
            <?php } else { ?>
                <table class="table_data_use">
                    <thead>
                        <tr>
                            <th class="s_number"><span id="B">หมายเลขรายการ</span></th>
                            <th class="name_use"><span id="B">ชื่อผู้ขอใช้งาน</span></th>
                            <th class="item_name"><span id="B">รายการที่ขอใช้งาน</span></th>
                            <th class="borrow_booking"><span id="B">วันเวลาที่ขอใช้งาน</span></th>
                            <th class="return"><span id="B">วันเวลาที่สิ้นสุดขอใช้งาน</span></th>
                            <th class="approval"><span id="B">อนุมัติ</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row) { ?>
                            <tr>
                                <td class="sn"><?php echo $row['serial_number']; ?></td>
                                <td><?php echo $row['firstname']; ?></td>
                                <td>
                                    <?php
                                    $items = explode(',', $row['product_name']);
                                    foreach ($items as $item) {
                                        $item_parts = explode('(', $item);
                                        $product_name = trim($item_parts[0]);
                                        $quantity = str_replace(')', '', $item_parts[1]);
                                        echo $product_name . ' ' . $quantity . ' ชิ้น<br>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo thai_date_time($row['created_at']); ?></td>
                                <td><?php echo thai_date_time($row['reservation_date']); ?></td>
                                <td>
                                    <form method="POST" action="process_reserve.php">
                                        <input type="hidden" name="id" value="<?php echo $row['serial_number']; ?>">
                                        <input type="hidden" name="userId" value="<?php echo $row['user_id']; ?>">
                                        <button class="submit" type="submit" name="confirm"><span>อนุมัติ</span></button>
                                        <button class="submit" type="submit" name="cancel"><span>ไม่อนุมัติ</span></button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</body>

</html>
