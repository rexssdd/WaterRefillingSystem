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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

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

// Update session with cart items
$stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['cart'] = [];
while ($row = $result->fetch_assoc()) {
    $_SESSION['cart'][$row['product_id']] = $row['quantity'];
}

// Fetch orders
function fetch_orders($conn, $user_id, $status) {
    $query = "SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY order_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}

$order_completed_result = fetch_orders($conn, $user_id, 'completed');
$order_cancelled_result = fetch_orders($conn, $user_id, 'cancelled');

// Fetch rentals
function fetch_rentals($conn, $user_id, $status) {
    $query = "SELECT r.rental_id, r.rental_date, r.return_date, ro.total_price, ro.status 
              FROM renting r 
              JOIN rental_orders ro ON r.rental_id = ro.rental_id 
              WHERE r.user_id = ? AND ro.status = ? 
              ORDER BY r.rental_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}

$rental_completed_result = fetch_rentals($conn, $user_id, 'completed');
$rental_cancelled_result = fetch_rentals($conn, $user_id, 'cancelled');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - JZ Waters</title>

    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:  #353544; color: #d4af37; font-family: 'Poppins', sans-serif; }
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
      .main-content { margin-left: 270px; padding: 30px; }
        .navbar { display: inline-flex; justify-content: center; background-color: black; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); color: #d4af37; position: fixed; width: 100%; margin-left: -40px; margin-top: -30px;}
        
        .table-box { display: none; margin-top: 30px; } /* Hide tables by default */
        .table-box.active { display: block; } /* Show active table */
        .table-container button { margin-bottom: 15px; }
        .table { border: 1px solid #d4af37; border-radius: 5px; color: #d4af37; }
        .table th, .table td { padding: 10px; text-align: center; }
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

.table-container{
    margin-top: 100px;
}
.px-4{
    margin-left: 10px;
    max-width: 82%;
    margin-top: 5px;
}
.btn{
    margin-left: 60px;
}

.btn-toggle {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
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

    .btn-toggle:hover {
        transform: translateY(-3px);
    }

    h4{
        color: #FFFFF0;
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
        <a href="order.php" class="uil uil-heart-alt"><strong class="s">Orders</strong></a>
        <a href="orderhistory.php" class="uil uil-history active">Order History</a>
        <a href="settings.php" class="uil uil-cog">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout logout-btn" style=" color: white; position: absolute; bottom: 20px;">Logout</a>
        </div>
<div class="main-content">

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

    <div class="table-container">
        <div class="btn">
        <!-- Buttons to switch between tables -->
        <button class="btn btn-primary" onclick="showTable('completed-products')">Completed Product Orders</button>
        <button class="btn btn-danger" onclick="showTable('cancelled-products')">Cancelled Product Orders</button>
        <button class="btn btn-primary" onclick="showTable('completed-rentals')">Completed Rental Orders</button>
        <button class="btn btn-danger" onclick="showTable('cancelled-rentals')">Cancelled Rental Orders</button>
        </div>
        <!-- Completed Product Orders -->
        <div id="completed-products" class="table-box active">
            <h4>Completed Product Orders</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $order_completed_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td>P<?= number_format($order['total'], 2) ?></td>
                            <td><?= ucfirst($order['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Cancelled Product Orders -->
        <div id="cancelled-products" class="table-box">
            <h4>Cancelled Product Orders</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $order_cancelled_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td>P<?= number_format($order['total'], 2) ?></td>
                            <td><?= ucfirst($order['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Completed Rental Orders -->
        <div id="completed-rentals" class="table-box">
            <h4>Completed Rental Orders</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Rental Date</th>
                        <th>Return Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rental = $rental_completed_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $rental['rental_id'] ?></td>
                            <td><?= $rental['rental_date'] ?></td>
                            <td><?= $rental['return_date'] ?></td>
                            <td>P<?= number_format($rental['total_price'], 2) ?></td>
                            <td><?= ucfirst($rental['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Cancelled Rental Orders -->
        <div id="cancelled-rentals" class="table-box">
            <h4>Cancelled Rental Orders</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Rental Date</th>
                        <th>Return Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rental = $rental_cancelled_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $rental['rental_id'] ?></td>
                            <td><?= $rental['rental_date'] ?></td>
                            <td><?= $rental['return_date'] ?></td>
                            <td>P<?= number_format($rental['total_price'], 2) ?></td>
                            <td><?= ucfirst($rental['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function showTable(tableId) {
        // Hide all tables
        const tables = document.querySelectorAll('.table-box');
        tables.forEach(table => table.classList.remove('active'));

        // Show the selected table
        const table = document.getElementById(tableId);
        table.classList.add('active');
    }
</script>

</body>
</html>
