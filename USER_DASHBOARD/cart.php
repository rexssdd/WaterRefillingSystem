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


$sql_total_price = "SELECT SUM(p.price * ci.quantity) AS total_price 
                    FROM cart ci 
                    JOIN product p ON ci.product_id = p.product_id 
                    WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql_total_price);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_price = 0;

// Check if user has an address
$sql_check_address = "SELECT * FROM address WHERE user_id = ?";
$stmt = $conn->prepare($sql_check_address);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_address = $result->num_rows > 0;


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

// Fetch cart items
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

// Calculate total price
$total_price = array_reduce($cartItems, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);

// Fetch user addresses
$addresses = [];
$stmt = $conn->prepare("SELECT address_id, barangay, street, landmark , note FROM address WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = $result->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - POS System</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
                               <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
       body {
            background-color: #353544;
            color:white;
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            background-color: #1e1e2f;
            height: 100vh;
            width: 260px;
            position: fixed;
            padding-top: 20px;
            color: white;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar a {
            color: white;
            padding: 15px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
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
        .main-content {
            margin-left: 270px;
            padding: 30px;
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
        }
        .cart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 80px;
        }
        .cart-item {
            background-color: #222;
            border-radius: 10px;
            padding: 15px;
            width: 250px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 5px;
        }
        .btn-danger {
            background-color: red;
            color: white;
            border-radius: 5px;
        }
        .place-order-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }

        .logo{
        width: 40px;  /* Adjust size as needed */
        height: auto;
        gap: 2px;
        margin-right: 20px; 
        }

        
        .log{
            
            color:rgb(243, 243, 243);
        }
        .cart-summary {
        position: fixed;
        bottom: 50px;
        left: 57%;
        transform: translateX(-50%);
        width: 1000px;
        background: #222;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2);
        text-align: center;
    }
    .cart-summary h4 {
        margin-bottom: 10px;
    }
    .place-order-btn {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }
    .ss{
     display: flex;
     justify-content: center;
     margin-top:50px;
     margin-left: 30px;
      max-width:70%;
    }
    .n{
      color: gold;
      font-size: 18px;
    }
    /* General Modal Styling */
.modal {
  display: none; /* Hidden by default */
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  color: gold;
  align-items: center;
  justify-content: center;
}

.modal-dialog {
  background: #1e1e2f;
  border-radius: 10px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0px 5px 15px rgba(241, 240, 240, 0.51);
  overflow: hidden;
}

.modal-backdrop{
  --bs-backdrop-zindex: 1050;
  --bs-backdrop-bg: #000;
  --bs-backdrop-opacity: 0;
  z-index: 0;
}

/* Header Styling */
.modal-header {
  background:rgb(36, 40, 44);
  color: black;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.2em;
  font-weight: bold;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5em;
  color: black;
  cursor: pointer;
}

/* Modal Body */
.modal-body {
  padding: 20px;
  font-size: 1em;
  color: white;
}

/* Form Styling */
.form-group {
  margin-bottom: 15px;
}

label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
}

select.form-control {
  appearance: none;
  background: #f8f9fa;
  cursor: pointer;
}

/* Cart Items Styling */
.list-group {
  list-style: none;
  padding: 0;
}

.list-group-item {
  background: #f8f9fa;
  padding: 10px;
  margin: 5px 0;
  border-radius: 5px;
  border-left: 6px solid #007bff;
}

/* Confirm Order Button */
#confirmOrderBtn {
  width: 100%;
  padding: 12px;
  background: #FFFFF0;
  color: black;
  border: none;
  font-size: 1em;
  font-weight: bold;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s ease;
}

#confirmOrderBtn:hover {
  background:#284387;
  color:  #FFFFF0;
}

/* Update Address Button */
#updateAddressBtn {
  display: block;
  margin-top: 10px;
  background: #284387;
  color: #FFFFF0;
  border: none;
  padding: 10px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

#updateAddressBtn:hover {
  background: #e0a800;
}

/* Note Styling */
.note {
  font-size: 0.9em;
  color: #666;
  margin-top: 10px;
}

