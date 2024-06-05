<?php
// Start session
session_start();

// Include database connection (if not already included)
require_once 'assets/database/connect.php';
include_once 'includes/thai_date_time.php';

if (!isset($_SESSION['staff_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('Location: auth/sign_in.php');
    exit;
}

try {
    // Query for materials, equipment, and tools with their respective usage counts
    $materialQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'วัสดุ' GROUP BY sci_name ORDER BY usage_count DESC LIMIT 10";
    $equipmentQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'อุปกรณ์' GROUP BY sci_name ORDER BY usage_count DESC LIMIT 10";
    $toolQuery = "SELECT sci_name, COUNT(*) as usage_count FROM crud WHERE categories = 'เครื่องมือ' GROUP BY sci_name ORDER BY usage_count DESC LIMIT 10";

    // Prepare and execute queries for materials
    $materialStmt = $conn->prepare($materialQuery);
    $materialStmt->execute();
    $materialResult = $materialStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare and execute queries for equipment
    $equipmentStmt = $conn->prepare($equipmentQuery);
    $equipmentStmt->execute();
    $equipmentResult = $equipmentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare and execute queries for tools
    $toolStmt = $conn->prepare($toolQuery);
    $toolStmt->execute();
    $toolResult = $toolStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database query errors
    echo "Query failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 10 Usage</title>
</head>

<body>
    <h2>Top 10 วัสดุ</h2>
    <ul>
        <?php
        // Display top 10 materials
        foreach ($materialResult as $row) {
            echo "<li>{$row['sci_name']} - Used {$row['usage_count']} times</li>";
        }
        ?>
    </ul>

    <h2>Top 10 อุปกรณ์</h2>
    <ul>
        <?php
        // Display top 10 equipment
        foreach ($equipmentResult as $row) {
            echo "<li>{$row['sci_name']} - Used {$row['usage_count']} times</li>";
        }
        ?>
    </ul>

    <h2>Top 10 เครื่องมือ</h2>
    <ul>
        <?php
        // Display top 10 tools
        foreach ($toolResult as $row) {
            echo "<li>{$row['sci_name']} - Used {$row['usage_count']} times</li>";
        }
        ?>
    </ul>
</body>

</html>