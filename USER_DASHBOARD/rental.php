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

$userId = $_SESSION['user_id'];
$addressQuery = "SELECT * FROM address WHERE user_id = ?";
$stmt = $conn->prepare($addressQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
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
                                           <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
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
    .uil-house-user{
        font-size: 24px;
    }
    .x{
        font-size: 24px;
    }

    /* Modal Background */
.modal-content {
    background: #1e1e2f;
    color: red;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(202, 200, 200, 0.3);
}

/* Modal Header */
.modal-header {
    background: #FFFFF0;
    color: black;
    border-radius: 12px 12px 0 0;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 22px;
    font-weight: bold;
}

/* Close Button */
.btn-close {
    background-color:  #FFFFF0;
    border-radius: 50%;
    padding: 8px;
    transition: 0.3s;
}

.btn-close:hover {
    background-color:  #FFFFF0;
}

/* Modal Body */
.modal-body {
    padding: 20px;
    font-size: 16px;
}

/* Labels */
.modal-body label {
    font-weight: bold;
    color: #FFFFF0;
    margin-top: 10px;
    display: block;
}

/* Select and Input Fields */
.modal-body select,
.modal-body input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solidrgb(255, 255, 255);
    background: #2a2a3c;
    color: white;
    font-size: 16px;
}

/* Placeholder Text Color */
.modal-body input::placeholder,
.modal-body select {
    color: #bbb;
}

/* Confirm Button */
.modal-body .btn-primary {
    background: #FFFFF0;
    color: black;
    font-weight: bold;
    font-size: 18px;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 8px;
    transition: 0.3s;
}

.modal-body .btn-primary:hover {
    background:#284387;
    color:  #FFFFF0;
}

/* Responsive Design */
@media (max-width: 576px) {
    .modal-content {
        padding: 15px;
    }

    .modal-body input,
    .modal-body select {
        font-size: 14px;
    }

    .modal-title {
        font-size: 18px;
    }
}

   </style>
</head>
<body>
<div class="sidebar text-white">
    <a href="#" class="nav_logo">
                <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo">
                <Strong style="font-size: 24px;" class="Jz_Waters">Jz Waters</Strong>
            </a>
            
        <a class="uil uil-box" href="dashboard.php" onclick="showProducts()" >Products</a>
        <a href="cart.php" class="uil uil-shopping-cart log"> Cart (<span id="cart-count"><?php echo $cart_count; ?></span>) </a>
        <a href="rental.php" class="uil uil-house-user active" onclick="showOrderHistory()"><strong class="n">Product Rental</strong></a>
        <a href="order.php" class="uil uil-heart-alt log" onclick="showOrderHistory()">Orders</a>
        <a href="order.php" class="uil uil-history log" onclick="showOrderHistory()">Order History</a>
        <a href="settings.php" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout logout-btn" style=" color: white; position: absolute; bottom: 20px;">Logout</a>
        </div>
<!-- Products Section -->
<!-- Main Content -->
<section class="products">
        <div class="main-content">
            <nav class="navbar" style="color:white; background:	#515151; border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-house-user"><strong class="x">Product Rental</strong></h2>
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
                <h3 class="uil-shopping-bag">Available Products</h3>
                <?php
                $product_types = ['renting'];
                foreach ($product_types as $type):
                    $product_query = "SELECT * FROM product WHERE product_type = '$type' AND status = 'available'";
                    $product_result = $conn->query($product_query);
                ?>
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php if ($product_result->num_rows > 0): ?>
                        <?php while ($product = $product_result->fetch_assoc()): ?>

<div class="col">
    <div class="card shadow-lg border-0 rounded-4 text-center"  
        style="box-shadow: 0 6px 14px rgba(247, 247, 247, 0.9); background: #1e1e2f; color: #fff; width: 80%; height:500px; margin: auto; font-family: 'Poppins', sans-serif;">
        
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
                <button type="button" class="btn btn-outline-light btn-lg px-3 py-1" onclick="decreaseQuantity(this)">-</button>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo (int) $product['stock']; ?>" 
                    class="form-control text-center mx-2" 
                    style="width: 60px; font-size: 18px; font-weight: 600; text-align: center;">
                <button type="button" class="btn btn-outline-light btn-lg px-3 py-1" onclick="increaseQuantity(this)">+</button>
            </div>

          <!-- Rent Now Button -->
