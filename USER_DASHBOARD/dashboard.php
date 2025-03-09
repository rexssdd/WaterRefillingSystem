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
        case 'Products&Accessories': $normal_products[] = $product; break;
        case 'Refill_Products': $refill_products[] = $product; break;
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
    
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
                               <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
         body {
        background-color:   #353544;
        color: white;
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
    }

    .sidebar {
        color: white;
        font-family: 'Poppins', sans-serif;
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;
        background: 	#1e1e2f;
        transition: 0.3s ease-in-out;
        padding-top: 20px;
        z-index: 1000;
    }

    .sidebar.show {
        left: 0;
    }
    .sidebar.hide {
        left: -257px;
    }


    .sidebar a {
        color:white;
        padding: 15px;
        text-decoration: none;
        display: block;
        transition: 0.3s;
    }

    .sidebar a:hover, 
    .sidebar a.active {
        font-weight: 400;
        line-height: 1.6;
        font-size: 18px;
        color: gold;
        background: rgba(230, 227, 227, 0.2);
        border-radius: 5px;
    }
    .menu-toggle {
        position: fixed;
        top: 15px;
        left: 275px;
        background: #d4af37;
        color: #101720;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        z-index: 1100;
        border-radius: 5px;
    }
    .nav_logo{
            gap: 5px;
        }

        
        .logo{
        width: 40px;  /* Adjust size as needed */
        height: auto;
        gap: 2px;
        margin-right: 20px; 
        }

    .menu-toggle:focus {
        outline: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            left: -100%;
        }

        .sidebar.show {
            left: 0;
        }
    }   

    .logout-btn {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: red;
        color: black;
        padding: 10px;
        text-align: center;
        border-radius: 20px;
        width: 80%;
        text-decoration: none;
        text-align: center;
        font-weight: bold;
    }

    .main-content { 
        margin-left: 260px; 
        transition: margin-left 0.3s ease-in-out; 
    }
    .sidebar.show ~ .main-content {
        margin-left: 260px; 
    }
    .sidebar:not(.show) ~ .main-content {
        margin-left: 0;
    }
    .products{
        margin:20px;
    }
    .user-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .user-icon {
        cursor: pointer;
        color: #d4af37;
        font-size: 1.5rem;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 50px;
        right: 10px;
        background: #232b2b;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        min-width: 120px;
        overflow: hidden;
        z-index: 1000;
    }

    .dropdown-menu a {
        display: block;
        padding: 10px;
        color: #d4af37;
        text-decoration: none;
        transition: 0.3s;
        text-align: center;
    }

    .dropdown-menu a:hover {
        background: rgba(230, 227, 227, 0.2);
    }
    .n{
        margin-left: 10px;
        color: gold;
        font-size: 18px;
    }
    a{
        color: white;
    }
    .uil-box{
        font-size: 24px;
    }

    .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #4caf50;
    color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 16px;
    z-index: 1000;
    animation: fadeInOut 3s ease-in-out;
}

.notification.error {
    background-color: #ff4d4d;
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-10px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-10px); }
}

.x{
        font-size: 24px;
    }

    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar show" id="sidebar">
        <a href="#" class="nav_logo">
            <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo" style="width: 40px; margin-right: 10px;">
            <strong style="font-size: 24px;">Jz Waters</strong>
        </a>
        <a href="dashboard.php" class="uil uil-box active"><strong class="n">Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart ">Cart(<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        <a href="rental.php" class="uil uil-house-user">Product Rental</a>
        <a href="order.php" class="uil uil-heart-alt">Orders</a>
        <a href="orderhistory.php" class="uil uil-history">Order History</a>
        <a href="settings.php" class="uil uil-cog">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout logout-btn" style=" color: white; position: absolute; bottom: 20px;">Logout</a>
    </div>
   
    <!-- Main Content -->
    <section class="products">
        <div class="main-content">
            <nav class="navbar" style="color:white; background:	#515151; border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-box"><strong class="x">Products</strong></h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white;margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="settings.php">Settings</a>
                    <a href="/php/logout.php" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>
            <div id="content" class="content" style="margin-top:20px;">
                <?php
                $product_types = ['Refill_Products', 'Products&Accessories'];
                foreach ($product_types as $type):
                    $product_query = "SELECT * FROM product WHERE product_type = '$type' AND status = 'available'";
                    $product_result = $conn->query($product_query);
                ?>
                <h4 style=" margin-top: 20px; font-size: 30px; font-family: sans-serif;" class="uil-shopping-bag mt-4 text-capitalize"><?php echo htmlspecialchars($type); ?></h4>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php if ($product_result->num_rows > 0): ?>
                        <?php while ($product = $product_result->fetch_assoc()): ?>

<div style="margin-top: 50px;" class="col">
    <div class="card shadow-w border-0 rounded-4 text-center"  
        style="box-shadow: 0 6px 14px rgba(247, 241, 241, 0.94); background: #1e1e2f; color: #fff; width: 80%; height:500px; margin: auto; font-family: 'Poppins', sans-serif;">
        
        <!-- Image Section -->
        <div class="card-img-top d-flex justify-content-center align-items-center" 
            style="height: 220px; background: rgba(255, 255, 255, 0.15); border-radius: 15px 15px 0 0; overflow: hidden;">
            <img src="<?php echo !empty($product['photo']) ? htmlspecialchars($product['photo']) : 'default-image.png'; ?>" 
                alt="Product Image" class="img-fluid" 
                style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: 12px;">
        </div>

        <!-- Card Body -->
        <div class="card-body p-4">
            <h4 class="fw-bold" style="color: #d4af37; font-size: 24px;"> 
                <?php echo htmlspecialchars($product['name']); ?> 
            </h4>
            <p class="card-text" style="font-size: 16px; font-weight: 400; color: #f8f8f8; line-height: 1.6;">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="fw-bold" style="color: #d4af37; font-size: 20px;">₱<?php echo number_format($product['price'], 2); ?></span>
                <small class="text-light" style="font-size: 14px;">Stock: <?php echo (int) $product['stock']; ?></small>
            </div>
        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-transparent border-0 pb-3">
            <!-- Quantity Section -->
            <div class="d-flex justify-content-center align-items-center mb-3">
    <button type="button" class="btn btn-outline-light btn-lg px-3 py-1 decrease-btn">-</button>
    <input type="number" name="quantity" value="1" min="1" max="<?php echo (int) $product['stock']; ?>" 
        class="form-control text-center mx-2 quantity-input"
        style="width: 60px; font-size: 18px; font-weight: 600; text-align: center;">
    <button type="button" class="btn btn-outline-light btn-lg px-3 py-1 increase-btn">+</button>
