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

$sql = "SELECT address_id, barangay, street, landmark FROM address WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $address_id = $row['address_id'];
    $barangay = $row['barangay'];
    $street = $row['street'];
    $landmark = $row['landmark'];
    $address = "{$street}, {$barangay}, Landmark: {$landmark}";
} else {
    $address_id = null;
    $barangay = $street = $landmark = '';
    $address = "No address found.";
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
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - POS System</title>
    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        body { background-color:rgb(0, 0, 0);  color: #d4af37; font-family: 'Poppins', sans-serif; }
        .sidebar {  background-color: black;  height: 100vh; width: 260px; position: fixed; padding-top: 20px; color: #d4af37; box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1); }
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
            
        } .content h2{
            align-self: center;
            gap: 30px;
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
        .home{
         position: relative;
    background-color: rgba(0,0,0,0.6);
    height: 1000px;
    width: 100%;
    /* background-image: url(/WaterRefillingSystem/images/bg-3.jpg); */
    background-size: cover;
    background-position: center;
        }
        .modal-backdrop.show{
          opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar text-white">
    <a href="#" class="nav_logo">
                <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo">
                <Strong class="Jz_Waters">Jz Waters</Strong>
            </a>
            
        <a class="uil uil-box" href="dashboard.php" onclick="showProducts()" ><strong class="x">Products</strong></a>
        <a href="cart.php"  class="uil uil-shopping-cart active">
             Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
        <a href="rental.php" class="uil uil-history log" onclick="showOrderHistory()">Product Rental</a>
        <a href="order.php" class="uil uil-history log" onclick="showOrderHistory()">Orders</a>
        <a href="orderhistory.php" class="uil uil-history log" onclick="showOrderHistory()">Order History</a>
        <a href="settings.php" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" style ="margin-top: 450px; background-color:red; color: white;"  class="uil uil-signout log1" onclick="confirmLogout()">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar">
            <h2>Your Cart</h2>
        </nav>
      <!-- Unified Cart Section -->
      <section class="cart mt-4">
    <div id="cart-content">
        <ul class="list-group">
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
            
            $total_price = 0;
            if ($cart_result->num_rows > 0) {
                while ($cart_item = $cart_result->fetch_assoc()) {
                    $item_total = $cart_item['price'] * $cart_item['quantity'];
                    $total_price += $item_total;
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                    <img src="<?php echo htmlspecialchars($cart_item['photo']); ?>" alt="Product Image" class="product-image" style="width: 80px; height: 80px; border-radius: 5px;">
                    <div>
                        <h5><?php echo htmlspecialchars($cart_item['name']); ?></h5>
                        <p>Price: ₱<?php echo number_format($cart_item['price'], 2); ?></p>
                        <label for="quantity_<?php echo $cart_item['cart_id']; ?>">Quantity:</label>
                        <input type="number" id="quantity_<?php echo $cart_item['cart_id']; ?>" 
                            class="form-control"
                            value="<?php echo $cart_item['quantity']; ?>" 
                            min="1" 
                            oninput="this.value = Math.max(this.value, 1)"
                            data-cart-id="<?php echo $cart_item['cart_id']; ?>" 
                            data-product-id="<?php echo $cart_item['product_id']; ?>" 
                            style="width: 80px;">


                    </div>
                    <a href="cart.php?remove_cart_id=<?php echo $cart_item['cart_id']; ?>" class="btn btn-danger">Remove</a>
                </li>
            <?php
                }
            } else {
                echo "<li class='list-group-item text-white bg-dark'>Your cart is empty.</li>";
            }
            ?>
        </ul>
        <h4 class="mt-3">Total Cart Value: ₱<?php echo number_format($total_price, 2); ?></h4>
    </div>
</section>

<!-- Place Order Button -->
<button class="btn btn-primary mt-3" id="placeOrderBtn" data-bs-toggle="modal" data-bs-target="#placeOrderModal">
    Place Order
</button>

<section class="home" id="home">
<!-- Place Order Modal -->
<div class="modal fade" id="placeOrderModal" tabindex="-1" aria-labelledby="placeOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="placeOrderModalLabel">Confirm Your Order</h5>
        <button type="button" class="btn-close btn-close-white" id="close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body p-4">
        <form id="orderForm">
          <!-- Delivery Address -->
          <div class="mb-3">
            <label for="address" class="form-label fw-bold">Delivery Address</label>
            <?php if ($address): ?>
              <textarea class="form-control" id="address" name="address" rows="2" readonly><?= htmlspecialchars($address); ?></textarea>
            <?php else: ?>
              <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                + Add Address
              </button>
            <?php endif; ?>
            <!-- Update Address Button -->
            <button type="button" class="btn btn-info mt-2" data-bs-toggle="modal" data-bs-target="#addAddressModal">
            Update Address
          </button>



          </div>

              
            <!-- Note -->
      <div class="mb-3">
        <label for="orderNote" class="form-label fw-bold">Special Instructions (Optional)</label>
        <textarea class="form-control" id="orderNote" name="orderNote" rows="2" placeholder="Add any notes for delivery..."></textarea>
      </div>


          <!-- Payment Method -->
          <div class="mb-3">
            <label for="paymentMethod" class="form-label fw-bold">Payment Method</label>
            <select class="form-select" id="paymentMethod" name="paymentMethod" required>
              <option value="gcash">GCash</option>
              <option value="cod">Cash on Delivery</option>
            </select>
          </div>

          <!-- Order Summary -->
          <div class="mb-3">
            <h6 class="fw-bold mb-3">Order Summary</h6>
            <ul id="orderSummary" class="list-group border rounded">
              <!-- Items will be dynamically added here -->
            </ul>
            <p class="mt-3 text-end fs-5">
    <strong>Total:</strong> ₱<span id="orderTotal">0.00</span>
</p>

<!-- Hidden input for form submission -->
<input type="hidden" id="totalPrice" name="total_price" value="0">
          </div>

         <!-- Confirm Order Button -->
         <div class="text-center">
               <button type="submit" class="btn btn-success w-100 py-2">Confirm Order</button>
             </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Manage Address Modal -->
<div class="modal fade" id="manageAddressModal" tabindex="-1" aria-labelledby="manageAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="manageAddressModalLabel">Manage Addresses</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <ul id="addressList" class="list-group">
          <!-- Addresses will be dynamically listed here -->
        </ul>

        <!-- Button to open Add Address Modal -->
        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
          Add New Address
        </button>
      </div>
    </div>
  </div>
</div>
</section>

<section>
<!-- Add Address Modal (Moved Outside) -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title" id="addAddressModalLabel">Add Your Address</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="addAddressForm">
          <div class="mb-3">
            <label for="barangay" class="form-label">Barangay</label>
            <input type="text" class="form-control" id="barangay" name="barangay" required>
          </div>

          <div class="mb-3">
            <label for="street" class="form-label">Street</label>
            <input type="text" class="form-control" id="street" name="street" required>
          </div>

          <div class="mb-3">
            <label for="landmark" class="form-label">Landmark</label>
            <input type="text" class="form-control" id="landmark" name="landmark" required>
          </div>

          <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" rows="3" placeholder="Additional details (optional)"></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100">Save Address</button>
        </form>
      </div>
    </div>
  </div>
</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" crossorigin="anonymous"></script>
<script>

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