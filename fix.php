<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ตะกร้า</title>
<!-- <link href="../dist/output.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script> -->
</head>

<body>
<?php
session_start();
include_once 'connect.php';
// Check if cart session exists, create one if not
if (!isset($_SESSION['cart'])) {
$_SESSION['cart'] = [];
}
// Handle actions like add, clear, or remove items from the cart
if (isset($_GET['action'])) {
$action = $_GET['action'];
if ($action === 'add' && isset($_GET['item'])) {
$itemToAdd = $_GET['item'];
$_SESSION['cart'][] = $itemToAdd;
header('Location: cart.php');
exit;
} elseif ($action === 'clear') {
$_SESSION['cart'] = [];
header('Location: cart.php');
exit;
} elseif ($action === 'remove' && isset($_GET['item'])) {
$itemToRemove = $_GET['item'];
$key = array_search($itemToRemove, $_SESSION['cart']);
if ($key !== false) {
unset($_SESSION['cart'][$key]);
}
header('Location: cart.php');
exit;
}
}
// Check if the user is logged in
if (!isset($_SESSION['user_login']) && !isset($_SESSION['admin_login'])) {
$_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
header('Location: sign_in.php');
exit;
}
// Fetch user data if logged in
if (isset($_SESSION['user_login'])) {
$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_SESSION['admin_login'])) {
$user_id = $_SESSION['admin_login'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!-- Display the items in the cart -->
<h1>อุปกรณ์การยืม</h1>
<?php echo $row['firstname'] ?>
<?php
if (empty($_SESSION['cart'])) {
echo "<p>ไม่มีอุปกรณ์ในตะกร้า</p>";
} else {
echo '<form method="post" action="process_return.php">';
echo '<table>';
echo '<tr><th>รูปภาพ</th><th>ชื่ออุปกรณ์</th><th>จำนวน</th></tr>';
foreach ($_SESSION['cart'] as $item) {
// Retrieve product details from the database based on the item
$query = $conn->prepare("SELECT * FROM crud WHERE file_name = :item");
$query->bindParam(':item', $item, PDO::PARAM_STR);
$query->execute();
$product = $query->fetch(PDO::FETCH_ASSOC);
$productName = $product['product_name'];
$imageURL = 'uploads/' . $product['file_name'];
if (file_exists($imageURL)) {
echo '<tr>';
echo '<td><img src="' . $imageURL . '" alt="' . $productName . '" width="100" ></td>';
echo '<td>' . $productName . '</td>';
echo '<td><input type="number" name="amount[' . $item . ']" value="1" min="1"></td>';
echo '<td><a href="cart.php?action=remove&item=' . $item . '">Remove</a></td>';
}
}
echo '</tr>';
echo '</table>';
?>
<label for="return_date">วันที่คืน: </label>
<input type="date" name="return_date" required>
<button type="submit" name="update">ยืนยัน</button>
<?php
echo '</form>';
echo '<a href="cart.php?action=clear">ล้างตะกร้า</a>';
}
?>
<a href="ajax.php">กลับหน้าหลัก</a>
</body>

</html>