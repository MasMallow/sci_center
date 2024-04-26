<?php
session_start();
include_once 'connect.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];
} elseif (isset($_SESSION['admin_login'])) {
    $user_id = $_SESSION['admin_login'];
}

$stmt = $conn->prepare("
    SELECT DISTINCT bh.product_name, MAX(bh.borrow_date) AS lauploads_borrow_date, MAX(rh.In_return_date) AS lauploads_return_date
    FROM borrow_history bh
    LEFT JOIN return_history rh ON bh.product_name = rh.product_name AND bh.user_id = rh.user_id
    WHERE bh.user_id = :user_id
    GROUP BY bh.product_name
    HAVING MAX(rh.In_return_date) < MAX(bh.borrow_date) OR MAX(rh.In_return_date) IS NULL
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$borrowHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>คืนอุปกรณ์</title>
    <!-- Add your CSS, Bootstrap, or necessary libraries here -->
</head>
<body>
    <h1>คืนอุปกรณ์</h1>
    <form method="post" action="TrueReturn.php">
        <table>
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>วันที่ยืมล่าสุด</th>
                    <th>วันที่ต้องคืน</th>
                    <th>คืนอุปกรณ์</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($borrowHistory as $product) :
                    ?>
                    <tr>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['lauploads_borrow_date']; ?></td>
                        <td><?php echo $product['lauploads_return_date']; ?></td>
                        <td>
                            <?php
                            $borrow_date = strtotime($product['lauploads_borrow_date']);
                            $return_date = strtotime($product['lauploads_return_date']);

                            if ($return_date <= $borrow_date || $return_date === false) {
                                echo '<input type="checkbox" name="return_item[]" value="' . $product['product_name'] . '">';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                endforeach;

                // Check if there are items to return before showing the submit button
                $itemsToReturn = array_filter($borrowHistory, function ($item) {
                    $borrow_date = strtotime($item['lauploads_borrow_date']);
                    $return_date = strtotime($item['lauploads_return_date']);

                    return ($return_date <= $borrow_date || $return_date === false);
                });

                if (!empty($itemsToReturn)) {
                    echo '<input type="submit" value="ยืนยันการคืน">';
                }
                ?>
            </tbody>
        </table>
    </form>
    <!-- Add your JavaScript or other necessary sections here -->
</body>
</html>
