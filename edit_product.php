<?php
// Include the database connection file
include_once 'db.php';

// Check if product ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Retrieve product information based on the ID
    $query = $db->query("SELECT * FROM image WHERE id = $id");

    if ($query->num_rows == 1) {
        $row = $query->fetch_assoc();
        $product_name = $row['product_name'];
        $quantity = $row['amount'];
        $imageURL = 'test/' . $row['file_name'];
    } else {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Product ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
</head>

<body>
    <h1>Edit Product</h1>
    <img src="<?php echo $imageURL; ?>" alt="Product Image" style="max-width: 200px;"><br><br>
    <form action="update_product.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        Product Name: <input type="text" name="product_name" value="<?php echo $product_name; ?>"><br>
        Quantity: <input type="number" name="quantity" value="<?php echo $quantity; ?>"><br>
        <input type="submit" value="Save Changes">
    </form>
</body>

</html>
