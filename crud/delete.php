<?php
// Include the database connection file
include_once '../db.php';

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the product based on the ID
    $delete = $db->query("DELETE FROM crud WHERE id = $id");

    if ($delete) {
        echo "Product deleted successfully.";
        header("Location: add-remove-update.php");
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Product ID not provided.";
}
?>