/* Responsive Design */
@media (max-width: 600px) {
  .modal-dialog {
    max-width: 90%;
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
    .uil-shopping-cart{
        font-size: 24px;
    }

    /* General Modal Styling */
.modal {
  background-color: #1e1e2f;
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
}

.modal-dialog {
  background: #1e1e2f;
  width: 90%;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0px 4px 10px rgba(233, 229, 229, 0.47);
  animation: fadeIn 0.3s ease-in-out;
}

.modal-header {
  background: #FFFFF0;
  color: black;
  padding: 15px;
  font-size: 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.modal-body {
  padding: 20px;
}

.modal-content {
    background: #1e1e2f;
    color: gold;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(202, 200, 200, 0.3);
}
/* Close Button */
.close-btn {
  background: none;
  border: none;
  font-size: 22px;
  color: black  ;
  cursor: pointer;
}

/* Address List */
.list-group-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

.address-item {
  cursor: pointer;
  transition: background 0.2s;
}

.address-item:hover {
  background: #f8f9fa;
}

/* Buttons */
button {
  background: #284387;
  color: #FFFFF0;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.edit-address-btn {
  background: #284387;
  color: #FFFFF0;
}

.delete-address-btn {
  background: #dc3545;
  color: white;
}

/* Edit Address Form */
form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

label {
  font-weight: bold;
}

input[type='text'] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 16px;
}

button[type='submit'] {
  background: #FFFFF0;
  color: black;
  padding: 10px;
  font-size: 16px;
  border-radius: 5px;
}
button[type='submit'] :hover {
  background:#284387;
    color:  #FFFFF0;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.x{
        font-size: 24px;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="#" class="nav_logo">
            <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo">
            <strong style="font-size: 24px;" class="Jz_Waters">Jz Waters</strong>
        </a>
        <a href="dashboard.php" class="uil uil-box">Products</a>
        <a href="cart.php" class="uil uil-shopping-cart active"><strong class="n" >Cart(<span id="cart-count"><?php echo $cart_count; ?></span>)</strong></a>
        <a href="rental.php" class="uil uil-house-user">Product Rental</a>
        <a href="order.php" class="uil uil-heart-alt">Orders</a>
        <a href="orderhistory.php" class="uil uil-history">Order History</a>
        <a href="settings.php" class="uil uil-cog">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout logout-btn" style="position: absolute; bottom: 20px;">Logout</a>
    </div>
      
    <!-- Main Content -->
       <div class="main-content">
       <nav class="navbar" style=" margin-top: 20px; color:white; background:#515151; border-radius: 50px; display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
        <!-- Menu Icon on the Left -->
        <i class="uil uil-bars fs-3" id="menu-icon" style="margin-left: 20px;"></i>
        
        <!-- Cart Title in the Center -->
        <h2 id="page-title" class="uil uil-shopping-cart" style="margin-left: 700px ; text-align: center;"><strong class="x">Cart</strong></h2>
        
        <!-- User Icon on the Right -->
        <div class="user-container" style="margin-left: auto; margin-right: 20px; position: relative;">
            <i class="uil uil-user fs-3 user-icon" id="userIcon" style="color:white;"></i>
            <div class="dropdown-menu" id="dropdownMenu" style="position: absolute; right: 0; display: none; background:#515151 ; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                <a href="settings.php">Settings</a>
                <a href="/php/logout.php" onclick="confirmLogout()">Logout</a>
            </div>
        </div>
    </nav>

          <?php if (isset($_SESSION['message'])): ?>
                  <div id="notification" style="
                      background-color: #4caf50; 
                      color: white; 
                      padding: 15px; 
                      position: fixed; 
                      top: 10px; 
                      right: 10px; 
                      width: 30%;
                      border-radius: 5px; 
                      z-index: 1000;">
                      <?= htmlspecialchars($_SESSION['message']); ?>
                  </div>
                  <script>
                      setTimeout(() => {
                          document.getElementById('notification').style.display = 'none';
                      }, 2000); // 2 seconds
                  </script>
                  <?php unset($_SESSION['message']); ?>
              <?php endif; ?>

                  
    
              <section class="cart mt-4">
              <div id="cart-content" class="d-flex flex-wrap justify-content-center gap-4" style="margin-top: 80px; margin-left: -130px;">
    <?php
    $stmt = $conn->prepare("
        SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.photo
        FROM cart c
        JOIN product p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    $total_price1= 0; // Initialize total price

    if ($cart_result->num_rows > 0): 
        while ($cart_item = $cart_result->fetch_assoc()): 
            $subtotal = $cart_item['quantity'] * $cart_item['price'];
            $total_price1 += $subtotal;
    ?>
        <!-- Cart Item Card -->
        <div class="card shadow-lg border-0 rounded-3 text-center" 
            style="box-shadow: 0 4px 12px rgba(255, 255, 255, 0.6); background: #1e1e2f; color: #fff; width: 20%; height: 550px; font-family: 'Poppins', sans-serif;">
            
            <div class="card-img-top d-flex justify-content-center align-items-center" 
                style="height: 160px; height:200px; background: rgba(255, 255, 255, 0.15); border-radius: 12px 12px 0 0; overflow: hidden;">
                <img src="<?= htmlspecialchars($cart_item['photo']); ?>" 
                    alt="Product Image" class="img-fluid" 
                    style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 8px;">
            </div>

            <div class="card-body p-3 d-flex flex-column justify-content-between">
    <div>
        <h4 class="fw-bold" style="color: #d4af37; font-size: 24px;"> 
            <?= htmlspecialchars($cart_item['name']); ?> 
        </h4>
        <p class="mb-2" style="font-size: 20px; font-weight: 500; color: #f8f8f8;">
            Price: <span style="color: #d4af37;">₱<?= number_format($cart_item['price'], 2); ?></span>
        </p>
        <p>Total: <span id="total_<?= $cart_item['cart_id']; ?>">₱<?= number_format($subtotal, 2); ?></span></p>
    </div>

    <!-- Quantity Section at Bottom Center -->
    <div class="d-flex justify-content-center align-items-center mt-auto">
        <button class="btn btn-outline-light btn-lg px-3 py-1" 
            onclick="updateQuantity(<?= $cart_item['cart_id']; ?>, -1)">-</button>
        <input type="number" id="quantity_<?= $cart_item['cart_id']; ?>"
            class="form-control text-center mx-2"
            value="<?= $cart_item['quantity']; ?>" 
            min="1" 
            oninput="this.value = Math.max(this.value, 1)"
            data-cart-id="<?= $cart_item['cart_id']; ?>" 
            data-product-id="<?= $cart_item['product_id']; ?>" 
            style="width: 60px; font-size: 20px; font-weight: 600; text-align: center;">
        <button class="btn btn-outline-light btn-lg px-3 py-1" 
            onclick="updateQuantity(<?= $cart_item['cart_id']; ?>, 1)">+</button>
    </div>
</div>


            <div class="card-footer bg-transparent border-0 pb-3">
                <a href="cart.php?remove_cart_id=<?= $cart_item['cart_id']; ?>" 
                    class="btn btn-danger w-100 fw-bold btn-lg" 
                    style="border-radius: 10px; font-size: 20px;">Remove</a>
            </div>
        </div>
    <?php endwhile; ?>
    <?php else: ?>
        <div class='alert alert-warning text-center w-50' style='font-size: 20px;  font-family: "Poppins", sans-serif; margin-left: 100px; margin-top: 20px;'>
            Your cart is empty.
        </div>
    <?php endif; ?>
</div>

<!-- Cart Summary -->
<div class="cart-summary text-center mt-4">
    <h4>Total Price: ₱<span id="total-price"><?= number_format($total_price, 2); ?></span></h4>
    <button class="btn btn-success btn-lg mt-2" id="placeOrderBtn" data-bs-toggle="modal" data-bs-target="#placeOrderModal">
        Place Order
    </button>
</div>

</section>  

  <div id="notification" style="
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 15px;
    border-radius: 5px;
    z-index: 1000;
  ">
    Notification message here!
  </div>



  <section class="home" id="home">
    <!-- Place Order Modal -->
    <div class="modal" id="placeOrderModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <h5 class="modal-title">Confirm Your Order</h5>
            <button type="button" class="close-btn" id="close">&times;</button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <form id="orderForm">
              <!-- Cart Items -->
              <h6>Your Cart:</h6>
              <ul id="cartItems" class="list-group">
                <?php 
                  $totalPrice = 0;
                  foreach ($cartItems as $item) {
                    $productName = htmlspecialchars($item['product_name']);
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $subtotal = $quantity * $price;
                    $totalPrice += $subtotal;
                ?>
                  <li class="list-group-item">
                    <?= $productName ?> - <?= $quantity ?> ₱<?= number_format($price, 2) ?>
                  </li>
                <?php } ?>
              </ul>

              <!-- Total Price -->
              <div class="form-group">
                <label for="totalPrice"><strong>Total Price:</strong></label>
                <input type="text" id="totalPrice" name="totalPrice" value="₱<?= number_format($totalPrice, 2) ?>" readonly class="form-control">
              </div>

              <!-- Delivery Address -->
              <div class="form-group">
                <label for="address">Select Address:</label>
                <select id="address" name="address" class="form-control" required onchange="updateAddressNote()">
                  <option value="">-- Select Address --</option>
                  <?php foreach ($addresses as $address) { 
                      $note = isset($address['note']) && $address['note'] !== '' ? htmlspecialchars($address['note']) : '';
                  ?>
                    <option value="<?= $address['address_id'] ?>" data-note="<?= $note ?>">
                      <?= htmlspecialchars($address['barangay'] . ', ' . $address['street'] . ' (Landmark: ' . $address['landmark'] . ')') ?>
                    </option>
                  <?php } ?>
                </select>
                <button type="button" id="updateAddressBtn">Update Address</button>
              </div>

              <!-- Address Note Field -->
              <div class="form-group">
                <label for="addressNote"><strong>Address Note:</strong></label>
                <input type="text" id="addressNote" name="addressNote" class="form-control" placeholder="Select an address to view or edit the note">
              </div>

              <!-- Payment Method -->
              <div class="form-group">
                <label for="paymentMethod">Payment Method:</label>
                <select id="paymentMethod" name="paymentMethod" class="form-control" required>
                  <option value="gcash">GCash</option>
                  <option value="cod">Cash on Delivery</option>
                </select>
              </div>

                <!-- Confirm Order -->
                <button type="submit" id="confirmOrderBtn">Confirm Order</button>

              <!-- General Note -->
              <p class="note" style="margin-top: 10px; font-size: 0.9em; color:  #FFFFF0;">
                <strong>Note:</strong> Please double-check your address and payment method before confirming your order.
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>




<!-- Manage Address Modal -->
<div class="modal" id="manageAddressModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Manage Addresses</h5>
        <button type="button" class="close-btn" id="closeManageAddressModal">&times;</button>
      </div>

      <div class="modal-body">
        <ul id="addressList" class="list-group">
          <?php if (isset($addresses) && is_array($addresses) && count($addresses) > 0): ?>
            <?php foreach ($addresses as $address): ?>
              <li class="list-group-item address-item" data-id="<?= htmlspecialchars($address['address_id']) ?>">
                <?= htmlspecialchars($address['barangay'] . ', ' . $address['street'] . ' (Landmark: ' . $address['landmark'] . ')') ?>
                <button class="edit-address-btn" 
                        data-id="<?= $address['address_id'] ?>" 
                        data-barangay="<?= $address['barangay'] ?>" 
                        data-street="<?= $address['street'] ?>" 
                        data-landmark="<?= $address['landmark'] ?>">
                  Edit
                </button>
                <button class="delete-address-btn" data-id="<?= $address['address_id'] ?>">Remove</button>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item">No address found.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Edit Address Modal -->
<div class="modal" id="editAddressModal" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Address</h5>
        <button type="button" id="closeEditAddressModal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="editAddressForm" method="POST">
          <input type="hidden" id="editAddressId" name="address_id">
          <label for="editBarangay">Barangay:</label>
          <input type="text" id="editBarangay" name="barangay" required>
          <label for="editStreet">Street:</label>
          <input type="text" id="editStreet" name="street" required>
          <label for="editLandmark">Landmark:</label>
          <input type="text" id="editLandmark" name="landmark" required>
          <button type="submit">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>


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
function confirmLogout() {
         var logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));
         logoutModal.show();
            }

function updateQuantity(cartId, change) {
    let quantityInput = document.getElementById("quantity_" + cartId);
    let newValue = parseInt(quantityInput.value) + change;
    if (newValue >= 1) {
        quantityInput.value = newValue;
    }
}

  document.addEventListener("DOMContentLoaded", function () {
    const orderForm = document.getElementById("orderForm");

    if (orderForm) {
        orderForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            // Get total price
            const totalPriceInput = document.getElementById("totalPrice");
            const totalPrice = parseFloat(totalPriceInput.value.replace('₱', '').trim());

            if (isNaN(totalPrice) || totalPrice <= 0) {
                alert("Invalid total price. Please check your order.");
                return;
            }

            const formData = new FormData(orderForm);

            try {
                const response = await fetch("/php/place_order.php", {
                    method: "POST",
                    body: formData,
                });

                const text = await response.text();
                console.log('Raw response:', text); // For debugging

                let data;
                try {
                    data = JSON.parse(text);
                } catch (jsonError) {
                    throw new Error("Invalid JSON response from server.");
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Failed to place order.");
                }

                alert(data.message || "Order placed successfully!");
                location.reload();
            } catch (error) {
                console.error("Error:", error.message);
                alert(error.message || "An error occurred while placing your order.");
            }
        });
    }
});


  function updateAddressNote() {
    const addressSelect = document.getElementById('address');
    const noteInput = document.getElementById('addressNote');
    const selectedOption = addressSelect.options[addressSelect.selectedIndex];
    const note = selectedOption.getAttribute('data-note');

    noteInput.value = note || ''; // Show empty string if no note is available
  }

  function fetchAddressNote() {
    const addressSelect = document.getElementById('address');
    const selectedOption = addressSelect.options[addressSelect.selectedIndex];
    const note = selectedOption.dataset.note || 'No note available';
    document.getElementById('addressNote').value = note;
  }