<?php if (isset($_SESSION['username']) && $product['stock'] > 0): ?>
    <form action="#" method="POST" class="d-inline">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <button type="button" class="btn btn-warning w-100 fw-bold btn-lg rent-now-btn" 
            style="background: white; border-radius: 12px; font-size: 18px;"
            data-bs-toggle="modal" 
            data-bs-target="#rentalModal"
            data-product-id="<?php echo $product['product_id']; ?>"
            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
            data-product-price="<?php echo number_format($product['price'], 2); ?>">
            Rent Now
        </button>
    </form>
<?php else: ?>
    <button class="btn btn-secondary w-100 btn-lg" disabled 
        style="border-radius: 12px; font-size: 18px;">
        Out of Stock
    </button>
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
<!-- Rental Modal -->


<div class="modal fade" id="rentalModal" tabindex="-1" aria-labelledby="rentalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rentalModalLabel">Confirm Rental</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rentalForm">
                    <input type="hidden" id="product_id" name="product_id">
                    <p id="product_details"></p>

                    <!-- Address Selection -->
                    <label for="address">Select Address:</label>
                    <select id="address" name="address" class="form-control" required>
                        <option value="">-- Select Address --</option>
                        <?php foreach ($addresses as $address) { ?>
                            <option value="<?= $address['address_id'] ?>">
                                <?= htmlspecialchars($address['barangay'] . ', ' . $address['street'] . ' (Landmark: ' . $address['landmark'] . ')') ?>
                            </option>
                        <?php } ?>
                    </select>
                    <br>

                    <!-- Rental Days -->
                    <label for="rental_duration">Number of Days:</label>
                    
                    <input type="number" id="rental_duration" name="rental_duration" class="form-control" min="1" required>
                    <label for="rental_start">Start Date:</label>
                        <input type="date" id="rental_start" name="rental_start" class="form-control" required>
                        <br>

                
                    <br>

                    <button type="submit" class="btn btn-primary">Confirm Rent</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Adjust Rent Now buttons in product list -->
<button type="button" class="btn btn-primary rent-now-btn" 
    data-product-id="<?php echo $product['product_id']; ?>" 
    data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
    data-product-price="<?php echo number_format($product['price'], 2); ?>">
    Rent Now
</button>


<!-- JavaScript to trigger modal and fetch product data -->
<script>

document.addEventListener("DOMContentLoaded", function () {
    const rentNowButtons = document.querySelectorAll('.rent-now-btn');
    const rentalModal = new bootstrap.Modal(document.getElementById("rentalModal"));
    const rentalForm = document.getElementById("rentalForm");

    // Open modal and populate details
    rentNowButtons.forEach(button => {
        button.addEventListener("click", function () {
            const productId = this.getAttribute("data-product-id");
            const productName = this.getAttribute("data-product-name");
            const productPrice = this.getAttribute("data-product-price");

            document.getElementById("product_id").value = productId;
            document.getElementById("product_details").innerHTML = 
                `<strong>${productName}</strong><br>Price: ₱${productPrice}`;

            rentalModal.show();
        });
    });

    // Handle rental form submission
    rentalForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(rentalForm);

        fetch("/php/rental_process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            if (data.status === "success") {
                alert(data.message);
                rentalModal.hide();
                location.reload();
            } else {
                alert("Error: " + (data.message || "Something went wrong."));
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    });

    // Clear modal on close
    document.getElementById("rentalModal").addEventListener("hidden.bs.modal", function () {
        rentalForm.reset(); // Reset all input fields
        document.getElementById("product_details").innerHTML = "";
    });
});



        document.addEventListener("DOMContentLoaded", function () {
    let form = document.getElementById("add-to-cart-form");
    let button = document.querySelector(".add-to-cart-btn");

    if (form && button) {
        button.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(form);

            // Disable button to prevent multiple clicks
            button.disabled = true;

            fetch("rentnot.php", { // Call the new PHP handler
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
