<?php
include_once 'assets/database/connect.php';

// SQL query to get the top 10 borrowed products
$query = $conn->query("
SELECT sci_name, SUM(quantity) AS total_borrowed
FROM borrow_history
GROUP BY sci_name
ORDER BY total_borrowed DESC
LIMIT 10;
");

if ($query->rowCount() > 0) {
    echo "<h2>10อันดับ อุปกรณ์ที่ถูกยืมมากที่สุด</h2>";
    echo "<ul>";
    while ($row = $query->fetch()) {
        echo "<li>{$row['sci_name']} - ยืม: {$row['total_borrowed']} ครั้ง</li>";
    }
    echo "</ul>";
} else {
    echo "No data available.";
}
