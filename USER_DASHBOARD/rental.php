<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user session variables are properly set
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Guest';
$isLoggedIn = isset($_SESSION['username']);

// Fetch user data (secure prepared statement)
$user_query = "SELECT u.username, a.street, a.barangay, a.landmark, a.note
               FROM user u
               LEFT JOIN address a ON u.user_id = a.user_id
               WHERE u.user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc() ?? [];

// Format user details
$username = htmlspecialchars($user_data['username'] ?? 'Guest');
$address_parts = array_filter([
    $user_data['street'] ?? '',
    $user_data['barangay'] ?? '',
    !empty($user_data['landmark']) ? "Landmark: {$user_data['landmark']}" : '',
    !empty($user_data['note']) ? "Note: {$user_data['note']}" : ''
]);
$address = !empty($address_parts) ? implode(', ', $address_parts) : 'No address provided';

// Fetch user's order history
$order_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Fetch all available products categorized
$product_query = "SELECT * FROM product WHERE status = 'available'";
$product_result = $conn->query($product_query);
$normal_products = $refill_products = $rental_products = [];

while ($product = $product_result->fetch_assoc()) {
    switch ($product['product_type']) {
        case 'normal': $normal_products[] = $product; break;
        case 'refill': $refill_products[] = $product; break;
        case 'renting': $rental_products[] = $product; break;
    }
}

// Fetch cart count
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
    header('Location: getstarted.php');
    exit();
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
        
        .list-group{
            max-width: 135vh;
        }
        .content{
            margin-top: 100px;
            
        } .content h2{
            align-self: center;
        }
        .content{
            display: flexbox list-item ;
             flex-wrap: nowrap;
            justify-content: center;
            margin-top: 100px;
            
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
        .uil-signout .log1{
            background-color: red;
            color: white;
            margin-top: 450px;
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
            
        <a class="uil uil-box" href="dashboard.php" onclick="showProducts()" class="active"><strong class="x">Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart log"> Cart (<span id="cart-count"><?php echo $cart_count; ?></span>) </a>
        <a href="rental.php" class="uil uil-history active" onclick="showOrderHistory()">Product Rental</a>
        <a href="order.php" class="uil uil-history log" onclick="showOrderHistory()">Orders</a>
        <a href="order.php" class="uil uil-history log" onclick="showOrderHistory()">Order History</a>
        <a href="settings.php" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" style ="margin-top: 450px; background-color:red; color: white;"  class="uil uil-signout log1" onclick="confirmLogout()">Logout</a>
    </div>
<!-- Products Section -->
<section class="products">
    <div class="main-content">
        <nav class="navbar navbar-light p-3 mb-4 rounded">
            <h2 id="page-title">Products</h2>
        </nav>

        <!-- Welcome message and address -->
        <div class="container mt-5">
            <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>! Have a good day using the system.</h2>
            <p class="text-center">Your registered address: <?php echo htmlspecialchars($address); ?></p>
        </div>

        <!-- Products List -->
        <div id="content" class="content">
            <h3>Available Products</h3>

            <?php
            $product_types = ['renting'];
            
            foreach ($product_types as $type):
                // Query to fetch products by type and status
                $product_query = "SELECT * FROM product WHERE product_type = '$type' AND status = 'available'";
                $product_result = $conn->query($product_query);
            ?>

            <h4 class="mt-4 text-capitalize"><?php echo htmlspecialchars($type); ?> Products</h4>
            <ul class="list-group">
                <?php if ($product_result->num_rows > 0): ?>
                    <?php while ($product = $product_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <!-- Product Image -->
                                <img src="<?php echo !empty($product['photo']) ? htmlspecialchars($product['photo']) : 'default-image.png'; ?>" 
                                    alt="Product Image" 
                                    class="img-thumbnail me-3" 
                                    style="width: 100px; height: 100px; object-fit: cover;">

                                <!-- Product Details -->
                                <div>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                    <?php echo htmlspecialchars($product['description']); ?><br>
                                    Price: ₱<?php echo number_format($product['price'], 2); ?><br>
                                    Stock: <?php echo (int) $product['stock']; ?><br>
                                    Status: <span class="text-success">Available</span>
                                </div>
                            </div>

                                                <!-- Add to Cart Form -->
                        <?php if (isset($_SESSION['username']) && $product['stock'] > 0): ?>
                            <form action="/php/add_to_cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Rent Now</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Out of Stock</button>
                        <?php endif; ?>

                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No <?php echo htmlspecialchars($type); ?> products available.</li>
                <?php endif; ?>
            </ul>

            <?php endforeach; ?>
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

            fetch("/xampp/htdocs/WaterRefillingSystem/php/add_to_cart.php", { // Call the new PHP handler
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
    fetch('/php/fetch_cart_count.php')
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
