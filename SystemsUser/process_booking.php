<?php
session_start();
require_once 'assets/database/config.php';
include_once 'includes/thai_date_time.php';

if (isset($_GET['item'])) {
    $action = $_GET['action'];
    $item = $_GET['item'];

    // Fetch the booking details for the specific item
    $stmt = $conn->prepare("SELECT * FROM approve_to_reserve WHERE id = :item");
    $stmt->bindParam(':item', $item, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a single row instead of all rows

    if ($data) {
        $list_name = $data['list_name'];
        $items = explode(',', $list_name);

        $Updatesituation = $conn->prepare("UPDATE approve_to_reserve SET situation = 3 WHERE id = :item");
        $Updatesituation->bindParam(':item', $item, PDO::PARAM_INT);
        $Updatesituation->execute();

        foreach ($items as $item) {
            $item_parts = explode('(', $item); // Split item name and quantity
            $product_name = trim($item_parts[0]); // Get item name and trim spaces
            $quantity = str_replace(')', '', $item_parts[1]); // Get quantity and remove parenthesis

            $stmtUpdate = $conn->prepare("UPDATE crud SET amount = amount - :quantity WHERE sci_name = :product_name");
            $stmtUpdate->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $stmtUpdate->execute();
        }
        echo 'ทำรายการเสร็จสิ้น';
        echo '<a href="home">กลับหน้าหลัก</a>';
    } else {
        echo "Booking not found.";
    }
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

</body>

</html>