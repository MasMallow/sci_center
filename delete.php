<?php
session_start();
require_once 'assets/database/dbConfig.php';

// Check if product ID is provided
if (!empty($_GET['id'])) {
    $id = $_GET['id'];

    $folder = 'assets/uploads/';

    $sql = $conn->prepare("SELECT * FROM crud WHERE ID = :id");
    $sql->bindParam(":id", $id);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    @unlink($folder . $result['img_name']);

    // Delete the product based on the ID
    $delete = $conn->query("DELETE FROM crud WHERE ID = $id");

    if ($delete) {
        echo "Product deleted successfully.";
        header("Location: management");
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Product ID not provided.";
}