// Show Place Order modal
document.getElementById('placeOrderBtn').addEventListener('click', () => {
  document.getElementById('placeOrderModal').style.display = 'block';
});

// Close Place Order modal
document.getElementById('close').addEventListener('click', () => {
  document.getElementById('placeOrderModal').style.display = 'none';
});

// Show Manage Address modal
document.getElementById('updateAddressBtn').addEventListener('click', () => {
  document.getElementById('manageAddressModal').style.display = 'block';
});

// Close Manage Address modal
document.getElementById('closeManageAddressModal').addEventListener('click', () => {
  document.getElementById('manageAddressModal').style.display = 'none';
});

// Select address and update dropdown
document.querySelectorAll('.address-item').forEach(item => {
  item.addEventListener('click', () => {
    const selectedAddress = item.textContent.trim();
    const addressId = item.getAttribute('data-id');

    const addressDropdown = document.getElementById('address');
    addressDropdown.innerHTML = `<option value="${addressId}">${selectedAddress}</option>`;
    document.getElementById('manageAddressModal').style.display = 'none';
  });
});


function updateQuantity(cartId, change) {
    let quantityInput = document.getElementById("quantity_" + cartId);
    if (!quantityInput) {
        console.error(`Quantity input not found for cartId: ${cartId}`);
        return;
    }

    let maxStock = parseInt(quantityInput.dataset.stock);
    let productId = quantityInput.dataset.productId;
    let newQuantity = parseInt(quantityInput.value) + change;

    // Prevent negative or zero values
    if (newQuantity < 1) {
        alert("Quantity must be at least 1.");
        newQuantity = 1;
    }

    // Prevent exceeding stock
    if (newQuantity > maxStock) {
        alert("Not enough stock available!");
        newQuantity = maxStock;
    }

    fetch('/php/update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart_id: cartId,
            product_id: productId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            quantityInput.value = newQuantity; // Update input field
            location.reload();
            // Update total item price if element exists
            let totalElement = document.getElementById("total_" + cartId);
            if (totalElement) {
                totalElement.innerText = `₱${data.new_total}`;
            } else {
                console.warn(`Element total_${cartId} not found`);
            }

            // Update overall cart total if element exists
            let totalPriceElement = document.getElementById("total-price");
            if (totalPriceElement) {
                totalPriceElement.innerText = `${data.total_price}`;
            } else {
                console.warn("Total price element not found");
            }
        } else {
            alert(data.message);
            quantityInput.value = parseInt(quantityInput.value); // Reset to valid value
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error updating quantity. Please try again.");
    });
}





