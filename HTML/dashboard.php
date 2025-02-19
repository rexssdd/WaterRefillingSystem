<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

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



// Fetch user's order history
$user_id = 1;  // Replace with actual logged-in user ID
$order_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$order_result = $conn->query($order_query);

// Fetch all available products categorized
$normal_products = [];
$refill_products = [];
$rental_products = [];

$product_query = "SELECT * FROM product ";
$product_result = $conn->query($product_query);

while ($product = $product_result->fetch_assoc()) {
    if ($product['product_type'] == 'normal') {
        $normal_products[] = $product;
    } elseif ($product['product_type'] == 'refill') {
        $refill_products[] = $product;
    } elseif ($product['product_type'] == 'renting') {
        $rental_products[] = $product;
    }
}

$cart_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;


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
    <title>JZ Waters User Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { background-color:rgb(0, 0, 0);  color: #d4af37; font-family: 'Poppins', sans-serif; }
        .sidebar {  background-color: black;  height: 100vh; width: 260px; position: fixed; padding-top: 20px; color: #d4af37; box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar a { color: #d4af37; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(230, 227, 227, 0.2); border-radius: 5px; }
        .main-content { margin-left: 270px; padding: 30px; }
        .navbar {     display: inline-flex;justify-content: center; background-color: black; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); color: #d4af37;  position: fixed; width: 100%; margin-left: -40px; margin-top: -30px;}
        .product-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .product-item { margin-top: 500px;background: #333; padding: 15px; max-width: 30%; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(255, 255, 255, 0.59); color: #d4af37; }
        .product-image { width: 100%; height: 200px; object-fit: contain; border-radius: 5px; }
        
        .content{
            margin-top: 100px;
            
        } .content h2{
            align-self: center;
        }
        .logo{
        width: 40px;  /* Adjust size as needed */
        height: auto;
        gap: 2px;
        margin-right: 20px; 
        }
        .Jz_Waters{
            font-size: 26px;
            font-style: sans-serif, arial;
        }
        .nav_logo{
            gap: 5px;
        }
        .log{
            color:rgb(243, 243, 243);
        }
   </style>
</head>
<body>
    <div class="sidebar text-white">
    <a href="#" class="nav_logo">
                <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo">
                <Strong class="Jz_Waters">Jz Waters</Strong>
            </a>
            
        <a class="uil uil-box" href="#" onclick="showProducts()" class="active"><strong class="x">Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart log">
    Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)
</a>

        <a href="#" class="uil uil-history log" onclick="showOrderHistory()">Order History</a>
        <a href="settings.php" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout log" onclick="confirmLogout()">Logout</a>
    </div>

    <section class="products">
    <div class="main-content">
        <nav class="navbar navbar-light p-3 mb-4 rounded">
            <h2  id="page-title">Products</h2>
        </nav>
<!-- address-->
<div class="container mt-5">
        <h2 class="text-center">Welcome, <?php echo $username; ?>! Have a good day using the system.</h2>
        <p class="text-center">Your registered address: <?php echo $address; ?></p>
    </div>
    
        <div id="content" class="content">

            <h3>Water Refills</h3>
            <div class="product-container">
                <?php foreach ($refill_products as $product): ?>
                    <div class="product-item" data-status="<?php echo htmlspecialchars($product['status'] ?? 'Unavailable'); ?>" data-stock="<?php echo (int)($product['stock'] ?? 0); ?>">
    <img src="<?php echo htmlspecialchars($product['photo'] ?? 'default.jpg'); ?>" class="product-image" alt="Product Image">
    <h5><strong><?php echo htmlspecialchars($product['name'] ?? 'Unknown Product'); ?></strong></h5>
    <p><?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?></p>
    <p>Price: ₱<?php echo isset($product['price']) ? number_format($product['price'], 2) : 'N/A'; ?></p>
    <p>Stock: <strong><?php echo (int)($product['stock'] ?? 0); ?></strong></p>
    <p>Status: 
        <strong class="<?php echo ($product['status'] ?? 'Unavailable') === 'Available' ? 'text-success' : 'text-danger'; ?>">
            <?php echo htmlspecialchars($product['status'] ?? 'Unavailable'); ?>
        </strong>
    </p>

    <form id="add-to-cart-form" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id'] ?? ''; ?>">
        
        <?php if (isset($_SESSION['username']) && ($product['stock'] ?? 0) > 0 && ($product['status'] ?? '') === 'Available'): ?>
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="1" min="1" class="form-control mb-2 quantity-field" required>
            
            <button type="submit" name="add_to_cart" class="btn-warning add-to-cart-btn">
                Add to Cart
            </button>
        <?php endif; ?>
    </form>
</div>

                <?php endforeach; ?>
                    </div>
            <h3>Rental Products</h3>
            <div class="product-container">
                <?php foreach ($rental_products as $product): ?>
                    <?php include 'rental_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
            

            <h3>Normal Products</h3>
            <div class="product-container">
                <?php foreach ($normal_products as $product): ?>
                    <?php include 'product_card.php'; ?>
                <?php endforeach; ?>
            </div>

        </div>  
    </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    let form = document.getElementById("add-to-cart-form");
    let button = document.querySelector(".add-to-cart-btn");

    if (form && button) {
        button.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(form);

            // Disable button to prevent multiple clicks
            button.disabled = true;

            fetch("add_to_cart.php", { // Call the new PHP handler
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // Expect JSON response
            .then(data => {
                if (data.success) {
                    alert(data.success);
                } else if (data.error) {
                    alert("Error: " + data.error);
                }
                button.disabled = false; // Re-enable button after request completes
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
                button.disabled = false; // Re-enable button in case of error
            });
        });
    }
});

function fetchCartCount() {
    fetch('fetch_cart_count.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('cart-count').innerText = data.cart_count;
    })
    .catch(error => console.error('Error fetching cart count:', error));
}

// Fetch cart count when the page loads
document.addEventListener("DOMContentLoaded", fetchCartCount);
function updateCartCount(count) {
    document.getElementById('cart-count').innerText = count;
}

        function confirmLogout() {
         var logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));
         logoutModal.show();
            }
        </script>
</body>
</html>
