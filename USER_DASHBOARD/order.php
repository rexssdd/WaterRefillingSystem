<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];

$host = "localhost";
$username = "root";
$password = "";
$database = "wrsystem";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product orders
$order_query = "SELECT o.order_id, o.order_date, o.quantity, o.total, o.status, p.name AS product_name 
                FROM orders o 
                JOIN product p ON o.product_id = p.product_id 
                WHERE o.user_id = ? 
                AND (o.status = 'pending' OR o.status = 'preparing' OR o.status = 'to deliver')
                ORDER BY o.order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Fetch cancelled orders
$cancelled_order_query = "SELECT o.order_id, o.order_date, o.quantity, o.total, o.status, p.name AS product_name 
                           FROM orders o 
                           JOIN product p ON o.product_id = p.product_id 
                           WHERE o.user_id = ? 
                           AND o.status = 'cancelled'
                           ORDER BY o.order_date DESC";
$stmt = $conn->prepare($cancelled_order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cancelled_order_result = $stmt->get_result();



// Fetch rental orders (Fixed: Changed `rental_start` to `rental_date`)
$rental_query = "SELECT r.rental_id, r.rental_date, r.return_date, ro.rental_duration, 
                        ro.status AS rental_status, ro.created_at, 
                        p.name AS product_name, ro.total_price 
                 FROM renting r 
                 JOIN rental_orders ro ON r.rental_id = ro.rental_id 
                 JOIN product p ON r.product_id = p.product_id 
                 WHERE r.user_id = ? 
                 AND (ro.status = 'pending' OR ro.status = 'approved' OR  ro.status = 'ongoing')
                 ORDER BY ro.created_at DESC";
$stmt = $conn->prepare($rental_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rental_result = $stmt->get_result();

// Fetch cancelled rental orders
$cancelled_rental_query = "SELECT r.rental_id, r.rental_date, r.return_date, ro.rental_duration, 
                                  ro.status AS rental_status, ro.created_at, 
                                  p.name AS product_name, ro.total_price 
                           FROM renting r 
                           JOIN rental_orders ro ON r.rental_id = ro.rental_id 
                           JOIN product p ON r.product_id = p.product_id 
                           WHERE r.user_id = ? 
                           AND ro.status = 'cancelled'
                           ORDER BY ro.created_at DESC";
$stmt = $conn->prepare($cancelled_rental_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cancelled_rental_result = $stmt->get_result();

// decline order  query
$declined_order_query = "SELECT o.order_id, o.order_date, o.quantity, o.total, o.status, p.name AS product_name 
                         FROM orders o 
                         JOIN product p ON o.product_id = p.product_id 
                         WHERE o.user_id = ? 
                         AND o.status = 'declined'
                         ORDER BY o.order_date DESC";


$stmt = $conn->prepare($declined_order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$declined_order_result = $stmt->get_result();

// Fetch the number of unique products in the cart
$cart_query = "SELECT COUNT(DISTINCT product_id) AS total_products FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['total_products'] ?? 0;

// Fetch cart items with product details
$cart_query = "SELECT c.product_id, c.quantity, p.name AS product_name, p.price 
               FROM cart c 
               JOIN product p ON c.product_id = p.product_id 
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

   // Query to get the order status
   $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
   $stmt->bind_param("i", $order_id);
   $stmt->execute();
   $stmt->bind_result($order_status);
   $stmt->fetch();

// Determine if cancel button should be enabled
$cancel_disabled = ($order_status !== 'pending') ? 'disabled' : 'available';

// Update session with cart items
$stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['cart'] = [];
while ($row = $result->fetch_assoc()) {
    $_SESSION['cart'][$row['product_id']] = $row['quantity'];


}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders & Rentals</title>
   <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
                               <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
        .container { text-align: center; margin-right: 200px; }
        .btn-toggle { margin: 10px; }
        .hidden { display: none; }
        .main-content { margin-left: 270px; padding: 30px; }
        .product-container { display: flex; flex-wrap: wrap; gap: 30px; }
    
        .list-group{
            max-width: 135vh;
            gap: 15px;
        }
        .content{
            display: flexbox list-item ;
            flex-wrap: nowrap;
            justify-content: center;
            margin-top: 100px;
        }
        .content h2{
            align-self: center;
            gap: 30px;
        }
        body {
        background-color:    #353544;
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
    strong{
        margin-left: 10px;
        color: white;
        font-size: 18px;
    }
    a{
        color: white;
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
        .log{
            color:rgb(243, 243, 243);
        }

        h2{
            font-size: 44px;
        }

        .top{
            border-color: cadetblue;
            margin-left: -580px;
        }
        
        h3{
            font-size: 34px;
        }

        .toast-container {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1050;
        }

        table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    background: #2c2c2c;
    color: #d4af37;
    font-family: 'Poppins', sans-serif;
    text-align: left;
}

thead {
    background: #1e1e1e;
    color: #d4af37;
    text-transform: uppercase;
    letter-spacing: 1px;
}

thead th {
    padding: 12px;
    border-bottom: 2px solid #d4af37;
}

tbody tr:nth-child(odd) {
    background: #333;
}

tbody tr:nth-child(even) {
    background: #2a2a2a;
}

tbody tr:hover {
    background: #444;
    transition: background 0.3s ease-in-out;
}

td {
    padding: 12px;
    border-bottom: 1px solid #444;
}

th, td {
    min-width: 120px;
    text-align: center;
}

.btn {
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 4px;
    text-transform: uppercase;
    transition: 0.3s;
}

.btn-danger {
    background: red;
    color: white;
    border: none;
}

.btn-danger:hover {
    background: darkred;
}
.s{
    color: gold;
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

    .navbar {
            display: flex;
            justify-content: center;
            background-color: black;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            color: #d4af37;
            position: fixed;
            width: calc(100% - 270px);
            top: 0;
            left: 270px;
            padding: 8px;
            text-align: center;
            margin-top: 20px;
        }

        .top{
            margin-left: 40px;
            word-spacing: 50px;
            margin-top: 100px;
           
        }
        .btn-toggle{
            border-radius: 24px;
            padding: 8px;
            font-weight: bold;
        
        }
       
        .table-responsive{
            margin-top: 40px;
        }
        .uil-heart-alt{
            font-size: 24px;
        }

        .btn-toggle {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-toggle:hover {
        transform: translateY(-3px);
    }

       /* Button colors */
       .btn-primary { background-color: #284387; color: #FFFFF0; }
    .btn-secondary { background-color: #284387; color: #FFFFF0; }
    .btn-danger { background-color: #284387; color: #FFFFF0; }
    .btn-warning { background-color: #284387; color: #FFFFF0; }

    /* Hover effects */
    .btn-primary:hover { background-color: #8F9BF8; }
    .btn-secondary:hover { background-color: #A1AEEA; }
    .btn-danger:hover { background-color: #B8BDF2; }
    .btn-warning:hover { background-color: #C9D4FB; }
    </style>
</head>
<body>
<div class="sidebar show" id="sidebar">
        <a href="#" class="nav_logo">
            <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo" style="width: 40px; margin-right: 10px;">
            <strong style="font-size: 24px;">Jz Waters</strong>
        </a>
        <a href="dashboard.php" class="uil uil-box ">Products</a>
        <a href="cart.php" class="uil uil-shopping-cart ">Cart(<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        <a href="rental.php" class="uil uil-house-user">Product Rental</a>
        <a href="order.php" class="uil uil-heart-alt active"><strong class="s">Orders</strong></a>
        <a href="orderhistory.php" class="uil uil-history">Order History</a>
        <a href="settings.php" class="uil uil-cog">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout logout-btn" style=" color: white; position: absolute; bottom: 20px;">Logout</a>
    </div>
   

<div class="container mt-5">
<nav class="navbar d-flex align-items-center justify-content-between px-4" 
    style="color: white; background: #515151; border-radius: 50px; height: 60px;">
    
    <!-- Left: Menu Icon -->
    <i class="uil uil-bars fs-3" id="menu-icon" style="cursor: pointer;"></i>

    <!-- Center: Page Title -->
    <h2 id="page-title" class="uil uil-heart-alt m-0 " style="font-size: 24px;" >
        <strong class="x">Orders</strong>
    </h2>

    <!-- Right: User Icon with Dropdown -->
    <div class="user-container position-relative">
        <i class="uil uil-user fs-3 user-icon" id="userIcon" style="color: white; cursor: pointer;"></i>
        <div class="dropdown-menu position-absolute end-0 mt-2 p-2 bg-white shadow rounded" 
            id="dropdownMenu" style="display: none;">
            <a href="settings.php" class="d-block text-dark text-decoration-none p-2">Settings</a>
            <a href="/php/logout.php" onclick="confirmLogout()" class="d-block text-dark text-decoration-none p-2">Logout</a>
        </div>
    </div>
</nav>


<div class="top">
    <button  class="btn btn-primary btn-toggle" onclick="showSection('orders')">Orders</button>
    <button  class="btn btn-secondary btn-toggle" onclick="showSection('rentals')">Rental Orders</button>
    <button  class="btn btn-danger btn-toggle" onclick="showSection('cancelled_orders')">Cancelled Orders</button>
    <button  class="btn btn-warning btn-toggle" onclick="showSection('cancelled_rentals')">Cancelled Rentals</button>
    <button  class="btn btn-secondary btn-toggle" onclick="showSection('declined_orders')">Declined Orders</button>
</div>

<!-- Orders Section -->

<div id="orders" class="table-responsive">
    <h3>Your Orders</h3>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Order ID</th><th>Order Date</th><th>Product</th><th>Quantity</th>
                <th>Total</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $order_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['order_date'] ?></td>
                    <td><?= $order['product_name'] ?></td>
                    <td><?= $order['quantity'] ?></td>
                    <td><?= $order['total'] ?></td>
                    <td><?= $order['status'] ?></td>
                    <td>
                        <!-- Cancel Button (Only show if status is 'pending') -->
                        <?php if ($order['status'] === 'pending'): ?>
                            <form action="/php/cancel_order.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <button type="submit" style="background-color: red; color: #FFFFF0;" class="btn btn-danger">Cancel Order</button>
                            </form>
                        <?php else: ?>
                            <!-- Button Hidden if status is not 'pending' -->
                            <span class="btn btn-danger" style="visibility: hidden;">Cancel Order</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


   <!-- Rentals Section (hidden by default) -->
<div id="rentals" class="table-responsive hidden">
    <h3>Your Rentals</h3>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Rental ID</th><th>Rental Date</th><th>Product</th><th>Duration</th>
                <th>Status</th><th>Total Price</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($rental = $rental_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $rental['rental_id'] ?></td>
                    <td><?= $rental['rental_date'] ?></td>
                    <td><?= $rental['product_name'] ?></td>
                    <td><?= $rental['rental_duration'] ?> days</td>
                    <td><?= $rental['rental_status'] ?></td>
                    <td><?= $rental['total_price'] ?></td>
                    <td>
                        <!-- Cancel Button (enabled only if status is 'pending') -->
                        <form action="/php/cancel_order.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= $rental['rental_id'] ?>">
                            <!-- Only show the cancel button if the rental status is 'pending' -->
                            <?php if ($rental['rental_status'] == 'pending'): ?>
                                <button type="submit" class="btn btn-danger">Cancel Rental</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


    <!-- Cancelled Orders Section (hidden by default) -->
    <div id="cancelled_orders" class="table-responsive hidden">
        <h3>Cancelled Orders</h3>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Order ID</th><th>Order Date</th><th>Product</th><th>Quantity</th>
                    <th>Total</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cancelled_order = $cancelled_order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $cancelled_order['order_id'] ?></td>
                        <td><?= $cancelled_order['order_date'] ?></td>
                        <td><?= $cancelled_order['product_name'] ?></td>
                        <td><?= $cancelled_order['quantity'] ?></td>
                        <td><?= $cancelled_order['total'] ?></td>
                        <td><?= $cancelled_order['status'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

   <!-- Declined Orders Section (hidden by default) -->
   <div id="declined_orders" class="table-responsive hidden">
    <h3>Declined Orders</h3>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Order ID</th><th>Order Date</th><th>Product</th><th>Quantity</th>
                <th>Total</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($declined_order = $declined_order_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $declined_order['order_id'] ?></td>
                    <td><?= $declined_order['order_date'] ?></td>
                    <td><?= $declined_order['product_name'] ?></td>
                    <td><?= $declined_order['quantity'] ?></td>
                    <td><?= $declined_order['total'] ?></td>
                    <td><?= $declined_order['status'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


    <!-- Cancelled Rentals Section (hidden by default) -->
    <div id="cancelled_rentals" class="table-responsive hidden">
        <h3>Cancelled Rentals</h3>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Rental ID</th><th>Rental Date</th><th>Product</th><th>Duration</th>
                    <th>Status</th><th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cancelled_rental = $cancelled_rental_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $cancelled_rental['rental_id'] ?></td>
                        <td><?= $cancelled_rental['rental_date'] ?></td>
                        <td><?= $cancelled_rental['product_name'] ?></td>
                        <td><?= $cancelled_rental['rental_duration'] ?> days</td>
                        <td><?= $cancelled_rental['rental_status'] ?></td>
                        <td><?= $cancelled_rental['total_price'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3">
    <!-- Toast Example -->
    <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Action was successful!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

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
   function showSection(sectionId) {
    const sections = ['orders', 'rentals', 'cancelled_orders', 'cancelled_rentals', 'declined_orders'];

    // Hide all sections
    sections.forEach(function(section) {
        const sectionElement = document.getElementById(section);
        if (sectionElement) {
            sectionElement.classList.add('hidden');
        }
    });

    // Show the selected section
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.remove('hidden');
    }
}


function showSection(sectionId) {
    document.querySelectorAll('.table-responsive').forEach(section => {
        section.classList.add('hidden');
    });
    document.getElementById(sectionId).classList.remove('hidden');
}

// Show Orders section by default
window.onload = function() {
    showSection('orders');
};

    <?php
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        $message = $notification['message'];
        $type = $notification['type'];

        // JavaScript alert based on session notification type
        echo "
            alert('$message');
        ";

        // Clear the session notification after showing the alert
        unset($_SESSION['notification']);
    }
    ?>

document.getElementById("menu-icon").addEventListener("click", function () {
            let sidebar = document.getElementById("sidebar");
            let mainContent = document.querySelector(".main-content");
            sidebar.classList.toggle("hide");
            mainContent.classList.toggle("expanded");
        });
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
