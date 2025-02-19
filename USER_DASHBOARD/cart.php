<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user session variables are properly set
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 0; // Default value if not logged in
}

$username = $_SESSION['username'] ?? 'Guest';
$isLoggedIn = isset($_SESSION['username']) ? 'true' : 'false';

// Fetch user data
$user_query = "SELECT u.username, a.street, a.barangay, a.landmark, a.note
               FROM user u
               LEFT JOIN address a ON u.user_id = a.user_id
               WHERE u.user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc() ?? [];

$username = htmlspecialchars($user_data['username'] ?? 'Guest');
$address_parts = array_filter([
    $user_data['street'] ?? '',
    $user_data['barangay'] ?? '',
    !empty($user_data['landmark']) ? "Landmark: {$user_data['landmark']}" : '',
    !empty($user_data['note']) ? "Note: {$user_data['note']}" : ''
]);
$address = !empty($address_parts) ? implode(', ', $address_parts) : 'No address provided';

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity']));

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
if (isset($_POST['process_order']) && !empty($_SESSION['cart'])) {
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $order_date = date('Y-m-d');

    foreach ($_SESSION['cart'] as $product_id => $cart_item) {
        $quantity = $cart_item['quantity'];

        // Secure product retrieval
        $product_query = "SELECT price FROM product WHERE product_id = ?";
        $stmt = $conn->prepare($product_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_result = $stmt->get_result();

        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            $total = $product['price'] * $quantity;

            // Insert into orders table
            $insert_order = "INSERT INTO orders (user_id, order_date, product_id, quantity, total, status) 
                             VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($insert_order);
            $stmt->bind_param("isidi", $user_id, $order_date, $product_id, $quantity, $total);
            if (!$stmt->execute()) {
                echo "Error: " . $conn->error;
            }
        }
    }

    $_SESSION['cart'] = []; // Clear cart after placing order
    echo "Order placed successfully!";
}

// Remove item from cart
if (isset($_GET['remove_product_id'])) {
    $product_id_to_remove = intval($_GET['remove_product_id']);
    unset($_SESSION['cart'][$product_id_to_remove]);
    header("Location: cart.php");
    exit();
}

// Fetch products for cart display
$product_query = "SELECT * FROM product WHERE status = 'available'";
$product_result = $conn->query($product_query);

// Initialize cart if not already present
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
// Fetch total price of items in the cart
$total_price = 0;

$sql_total_price = "SELECT SUM(p.price * ci.quantity) AS total_price 
                    FROM cart ci 
                    JOIN product p ON ci.product_id = p.product_id 
                    WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql_total_price);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_price = $row['total_price'] ?? 0; // Default to 0 if cart is empty

// Fetch user's order history
$order_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();


// Check if user has an address
$sql_check_address = "SELECT * FROM address WHERE user_id = ?";
$stmt = $conn->prepare($sql_check_address);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_address = $result->num_rows > 0;