document.addEventListener('DOMContentLoaded', () => {
  // Show Edit Address modal
  document.getElementById('editAddressForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    fetch('update_address.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Address updated successfully!');
          location.reload();
        } else {
          alert('Failed to update address.');
        }
      }).catch(error => console.error('Error:', error));
  });

  // Close Edit Address modal
  document.getElementById('closeEditAddressModal').addEventListener('click', () => {
    document.getElementById('editAddressModal').style.display = 'none';
  });

  // Handle address list interactions
  const addressList = document.getElementById('addressList');

  if (addressList) {
    addressList.addEventListener('click', (e) => {
      if (e.target.classList.contains('edit-address-btn')) {
        const button = e.target;
        document.getElementById('editAddressId').value = button.dataset.id;
        document.getElementById('editBarangay').value = button.dataset.barangay;
        document.getElementById('editStreet').value = button.dataset.street;
        document.getElementById('editLandmark').value = button.dataset.landmark;

        document.getElementById('editAddressModal').style.display = 'block';
      }

      if (e.target.classList.contains('delete-address-btn')) {
        const addressId = e.target.dataset.id;
        if (confirm('Are you sure you want to remove this address?')) {
          fetch('delete_address.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `address_id=${addressId}`
          }).then(response => response.text())
            .then(data => {
              if (data.trim() === 'success') {
                alert('Address removed successfully!');
                e.target.closest('li').remove();
              } else {
                alert('Failed to remove address.');
              }
            }).catch(error => console.error('Error:', error));
        }
      }
    });
  } else {
    console.error('Error: #addressList not found.');
  }
});




