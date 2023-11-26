<?php
session_start();
include_once 'db.php';
if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE id =$user_id");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (isset($_SESSION['admin_login'])) {
    $user_id = $_SESSION['admin_login'];
    $stmt = $conn->query("SELECT * FROM users WHERE id =$user_id");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
$stmt = $conn->prepare("SELECT DISTINCT product_name, MAX(borrow_date) AS latest_borrow_date FROM borrow_history WHERE user_id = :user_id GROUP BY product_name");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$borrowHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการยืมสินค้าล่าสุด</title>
    <!-- Add your CSS, Bootstrap, or necessary libraries here -->
</head>
<body>
    <h1>ประวัติการยืมสินค้าล่าสุด</h1>
    <table>
        <thead>
            <tr>
                <th>สินค้า</th>
                <th>วันที่ยืมล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowHistory as $product) : ?>
                <tr>
                    <td><?php echo $product['product_name']; ?></td>
                    <td><?php echo $product['latest_borrow_date']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Add your JavaScript or other necessary sections here -->
</body>
</html>