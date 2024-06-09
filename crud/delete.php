<?php
// Include the database connection file
require_once '../assets/database/connect.php';

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the product based on the ID
    $delete = $conn->query("DELETE FROM crud WHERE id = $id");

    if ($delete) {
        echo "Product deleted successfully.";
        header("Location: management");
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Product ID not provided.";
}
?>
