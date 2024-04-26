<?php
include_once 'connect.php';

// SQL query to get the top 10 borrowed products
$query = $db->query("
SELECT product_name, SUM(quantity) AS total_borrowed
FROM borrow_history
GROUP BY product_name
ORDER BY total_borrowed DESC
LIMIT 10;
");

if ($query->num_rows > 0) {
    echo "<h2>10อันดับ อุปกรณ์ที่ถูกยืมมากที่สุด</h2>";
    echo "<ul>";
    while ($row = $query->fetch_assoc()) {
        echo "<li>{$row['product_name']} - ยืม: {$row['total_borrowed']} ครั้ง</li>";
    }
    echo "</ul>";
} else {
    echo "No data available.";
}
