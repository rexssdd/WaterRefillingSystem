<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "wrsystem";
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product is already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = ['product_id' => $product_id, 'quantity' => $quantity];
    }
}

// Handle processing the order
if (isset($_POST['process_order'])) {
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $user_id = 1; // Use session user_id (replace with actual user ID)
    $order_date = date('Y-m-d');

    foreach ($_SESSION['cart'] as $product_id => $cart_item) {
        $quantity = $cart_item['quantity'];
        $product_query = "SELECT * FROM product WHERE product_id = $product_id";
        $product_result = $conn->query($product_query);
        
        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            $total = $product['price'] * $quantity;

            // Insert into orders table
            $insert_order = "INSERT INTO orders (user_id, order_date, product_id, quantity, total, status) 
                             VALUES ('$user_id', '$order_date', '$product_id', '$quantity', '$total', 'pending')";
            if (!$conn->query($insert_order)) {
                echo "Error: " . $conn->error;
            }
        }
    }

    $_SESSION['cart'] = [];  // Clear cart after placing order
    echo "Order placed successfully!";
}

// Remove item from cart
if (isset($_GET['remove_product_id'])) {
    $product_id_to_remove = $_GET['remove_product_id'];
    unset($_SESSION['cart'][$product_id_to_remove]);
    header("Location: cart.php"); // Redirect back to the cart page to reflect the changes
}

// Fetch products for cart
$product_query = "SELECT * FROM product WHERE status = 'available'";
$product_result = $conn->query($product_query);

// Initialize cart if not already present
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch user's order history
$user_id = 1;  // Replace with the actual logged-in user ID
$order_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$order_result = $conn->query($order_query);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #212121; color: #d4af37; font-family: 'Poppins', sans-serif; }
        .sidebar { height: 100vh; width: 260px; position: fixed; background: #000; padding-top: 20px; color: #d4af37; box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar a { color: #d4af37; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.2); border-radius: 5px; }
        .main-content { margin-left: 270px; padding: 30px; }
        .navbar { background: #000; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); color: #d4af37; }
        .product-item { background: #333; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #d4af37; }
        .product-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: unset; }
        .product-image { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .cart-item { background: #333; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #d4af37; position: relative; }
       .mb-2{
        max-width: 500px;
       }
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            cursor: pointer;
            font-size: 18px;
        }
        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup-content {
            background-color: #222;
            padding: 20px;
            width: 400px;
            margin: 100px auto;
            border-radius: 8px;
            color: #d4af37;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar text-white">
        <h4 class="text-center">POS System</h4>
        <a href="dashboard.php" class="">Products</a>
        <a href="cart.php" class="active">Cart (<span id="cart-count"><?php echo count($_SESSION['cart']); ?></span>)</a>
        <a href="order-history.php">Order History</a>
        <a href="?logout=true">Logout</a>
    </div>

    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-light p-3 mb-4 rounded">
            <h2 id="page-title">Your Cart</h2>
        </nav>

        <!-- Cart Section -->
        <section class="cart">
            <div id="cart-content">
                <h3>Your Cart</h3>
                <div class="product-container">
                    <?php 
                        $total_price = 0;
                        foreach ($_SESSION['cart'] as $product_id => $cart_item):
                            $product_query = "SELECT * FROM product WHERE product_id = $product_id";
                            $product_result = $conn->query($product_query);
                            $product = $product_result->fetch_assoc();
                            $item_total = $product['price'] * $cart_item['quantity'];
                            $total_price += $item_total;
                    ?>
                        <div class="cart-item">
                            <img src="<?php echo $product['photo']; ?>" class="product-image" alt="Product Image">
                            <h5><?php echo $product['name']; ?></h5>
                            <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
                            <p>Quantity: <?php echo $cart_item['quantity']; ?></p>
                            <p>Total: ₱<?php echo number_format($item_total, 2); ?></p>
                            <!-- Remove Button -->
                            <a href="cart.php?remove_product_id=<?php echo $product_id; ?>" class="remove-btn">X</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h4>Total Cart Value: ₱<?php echo number_format($total_price, 2); ?></h4>
                <form method="POST">
                    <input type="text" name="address" placeholder="Enter your address" class="form-control mb-2" required>
                    <select name="payment_method" class="form-control mb-2" required>
                        <option value="cash">Cash on Delivery</option>
                        <option value="credit">Credit Card</option>
                    </select>
                    <button type="submit" name="process_order" class="btn btn-warning">Place Order</button>
                </form>
            </div>
        </section>
    </div>
</body>
</html>

<?php
$conn->close();
?>
