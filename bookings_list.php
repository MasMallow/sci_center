<?php
session_start();
include_once 'assets/database/connect.php';
if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        if ($userData['status'] == 'not_approved') {
            unset($_SESSION['user_login']);
            header('Location: auth/sign_in.php');
            exit();
        }
    }
}
if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE user_id =$user_id");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = :user_id AND reservation_date >= CURDATE()");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User's Bookings</title>
    <link href="assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
    <link rel="stylesheet" href="assets/css/bookings_list.css">
</head>

<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>
    <div class="maintenance">
        <div class="header_maintenance_section">
            <a href="../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
            <span id="B">รายการจอง</span>
        </div>
    </div>
    <?php
    if (empty($bookings)) {
        echo "ไม่มีรายการจอง";
    } else {
    ?>
        <form method="POST" action="cancel_booking">
            <table>
                <tr>
                    <th>ชื่ออุปกรณ์</th>
                    <th>จำนวน</th>
                    <th>วันที่จะยืมของ</th>
                    <th>วันที่กดจอง</th>
                    <th>ยกเลิกการจอง</th>
                </tr>
                <?php foreach ($bookings as $booking) : ?>
                    <tr>
                        <td><?php echo $booking['sci_name']; ?></td>
                        <td><?php echo $booking['quantity']; ?></td>
                        <td><?php echo $booking['reservation_date']; ?></td>
                        <td><?php echo $booking['created_at']; ?></td>
                        <td>
                            <input type="checkbox" name="booking_ids[]" value="<?php echo $booking['id']; ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                <input type="hidden" name="user_id" value="<?php echo $bookings[0]['user_id']; ?>">
            </table>
            <button type="submit">ยกเลิกการจอง</button>
        </form>
    <?php } ?>

    <!-- Add necessary JavaScript here -->
</body>

</html>