// Select an address from the Manage Address modal
document.querySelectorAll('.address-item').forEach(item => {
  item.addEventListener('click', () => {
    const selectedAddress = item.textContent.trim();
    const addressId = item.getAttribute('data-id');

    const addressDropdown = document.getElementById('address');
    addressDropdown.innerHTML = `<option value="${addressId}">${selectedAddress}</option>`;
    document.getElementById('manageAddressModal').style.display = 'none';
  });
});


    document.getElementById('addressList').addEventListener('click', (e) => {
        if (e.target.classList.contains('select-address')) {
            const selectedAddress = e.target.getAttribute('data-address');
            const selectedAddressId = e.target.getAttribute('data-address-id');
            const addressDropdown = document.getElementById('address');
            addressDropdown.innerHTML = `<option value="${selectedAddressId}" selected>${selectedAddress}</option>`;
            const manageAddressModal = bootstrap.Modal.getInstance(document.getElementById('manageAddressModal'));
            manageAddressModal.hide();
        }
    });

    document.getElementById('addAddressForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const barangay = document.getElementById('barangay').value;
        const street = document.getElementById('street').value;
        const landmark = document.getElementById('landmark').value;
        const newAddress = `${barangay}, ${street} (Landmark: ${landmark})`;
        const newAddressId = Date.now();

        const addressList = document.getElementById('addressList');
        addressList.innerHTML += `<li class="list-group-item">
            ${newAddress}
            <button class="btn btn-sm btn-secondary select-address" data-address-id="${newAddressId}" data-address="${newAddress}">Select</button>
        </li>`;

        document.getElementById('addAddressForm').reset();
        const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
        addAddressModal.hide();
    });
