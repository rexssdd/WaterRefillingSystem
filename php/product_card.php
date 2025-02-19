<?php 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Check if connection is still active
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check login status
$isLoggedIn = isset($_SESSION['username']) ? 'true' : 'false';
$user_id = $_SESSION['user_id'] ?? null;

// Handle "Add to Cart" action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {
    if (!$user_id) {
        echo "<script>alert('You must log in first to add items to your cart.'); window.location.href = 'login.php';</script>";
        exit;
    }

    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    // Validate quantity
    if ($quantity < 1) {
        echo "<script>alert('Invalid quantity selected.');</script>";
    } else {
        // Check if item already exists in the cart
        $check_cart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check_cart->bind_param("ii", $user_id, $product_id);
        $check_cart->execute();
        $result = $check_cart->get_result();
        $check_cart->close();

        if ($result->num_rows > 0) {
            // Update quantity if product exists in cart
            $update_cart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $update_cart->bind_param("iii", $quantity, $user_id, $product_id);
            $update_cart->execute();
            $update_cart->close();
        } else {
            // Insert new product into cart
            $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_cart->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_cart->execute();
            $insert_cart->close();
        }

        echo "<script>alert('Item added to cart successfully!');</script>";
    }
}
?>

<style>
    .product-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 250px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        margin: 10px;
        background-color: black;
    }

    .product-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 10px;
    }

    .product-item h5 {
        font-size: 18px;
        margin: 10px 0;
        color: gold;
    }

    .product-item p {
        font-size: 14px;
        color: white;
    }

    .form-control {
        width: 100%;
        text-align: center;
    }

    .btn-warning {
        color: white;
        width: 100%;
        padding: 8px;
        font-size: 16px;
        border-radius: 10px;
        border-color: gold;
        background-color: black;
    }

    .btn-warning:hover {
        background-color: yellow;
        color: black;
    }

    label {
        color: white;
    }
</style>

<div class="product-item" data-status="<?php echo htmlspecialchars($product['status']); ?>" data-stock="<?php echo (int)$product['stock']; ?>">
    <img src="<?php echo htmlspecialchars($product['photo']); ?>" class="product-image" alt="Product Image">
    <h5><strong class="s"><?php echo htmlspecialchars($product['name']); ?></strong></h5>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
    <p>Stock: <strong><?php echo (int)$product['stock']; ?></strong></p>
    <p>Status: 
        <strong class="<?php echo ($product['status'] === 'Available') ? 'text-success' : 'text-danger'; ?>">
            <?php echo htmlspecialchars($product['status']); ?>
        </strong>
    </p>

    <form id="add-to-cart-form" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
    
    <?php if (isset($_SESSION['username']) && $product['stock'] > 0 || $product['status'] === 'Available'): ?>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="1" min="1" class="form-control mb-2 quantity-field" required>
        
        <button type="submit" name="add_to_cart" class="uil uil-shopping-cart btn-warning add-to-cart-btn">
            Add to Cart
        </button>
    <?php endif; ?>
</form>

</div>
