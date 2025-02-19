<?php
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

session_start();

// Handle order processing
if (isset($_POST['process_order'])) {
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $user_id = 1; // Replace with actual user ID from session
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

// Fetch user order history
$order_query = "SELECT * FROM orders WHERE user_id = 1 ORDER BY order_date DESC";
$order_result = $conn->query($order_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Page - JZ Waters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Order Summary</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['product_id']; ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td><?php echo $order['total']; ?></td>
                        <td><?php echo ucfirst($order['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