//dss

document.querySelectorAll('input[type=number]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value < 1) {
            alert('Invalid quantity! Must be at least 1.');
            this.value = 1;
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const orderForm = document.getElementById("orderForm");

    // Ensure quantity inputs are valid (min 1)
    document.querySelectorAll('input[type=number]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) {
                alert('Invalid quantity! Must be at least 1.');
                this.value = 1;
            }
        });
    });

    // Handle form submission
    if (orderForm) {
        orderForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const orderTotalElement = document.getElementById("orderTotal");
            if (!orderTotalElement) {
                alert("Order total is missing.");
                return;
            }

            const totalPrice = parseFloat(orderTotalElement.textContent.trim().replace('₱', ''));
            if (isNaN(totalPrice) || totalPrice <= 0) {
                alert("Invalid total price. Please check your order.");
                return;
            }

            document.getElementById("totalPrice").value = totalPrice;

            const formData = new FormData(orderForm);

            try {
                const response = await fetch("/php/place_order.php", {
                    method: "POST",
                    body: formData,
                });

                const data = await response.json(); // Get the response as JSON

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Failed to place order.");
                }

                alert("Order placed successfully!");

                // Clear the cart and refresh the page
                location.reload(); // This will refresh the page after a successful order

            } catch (error) {
                console.error("Error:", error.message);
                alert(error.message || "An error occurred while placing your order.");
            }
        });
    }

});




  const updateAddressButton = document.querySelector('[data-bs-target="#manageAddressModal"]');
if (updateAddressButton) {
    updateAddressButton.addEventListener("click", function () {
        let modal = new bootstrap.Modal(document.getElementById("manageAddressModal"));
        modal.show();
    });
} else {
    console.error("Error: Update Address button not found.");
}

document.addEventListener("DOMContentLoaded", () => {
    const userId = 1;  // Replace with actual logged-in user ID
    fetchAddresses(userId);
});

function fetchAddresses(userId) {
    fetch(`/php/fetch_user_address.php?user_id=${userId}`)
        .then(response => response.text()) // <-- Get raw response first
        .then(text => {
            console.log("Raw response:", text); // Log raw output
            return JSON.parse(text); // Try to parse JSON
        })
        .then(data => {
            console.log("Parsed JSON:", data); // Ensure JSON is correct

            const addressList = document.getElementById("addressList");
            addressList.innerHTML = "";

            if (!data.success) {
                addressList.innerHTML = `<li class='list-group-item'>${data.message || "No addresses found"}</li>`;
                return;
            }

            const addresses = data.data || [];
            if (addresses.length === 0) {
                addressList.innerHTML = "<li class='list-group-item'>No addresses found</li>";
                return;
            }

            addresses.forEach(address => {
                const li = document.createElement("li");
                li.classList.add("list-group-item");
                li.textContent = `${address.street}, ${address.barangay}, Landmark: ${address.landmark}, Note: ${address.note}`;
                addressList.appendChild(li);
            });
        })
        .catch(error => console.error("Error fetching addresses:", error));
}

document.addEventListener("DOMContentLoaded", function () {
    const closeButton = document.querySelector("#placeOrderModal .btn-close");

    if (closeButton) {
        closeButton.addEventListener("click", function () {
            const placeOrderModal = new bootstrap.Modal(document.getElementById("placeOrderModal"));
            placeOrderModal.hide();
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
  const addAddressModal = document.getElementById("addAddressModal");
  const placeOrderModal = new bootstrap.Modal(document.getElementById("home"));

  addAddressModal.addEventListener("hidden.bs.modal", function () {
    placeOrderModal.show(); // Reopen Place Order Modal when Add Address Modal is closed
  });
});

document.getElementById('orderForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  // Simulate form submission (Replace this with your actual order submission logic)
  console.log('Order submitted!');

  // Close the modal manually
  const modal = bootstrap.Modal.getInstance(document.getElementById('placeOrderModal'));
  modal.hide();
});
  
async function loadAddressNote() {
  try {
    const response = await fetch('/php/fetch_address.php');
    const data = await response.json();

    if (data.note) {
      document.getElementById('orderNote').value = data.note;
    }
  } catch (error) {
    console.error('Error fetching address note:', error);
  }
}

// Call function when the page loads
document.addEventListener('DOMContentLoaded', loadAddressNote);

document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/php/save_address.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal(); // Hide the modal
            location.reload(); // Refresh to show the new address
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error adding address:', error);
    });
});

    // Fetch and display user's address