</div>

            <!-- Add to Cart Button -->
            <?php if ($product['stock'] > 0): ?>
                <form onsubmit="return addToCart(event, this);">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit" class="btn btn-warning w-100 fw-bold btn-lg"
                            style="background: white; border-radius: 12px; font-size: 18px;">🛒 Add to Cart</button>
                    </form>


            <?php else: ?>
                <button class="btn btn-secondary w-100 btn-lg" disabled 
                    style="border-radius: 12px; font-size: 18px;">Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>


                        <?php endwhile; ?>
                    <?php else: ?>
                        <li>No <?php echo htmlspecialchars($type); ?> products available.</li>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".increase-btn").forEach(button => {
        button.addEventListener("click", function () {
            let input = this.parentElement.querySelector(".quantity-input");
            if (!input) return;

            let max = parseInt(input.max) || 1;
            let value = parseInt(input.value) || 1;

            if (value < max) {
                input.value = value + 1;
            }
        });
    });

    document.querySelectorAll(".decrease-btn").forEach(button => {
        button.addEventListener("click", function () {
            let input = this.parentElement.querySelector(".quantity-input");
            if (!input) return;

            let min = parseInt(input.min) || 1;
            let value = parseInt(input.value) || 1;

            if (value > min) {
                input.value = value - 1;
            }
        });
    });
});



function updateQuantity(cartId, change) {
    let quantityInput = document.getElementById("quantity_" + cartId);
    if (!quantityInput) {
        console.error(`🚨 Quantity input not found for cartId: ${cartId}`);
        return;
    }

    let maxStock = parseInt(quantityInput.dataset.stock) || Infinity;
    let newQuantity = parseInt(quantityInput.value) + change;

    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > maxStock) newQuantity = maxStock;

    quantityInput.value = newQuantity;
}

// Optional: Toast-style feedback
function showToast(message) {
    let toast = document.createElement('div');
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.backgroundColor = '#333';
    toast.style.color = '#fff';
    toast.style.padding = '10px 20px';
    toast.style.borderRadius = '8px';
    toast.style.zIndex = '1000';
    toast.style.opacity = '0.9';
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 2000);
}


function addToCart(event, form) {
    event.preventDefault(); // Prevent default form submission

    let formData = new FormData(form);

    fetch("/php/add_to_cart.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.success, "success");
        } else {
            showNotification(data.error, "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showNotification("Something went wrong!", "error");
    });

    return false; // Prevent page reload
}

function showNotification(message, type = "success") {
    let notification = document.createElement("div");
    notification.innerText = message;
    notification.className = `notification ${type}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}



document.getElementById("userIcon").addEventListener("click", function () {
        let dropdown = document.getElementById("dropdownMenu");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        let dropdown = document.getElementById("dropdownMenu");
        let userIcon = document.getElementById("userIcon");
        if (!userIcon.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
    function decreaseQuantity(button) {
        let input = button.nextElementSibling;
        if (input.value > 1) {
            input.value--;
        }
    }

    function increaseQuantity(button) {
        let input = button.previousElementSibling;
        if (parseInt(input.value) < parseInt(input.max)) {
            input.value++;
        }
    }
        document.getElementById("menu-icon").addEventListener("click", function () {
            let sidebar = document.getElementById("sidebar");
            let mainContent = document.querySelector(".main-content");
            sidebar.classList.toggle("hide");
            mainContent.classList.toggle("expanded");
        });
        // Function to decrease the quantity
        function decreaseQuantity(productId, stock) {
            const quantityInput = document.getElementById('quantity_' + productId);
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        }

        // Function to increase the quantity
        function increaseQuantity(productId, stock) {
            const quantityInput = document.getElementById('quantity_' + productId);
            let currentValue = parseInt(quantityInput.value);
            if (currentValue < stock) {
                quantityInput.value = currentValue + 1;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
    let form = document.getElementById("add-to-cart-form");
    let button = document.querySelector(".add-to-cart-btn");
    let cartCount = document.getElementById("cart-count"); // Update cart count dynamically

    if (form && button) {
        button.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent full page reload

            let formData = new FormData(form);

            // Disable button to prevent multiple clicks
            button.disabled = true;

            fetch("/xampp/htdocs/WaterRefillingSystem/php/add_to_cart.php", { // Call the PHP handler
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // Expect JSON response
            .then(data => {
                if (data.success) {
                    alert(data.success);

                    // Update the cart count dynamically
                    if (cartCount) {
                        cartCount.textContent = data.cart_count; // Update count from response
                    }
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
                
                function confirmLogout() {
        let confirmAction = confirm("Are you sure you want to logout?");
        if (!confirmAction) {
            event.preventDefault(); // Prevent logout if the user cancels
        }
    }
    </script>
</body>
</html>
