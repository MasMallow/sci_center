<?php
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['product_name'], $_POST['quantity'])) {
        $id = $_POST['id'];
        $product_name = $_POST['product_name'];
        $quantity = $_POST['quantity'];

        // Update product information in the database
        $stmt = $db->prepare("UPDATE crud SET product_name = ?, amount = ? WHERE id = ?");
        $stmt->bind_param('sii', $product_name, $quantity, $id);

        if ($stmt->execute()) {
            // Redirect back to the image page
            header("Location: add-remove-update.php");
            exit();
        } else {
            echo "Failed to update product. Please try again.";
        }
    } else {
        echo "Invalid data received.";
    }
} else {
    echo "Invalid request method.";
}
?>
