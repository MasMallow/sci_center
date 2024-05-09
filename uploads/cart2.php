<?php
session_start();

// Add item to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];

    $cart_item = array(
        'id' => $product_id,
        'name' => $product_name
    );

    // Check if cart exists in session, if not, create it
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Add item to cart
    $_SESSION['cart'][] = $cart_item;
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $item_index = $_GET['remove'];

    if (isset($_SESSION['cart'][$item_index])) {
        unset($_SESSION['cart'][$item_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reset array keys
    }
}

// Clear the entire cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Item Borrowing System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .empty-cart {
            text-align: center;
        }

        .cart-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .cart-actions a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            margin-right: 10px;
        }

        .cart-actions a:hover {
            background-color: #45a049;
        }

        .cart-table {
            width: 100%;
        }

        .cart-table th,
        .cart-table td {
            padding: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Item Borrowing System</h2>

        <?php
        // Display cart items
        if (!empty($_SESSION['cart'])) {
            $cart = $_SESSION['cart'];
            ?>

            <table class="table cart-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    foreach ($cart as $index => $item) {
                        ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><a href="?remove=<?php echo $index; ?>">Remove</a></td>
                        </tr>
                        <?php
                    }
                    ?>

                </tbody>
            </table>

            <div class="cart-actions">
                <a href="?clear">Clear Cart</a>
            </div>

            <?php
        } else {
            echo "<div class='empty-cart'><p>Your cart is empty.</p></div>";
        }

        ?>

        <h2>Add Item to Cart</h2>

        <form method="post" action="">
            <div class="form-group">
                <label for="product_id">Product ID</label>
                <input type="text" class="form-control" id="product_id" name="product_id" required>
            </div>
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