document.addEventListener('DOMContentLoaded', async () => {
  await fetchAddress();
});

async function fetchAddress() {
    try {
        const response = await fetch("/php/fetch_address.php"); // Adjust the endpoint if needed
        const data = await response.json();

        const addressList = document.getElementById("addressList"); // Ensure this ID exists
        if (!addressList) {
            console.error("Error: addressList element not found.");
            return; // Stop execution if the element is missing
        }

        if (data.success) {
            addressList.innerHTML = ""; // Clear existing content

            data.addresses.forEach(address => {
                let listItem = document.createElement("li");
                listItem.classList.add("list-group-item");
                listItem.textContent = address;
                addressList.appendChild(listItem);
            });
        } else {
            addressList.innerHTML = "<li class='list-group-item text-danger'>No addresses found.</li>";
        }
    } catch (error) {
        console.error("Error fetching address:", error);
    }
}

// Call fetchAddress only when DOM is fully loaded
document.addEventListener("DOMContentLoaded", fetchAddress);


// Add new address
document.getElementById('addAddressForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const barangay = document.getElementById('barangay').value;
  const street = document.getElementById('street').value;
  const landmark = document.getElementById('landmark').value;
  const note = document.getElementById('note').value;

  try {
    const response = await fetch('/php/add_address.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ barangay, street, landmark, note })
    });

    const result = await response.json();
    if (result.status === 'success') {
      fetchAddress();
      const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
      addAddressModal.hide();

      // Ensure modal backdrop is removed
      document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

      // Optionally, show the "Place Order" modal
      const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
      placeOrderModal.show();
    } else {
      alert('Failed to add address. Please try again.');
    }
  } catch (error) {
    console.error('Error adding address:', error);
  }
});




// Manage address (show the update modal)
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('[data-bs-target="#manageAddressModal"]').addEventListener("click", function() {
        const modal = new bootstrap.Modal(document.getElementById("manageAddressModal"));
        modal.show();
    });
});


// Fetch address for editing
async function fetchAddressForEdit() {
  try {
    const response = await fetch('fetch_address.php');
    const data = await response.json();

    document.getElementById('editBarangay').value = data.barangay || '';
    document.getElementById('editStreet').value = data.street || '';
    document.getElementById('editLandmark').value = data.landmark || '';
  } catch (error) {
    console.error('Error fetching address for edit:', error);
  }
}

     document.getElementById('addAddressForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Get form values
    const barangay = document.getElementById('barangay').value;
    const street = document.getElementById('street').value;
    const landmark = document.getElementById('landmark').value;

    // Validate fields
    if (!barangay || !street || !landmark) {
      alert('Please fill in all address fields.');
      return;
    }

    // Update the main modal with new address
    const addressField = document.getElementById('address');
    addressField.textContent = `${street}, ${barangay}, Landmark: ${landmark}`;
    addressField.removeAttribute('readonly');

    // Close modal
    const addAddressModal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
    addAddressModal.hide();
  });
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        fetchCartItems();
    });
});
function fetchCartItems() {
    fetch("/php/fetch_cart.php")
        .then(response => response.text()) // Get raw response
        .then(data => {
            console.log("Raw response:", data); // Debugging output

            try {
                let jsonData = JSON.parse(data); // Convert to JSON
                if (jsonData.status === "success") {
                    console.log("Cart Items:", jsonData.items);

                    let orderSummary = document.getElementById("orderSummary");
                    let orderTotal = document.getElementById("orderTotal");

                    orderSummary.innerHTML = "";
                    jsonData.items.forEach(item => {
                        let listItem = document.createElement("li");
                        listItem.className = "list-group-item bg-dark text-white";
                        listItem.innerHTML = `${item.product_name} - ${item.quantity} x ₱${item.price.toFixed(2)} = ₱${item.subtotal.toFixed(2)}`;
                        orderSummary.appendChild(listItem);
                    });

                    orderTotal.textContent = jsonData.total.toFixed(2);
                } else {
                    alert("Error: " + jsonData.message);
                }
            } catch (error) {
                console.error("JSON Parsing Error:", error, "\nResponse:", data);
                alert("Invalid server response. Check console for details.");
            }
        })
        .catch(error => console.error("Fetch Error:", error));
}


