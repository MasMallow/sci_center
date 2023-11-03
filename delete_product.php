<?php
// Include the database connection file
include_once 'db.php';

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the product based on the ID
    $delete = $db->query("DELETE FROM image WHERE id = $id");

    if ($delete) {
        echo "Product deleted successfully.";
        header("Location: image.php");
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Product ID not provided.";
}
?>
