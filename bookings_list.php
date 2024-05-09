<!-- bookings_list.php -->
<?php
session_start();
include_once 'assets/database/connect.php';

// Fetch user ID and bookings from the database
if (isset($_SESSION['user_login']) || isset($_SESSION['admin_login'])) {
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
    } elseif (isset($_SESSION['admin_login'])) {
        $user_id = $_SESSION['admin_login'];
    }

    $user_query = $conn->prepare("SELECT firstname FROM users WHERE user_id = :user_id");
    $user_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $firstname = $user['firstname']; // User's first name

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = :user_id AND reservation_date >= CURDATE()");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User's Bookings</title>
    <!-- Add necessary CSS styles here -->
</head>
<body>
<h1>รายการจอง</h1>

<?php
if (empty($bookings)) {
    echo "ไม่มีรายการจอง";
} else {
    ?>
    <form method="POST" action="cancel_booking.php">
        <table>
            <tr>
                <th>ชื่ออุปกรณ์</th>
                <th>จำนวน</th>
                <th>วันที่จะยืมของ</th>
                <th>วันที่กดจอง</th>
                <th>ยกเลิกการจอง</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
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