$cart_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    if (!$has_address) {
        echo "<script>alert('Please set your address before placing an order.');</script>";
    } elseif ($cart_empty) {
        echo "<script>alert('Your cart is empty. Add items before placing an order.');</script>";
    } else {
        $note = isset($_POST['order_note']) ? $_POST['order_note'] : '';
        $payment_method = $_POST['payment_method'];

        // Insert order into database
        $sql_order = "INSERT INTO orders (user_id, order_note, payment_method, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql_order);
        $stmt->bind_param("iss", $user_id, $note, $payment_method);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Generate a delivery number (consider making this unique)
        $delivery_number = time() . rand(100, 999);
       
        // Redirect with success message
        echo "<script>
                alert('Order placed successfully! Order No: $order_id | Delivery No: $delivery_number');
                window.location.href='order_history.php';
              </script>";
    }
}
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
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
        body { background-color: black; color: #d4af37; font-family: 'Poppins', sans-serif; }
        .sidebar { background-color: black; height: 100vh; width: 260px; position: fixed; padding-top: 20px; color: #d4af37; }
        .sidebar a { color: #d4af37; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(230, 227, 227, 0.2); border-radius: 5px; }
        .main-content { margin-left: 270px; padding: 30px; }
        .navbar { background-color: black; color: #d4af37; width: 100%; padding: 15px; text-align: center; }
        .cart-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .cart-item { background-color: #000; border-radius: 10px; padding: 15px; width: 250px; text-align: center; box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2); }
        .product-image { width: 100%; height: 200px; object-fit: contain; border-radius: 5px; }
        .remove-btn { background-color: red; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <a href="#" class="nav_logo">
            <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo" width="40">
            <strong class="Jz_Waters">Jz Waters</strong>
        </a>
        <a href="dashboard.php" class="active"><strong>Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart log">Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
   <a href="#">Orders</a>
        <a href="#">Order History</a>
        <a href="#">Settings</a>
        <a href="/php/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar">
            <h2>Your Cart</h2>
        </nav>

        <div class="container mt-4">
            <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p class="text-center">Your registered address: <?php echo htmlspecialchars($address); ?></p>
        </div>

      <!-- Unified Cart Section -->
<section class="cart mt-4">
    <div id="cart-content">
        <div class="cart-container">
            <?php

            // Fetch cart items from the database
            $stmt = $conn->prepare("
                SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.photo
                FROM cart c
                JOIN product p ON c.product_id = p.product_id
                WHERE c.user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $cart_result = $stmt->get_result();
            
            if ($cart_result->num_rows > 0) {
                while ($cart_item = $cart_result->fetch_assoc()) {
                    $item_total = $cart_item['price'] * $cart_item['quantity'];
                    $total_price += $item_total;
            ?>
                <div class="cart-item">
    <img src="<?php echo htmlspecialchars($cart_item['photo']); ?>" class="product-image" alt="Product Image">
    <h5><?php echo htmlspecialchars($cart_item['name']); ?></h5>
    <p>Price: ₱<?php echo number_format($cart_item['price'], 2); ?></p>

    <!-- Editable Quantity Input -->
    <label for="quantity_<?php echo $cart_item['cart_id']; ?>">Quantity:</label>
    <input type="number" id="quantity_<?php echo $cart_item['cart_id']; ?>" class="quantity-input"
           value="<?php echo $cart_item['quantity']; ?>" min="1"
           data-cart-id="<?php echo $cart_item['cart_id']; ?>"
           data-product-id="<?php echo $cart_item['product_id']; ?>" 
           data-stock="<?php echo $cart_item['stock']; ?>">

    <p>Total: ₱<span id="total_<?php echo $cart_item['cart_id']; ?>">
        <?php echo number_format($item_total, 2); ?>
    </span></p>

    <!-- Remove button -->
    <a href="cart.php?remove_cart_id=<?php echo $cart_item['cart_id']; ?>" class="remove-btn">Remove</a>
</div>


            <?php
                }
            } else {
                echo "<p>Your cart is empty.</p>";
            }
            ?>
        </div>
        <h4>Total Cart Value: ₱<?php echo number_format($total_price, 2); ?></h4>

        <!-- Checkout Form -->
<form method="POST" action="checkout.php" onsubmit="return validateAddress()">
    <label for="order_note">Special Instructions:</label>
    <textarea name="order_note" id="order_note" class="form-control mb-2" placeholder="Enter any special instructions..."></textarea>

    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" class="form-control mb-2" required>
        <option value="gcash">GCash</option>
        <option value="paypal">PayPal</option>
        <option value="credit_card">Credit/Debit Card</option>
        <option value="cod">Cash on Delivery</option>
    </select>
  

    <button type="submit" name="place_order" class="btn btn-primary w-100" id="placeOrderBtn" <?= !$has_address ? 'disabled' : ''; ?>>
        Place Order
    </button>
</form>

<!-- Manage Address Button -->
<button class="btn btn-secondary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#addressModal">
    Manage Address
</button>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($hasAddress): ?>
                    <?php while ($row = $addresses->fetch_assoc()): ?>
                        <form method="POST">
                            <input type="hidden" name="address_id" value="<?= $row['address_id']; ?>">
                            <?php foreach (["street", "barangay", "district", "city", "province"] as $field): ?>
                                <input type="text" name="<?= $field ?>" class="form-control mb-2" value="<?= htmlspecialchars($row[$field]); ?>" required>
                            <?php endforeach; ?>
                            <button type="submit" name="update_address" class="btn btn-primary w-100">Update Address</button>
                        </form>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-danger">No address found. Please add your address first.</p>
                <?php endif; ?>

                <h5 class="mt-3">Add New Address</h5>
                <form method="POST">
                    <input type="hidden" name="add_address">
                    <?php foreach (["Street", "Barangay", "District", "City", "Province"] as $field): ?>
                        <input type="text" name="<?= strtolower($field) ?>" class="form-control mb-2" placeholder="<?= $field ?>" required>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-success w-100">Add Address</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    // Handle quantity change via AJAX
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function () {
            let cartId = this.dataset.cartId;
            let productId = this.dataset.productId;
            let newQuantity = parseInt(this.value);
            let maxStock = parseInt(this.dataset.stock);

            if (newQuantity <= 0) {
                alert("Quantity must be at least 1.");
                this.value = 1;
                return;
            }

            if (newQuantity > maxStock) {
                alert("Not enough stock available!");
                this.value = maxStock;
                return;
            }

            fetch('/php/update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_id: cartId, product_id: productId, quantity: newQuantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('total_' + cartId).textContent = "₱" + data.new_total;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

function validateAddress() {
    var hasAddress = <?= json_encode($hasAddress) ?>;
    if (!hasAddress) {
        alert("Please set your address first.");
        return false;
    }
    return true;
}
</script>
</section>

<?php
// Remove item from cart
if (isset($_GET['remove_cart_id'])) {
    $remove_cart_id = $_GET['remove_cart_id'];
    
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $remove_cart_id, $user_id);
    $delete_stmt->execute();

    // Refresh the page after removal
    header("Location: cart.php");
    exit();
}
?>

<?php $conn->close(); ?>
</body>
</html>