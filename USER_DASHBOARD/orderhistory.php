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
$user_id = 1; // Replace with actual logged-in user ID


// Fetch cart count
$cart_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['total_items'] ?? 0;

// Fetch order history
$order_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$order_result = $conn->query($order_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: black; color: #d4af37; font-family: 'Poppins', sans-serif; }
        .container { margin-top: 50px; }
        .table { color: #d4af37; }
        body { background: black;  color: #d4af37; font-family: 'Poppins', sans-serif; }
        .sidebar {  background-color: black; margin-top: -50px; height: 100vh; width: 260px; position: fixed; padding-top: 20px; color: #d4af37; box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1); }
        .sidebar a { color: #d4af37; padding: 15px; text-decoration: none; display: block; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(230, 227, 227, 0.2); border-radius: 5px; }
        .main-content { margin-left: 270px; padding: 30px; }
        .navbar {     display: inline-flex;justify-content: center; background-color: black; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); color: #d4af37;  position: fixed; width: 100%; margin-left: -40px; margin-top: -30px;}
        .product-container { display: flex; flex-wrap: wrap; gap: 30px; }
        .product-item { margin-top: 500px;background: #333; padding: 15px; max-width: 30%; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(255, 255, 255, 0.59); color: #d4af37; }
        .product-image { width: 100%; height: 200px; object-fit: contain; border-radius: 5px; }
        
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
           .h2{
           color: white;
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
            
        <a class="uil uil-box log" href="dashboard.php"  onclick="showProducts()"><strong class="x">Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart log">
             Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        <a href="rental.php" class="uil uil-history log" onclick="showOrderHistory()">Product Rental</a>
        <a href="order.php" class="uil uil-history log" onclick="showOrderHistory()">Orders</a>
        <a href="orderhistory.php" class="uil uil-history active" onclick="showOrderHistory()">Order History</a>
        <a href="settings.php" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" style ="margin-top: 450px; border-radius:20px; background-color:red; color: white;"  class="uil uil-signout log1" onclick="confirmLogout()">Logout</a>
    </div>
    <div class="container">
        <h2>Order History</h2>
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
                <?php while ($order = $order_result->fetch_assoc()): ?>
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
</body>
</html>