function fetchCartItems() {
  fetch('/php/fetch_cart.php')
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const items = data.items;
        const orderSummary = document.getElementById('orderSummary');
        const orderTotal = document.getElementById('orderTotal');
        const totalPriceInput = document.getElementById('totalPrice');

        if (!orderSummary || !orderTotal || !totalPriceInput) {
          console.error('Error: Required elements not found in the DOM.');
          return;
        }

        // Clear previous content
        orderSummary.innerHTML = '';

        // Add each item to the modal
        let total = 0;
        items.forEach(item => {
          const itemDetails = `
            <li class="list-group-item">
              <strong>${item.product_name}</strong> - 
              Quantity: ${item.quantity}, 
              Price: ₱${parseFloat(item.price).toFixed(2)}, 
              Subtotal: ₱${parseFloat(item.subtotal).toFixed(2)}
            </li>
          `;
          total += parseFloat(item.subtotal);
          orderSummary.innerHTML += itemDetails;
        });

        // Update total price
        orderTotal.textContent = total.toFixed(2);
        totalPriceInput.value = total.toFixed(2);

        // Show the modal
        const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
        placeOrderModal.show();
      } else {
        console.error('Failed to fetch cart items:', data.message);
      }
    })
    .catch(error => console.error('Error fetching cart items:', error));
}

// Add event listener to place order button
document.getElementById('placeOrderBtn').addEventListener('click', fetchCartItems);


    document.addEventListener("DOMContentLoaded", function () {
    const placeOrderModal = new bootstrap.Modal(document.getElementById("placeOrderModal"));
    const orderSummary = document.getElementById("orderSummary");
    const orderTotal = document.getElementById("orderTotal");
    const addressField = document.getElementById("address");

    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        fetch("/php/fetch_order_details.php") // Update path accordingly
            .then(response => response.json())
            .then(data => {
                if (data.status === "error") {
                    alert(data.message);
                    return;
                }

                // Fill address field
                addressField.value = `${data.address.street}, ${data.address.barangay}, Landmark: ${data.address.landmark}\nNote: ${data.address.note}`;

                // Display order summary
                orderSummary.innerHTML = "";
                data.items.forEach(item => {
                    orderSummary.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between">
                            ${item.product_name} (x${item.quantity})
                            <span>₱${item.subtotal.toFixed(2)}</span>
                        </li>`;
                });

                // Display total price
                orderTotal.textContent = data.total.toFixed(2);

                // Show modal
                placeOrderModal.show();
            })
            .catch(error => console.error("Error:", error));
    });
});

document.getElementById('placeOrderBtn').addEventListener('click', function() {
    const orderSummary = document.getElementById('orderSummary');
    const orderTotal = document.getElementById('orderTotal');
    let total = 0;
    orderSummary.innerHTML = '';

    document.querySelectorAll('.cart .list-group-item').forEach(item => {
        const name = item.querySelector('h5').textContent;
        const price = parseFloat(item.querySelector('p').textContent.replace('Price: ₱', ''));
        const quantity = item.querySelector('input[type="number"]').value;
        const itemTotal = price * quantity;
        total += itemTotal;

        const listItem = document.createElement('li');
        listItem.className = 'list-group-item bg-dark text-white';
        listItem.textContent = `${name} x${quantity} - ₱${itemTotal.toFixed(2)}`;
        orderSummary.appendChild(listItem);
    });

    orderTotal.textContent = total.toFixed(2);

    // Show the modal
    const placeOrderModal = new bootstrap.Modal(document.getElementById('placeOrderModal'));
    placeOrderModal.show();
});


  document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    new bootstrap.Modal(document.getElementById('placeOrderModal')).hide();
    new bootstrap.Modal(document.getElementById('successModal')).show();
  });

    
    

document.getElementById('placeOrderBtn').addEventListener('click', function () {
    fetch('/php/get_cart_items.php') // Fetch cart items from database
        .then(response => response.json())
        .then(data => {
            const orderSummary = document.getElementById('orderSummary');
            const orderTotal = document.getElementById('orderTotal');
            let total = 0;
            orderSummary.innerHTML = '';

            data.items.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const listItem = document.createElement('li');
                listItem.className = 'list-group-item bg-dark text-white';
                listItem.textContent = `${item.name} x${item.quantity} - ₱${itemTotal.toFixed(2)}`;
                orderSummary.appendChild(listItem);
            });

            orderTotal.textContent = total.toFixed(2);
        })
        .catch(error => console.error('Error fetching cart items:', error));
});

document.getElementById('orderForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const address = document.getElementById('address').value.trim();
    const paymentMethod = document.getElementById('paymentMethod').value;

    if (address === '') {
        alert('Please enter a delivery address.');
        return;
    }

    fetch('/php/fetch_cart.php') // Get cart items before placing order
        .then(response => response.json())
        .then(data => {


            const orderData = {
                items: data.items,
                payment_method: paymentMethod
            };

            return fetch('/php/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                new bootstrap.Modal(document.getElementById('placeOrderModal')).hide();
                new bootstrap.Modal(document.getElementById('successModal')).show();
            } else {
                alert('Order failed: ' + result.message);
            }
        })
        .catch(error => console.error('Error placing order:', error));
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

<?php $conn->close(); ?>
</body>
</html>