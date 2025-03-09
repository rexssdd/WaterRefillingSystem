<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';// Redirect if not authenticated

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Ensure the cart is always an array
}

if (isset($_SESSION['message'])) {
    echo '<p>' . htmlspecialchars($_SESSION['message']) . '</p>';
    unset($_SESSION['message']); // Clear the message after displaying it
}
// Ensure user session variables are properly set
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 0; // Default value if not logged in
}

$username = $_SESSION['username'] ?? 'Guest';
$isLoggedIn = isset($_SESSION['username']) ? 'true' : 'false';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch addresses
$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();

$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses1 = $stmt->get_result();

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST['username']);
        if (!empty($new_username)) {
            $stmt = $conn->prepare("UPDATE user SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_username, $user_id);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $_SESSION['message'] = "Username updated successfully.";
            } else {
                $_SESSION['message'] = "Failed to update username.";
            }
        } else {
            $_SESSION['message'] = "Username cannot be empty.";
        }
        header("Location: settings.php");
        exit(); // Prevent form resubmission
    }

    if (isset($_POST['add_address'])) {
        $stmt = $conn->prepare("INSERT INTO address (user_id, street, barangay, landmark, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $_POST['street'], $_POST['barangay'], $_POST['landmark'], $_POST['note']);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Address added successfully.";
        } else {
            $_SESSION['message'] = "Failed to add address.";
        }
        header("Location: settings.php");
        exit();
    }

    if (isset($_POST['update_address'])) {
        $stmt = $conn->prepare("UPDATE address SET street=?, barangay=?, landmark=?, note=? WHERE address_id=? AND user_id=?");
        $stmt->bind_param("ssssii", $_POST['street'], $_POST['barangay'], $_POST['landmark'], $_POST['note'], $_POST['address_id'], $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Address updated successfully.";
        } else {
            $_SESSION['message'] = "Failed to update address.";
        }
        header("Location: settings.php");
        exit();
    }

    if (isset($_POST['delete_address'])) {
        $stmt = $conn->prepare("DELETE FROM address WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_POST['address_id'], $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Address deleted successfully.";
        } else {
            $_SESSION['message'] = "Failed to delete address.";
        }
        header("Location: settings.php");
        exit();
    }

    if (isset($_POST['update_password'])) {
        $new_password = trim($_POST['password']);
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $_SESSION['password_message'] = "Password updated successfully.";
            } else {
                $_SESSION['password_message'] = "Failed to update password.";
            }
        } else {
            $_SESSION['password_message'] = "Password cannot be empty.";
        }
        header("Location: settings.php");
        exit();
    }
}



?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Settings - JZ Waters</title>
                                       <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

    <style>
            /* General Styles */
     body { background-color:  #353544; color: #d4af37; font-family: 'Poppins', sans-serif; }
  
     .navbar { display: inline-flex; justify-content: center; background-color: black; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); color: #d4af37; position: fixed; width: 80%; margin-left: 340px; margin-top: -80px;}
        
    /* Centering the content */
    
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


    /* General styling for user info section */
.user-info-card {
    background: #222;
    color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(255, 255, 255, 0.3);
    text-align: left;
    margin-bottom: 500px;
    padding: 20px;
}

/* Headings for better readability */
.user-info-card h4 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #f8f9fa;
}

/* User information text */
.user-info-card p {
    font-size: 24px;
    margin-bottom: 10px;
}

/* Styled list for addresses */
.user-info-card ul {
    padding: 0;
    list-style: none;
}

.user-info-card li {
    font-size: 0.95rem;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px;
    margin: 5px 0;
    border-radius: 5px;
}

/* Button section */
.user-info-card .d-grid {
    margin-top: 400px;
}

.user-info-card button {
    padding: 10px 15px;
    font-size: 1rem;
    border-radius: 5px;
    transition: background 0.3s, transform 0.2s;
}

/* Hover effect for better interactivity */
.user-info-card button:hover {
    transform: scale(1.05);
}

/* Ensuring accessibility */
@media (max-width: 600px) {
    .user-info-card {
        width: 90%;
    }

    .user-info-card button {
        font-size: 0.9rem;
        padding: 8px 12px;
    }
}


    /* Center content properly */
    .main-content {
        margin-left: 270px;
        padding: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    /* Card Styling */
    .card {
        background: #222;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(255, 255, 255, 0.3);
        padding: 15px;
        width: 100%;
        max-width: 350px;
        text-align: center;
    }

    /* Inputs */
    .form-control {
        background-color: #333;
        border: 1px solid #555;
        color: #d4af37;
        text-align: center;
    }

    input{
        color:  #FFFFF0;
    }

    /* Buttons */
    .btn {
        background-color: #FFFFF0;
        color:  black;
        border-radius: 5px;
        padding: 8px;
        font-size: 14px;
        font-weight: bold;
        transition: 0.3s ease-in-out;
    }

    .btn:hover {
        background:#284387;
        color:  #FFFFF0;
    }

    /* Modal */
    .modal-content {
        background: #222;
        color: #d4af37;
        border-radius: 10px;
    }

    /* Modal Buttons */
    .btn-close {
        filter: invert(1);
    }

    .toggle-password {
        background: transparent;
        border: none;
        color: #d4af37;
        font-size: 18px;
    }

    .toggle-password:hover {
        color: #fff;
    }

    /* Centered Notification */
    #notification {
        background-color: #4caf50;
        color: white;
        padding: 10px;
        position: fixed;
        top: 10px;
        right: 10px;
        border-radius: 5px;
        z-index: 1000;
        font-size: 14px;
    }

    /* Adjust buttons for better alignment */
    .btn-secondary,
    .btn-success {
        width: 100%;
        
        margin: 5px auto;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 220px;
        }

        .main-content {
            margin-left: 230px;
            padding: 20px;
        }

        .container {
            max-width: 350px;
        }
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
    .s{
        margin-top: 100px;
    }
      @media (max-width: 1600px) {
        .user-info-card {
            width: 1200px;
            margin-left: -300px;
        }
    }
    @media (max-width: 1200px) {
        .user-info-card {
            width: 900px;
            margin-left: -150px;
        }
    }
    @media (max-width: 992px) {
        .user-info-card {
            width: 700px;
            margin-left: 0;
        }
    }
    @media (max-width: 768px) {
        .user-info-card {
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
    }

    .form-label{
        color:  #FFFFF0;
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
            <a href="cart.php" class="uil uil-shopping-cart log" onclick="showCart()">Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)</a>
           <a href="rental.php" class="uil uil-house-user">Product Rental</a>
        <a href="order.php" class="uil uil-heart-alt">Orders</a>
        <a href="orderhistory.php" class="uil uil-history">Order History</a>
        <a href="settings.php" class="uil uil-cog active">Settings</a>
       <a href="/php/logout.php" class="uil uil-signout logout-btn" onclick="confirmLogout()" style=" color: white; position: absolute; bottom: 20px;">Logout</a>
        </div>

        <nav class="navbar d-flex align-items-center justify-content-between px-4" 
    style="color: white; background: #515151; border-radius: 50px; height: 60px;">
    
    <!-- Left: Menu Icon -->
    <i class="uil uil-bars fs-3" id="menu-icon" style="cursor: pointer;"></i>

    <!-- Center: Page Title -->
    <h2 id="page-title" class="uil uil-cog m-0 " style="font-size: 24px;" >
        <strong class="x">Settings</strong>
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
        <div class="container mt-5">
       

    <!-- Notification Section -->
    <?php if (isset($_SESSION['message'])): ?>
        <div id="notification" class="alert alert-success" role="alert">
            <?= htmlspecialchars($_SESSION['message']); ?>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('notification').style.display = 'none';
            }, 2000);
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="s">
  <!-- User Information Display Section -->
<div class="user-info-card mt-5 p-5" style="max-width: 1500px; width: 100%; margin-left:120px; margin-top: 500px; margin-bottom: 100px; background: #222; border-radius: 10px; box-shadow: 0 4px 6px rgba(255, 255, 255, 0.3);">
    <h4 class="mb-3 uil uil-user" style="text-align: center; font-size: 28px; "><strong class="s">User Information</strong></h4>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
    <p><strong>Contact Number:</strong> <?= htmlspecialchars($user['contact_number']); ?></p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserInfoModal">Edit Information</button>

    <h4 class="mt-4">User Addresses</h4>
    <ul class="list-unstyled">
        <?php if ($addresses->num_rows > 0): ?>
            <?php while ($row = $addresses->fetch_assoc()): ?>
                <li><?= htmlspecialchars($row['street']) ?>, <?= htmlspecialchars($row['barangay']) ?> (Landmark: <?= htmlspecialchars($row['landmark']) ?>) - <?= htmlspecialchars($row['note'] ?? 'No notes') ?></li>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No addresses available.</p>
        <?php endif; ?>
    </ul>

    <!-- Buttons inside the section -->
    <div class="mt-4 d-line gap-20">
               <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#updateAddressModal">Manage Address</button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add New Address</button>
    </div>
</div>

</div>
    <!-- Edit User Info Modal -->
    <div class="modal fade" id="editUserInfoModal" tabindex="-1" aria-labelledby="editUserInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control mb-2" value="<?= htmlspecialchars($user['username']); ?>" required>
                        
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control mb-2" value="<?= htmlspecialchars($user['email']); ?>" required>
                        
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control mb-2" value="<?= htmlspecialchars($user['contact_number']); ?>" required>
                        
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <p class="text-center mt-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal" style="color: #d4af37; text-decoration: underline;">
                            Forgot Password?
                        </a>
                    </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="container mt-5">
          <!-- Display the notification -->
        <!-- Display password update message -->
    <?php if (isset($_SESSION['password_message'])): ?>
        <p style="color: <?= ($_SESSION['password_message'] === 'Password updated successfully.') ? '#4caf50' : 'red'; ?>;">
            <?= htmlspecialchars($_SESSION['password_message']); ?>
        </p>
        <?php unset($_SESSION['password_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['message'])): ?>
        <div id="notification" style="
            background-color: #4caf50; 
            color: white; 
            padding: 15px; 
            position: fixed; 
            top: 10px; 
            right: 10px; 
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


 
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="changePasswordForm">
                        <!-- Current Password -->
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <div class="input-group mb-2">
                            <input type="password" id="currentPassword" name="current_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="currentPassword">
                                👁️
                            </button>
                        </div>

                        <!-- New Password -->
                        <label for="newPassword" class="form-label">New Password</label>
                        <div class="input-group mb-2">
                            <input type="password" id="newPassword" name="new_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newPassword">
                                👁️
                            </button>
                        </div>

                        <!-- Confirm New Password -->
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <div class="input-group mb-3">
                            <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword">
                                👁️
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Update Address Modal -->
    <div class="modal fade" id="updateAddressModal" tabindex="-1" inert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Address Selection -->
                    <form method="POST" id="deleteAddressForm">
                        <label for="addressSelect" class="form-label">Select an Address:</label>
                        <div class="d-flex align-items-center mb-3">
                            <select id="addressSelect" class="form-select me-2" name="address_id" required>
                            <option value="" disabled selected>Choose an address</option>
                            <?php if ($addresses1->num_rows > 0): ?>
                                <?php while ($row = $addresses1->fetch_assoc()): ?>
                                    <option value="<?= $row['address_id']; ?>"
                                            data-barangay="<?= htmlspecialchars($row['barangay']); ?>"
                                            data-street="<?= htmlspecialchars($row['street']); ?>"
                                            data-landmark="<?= htmlspecialchars($row['landmark']); ?>"
                                            data-note="<?= htmlspecialchars($row['note'] ?? ''); ?>">
                                        <?= htmlspecialchars($row['street']) . ', ' . htmlspecialchars($row['barangay']) . ' (Landmark: ' . htmlspecialchars($row['landmark']) . ')' ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option disabled>No addresses found</option>
                            <?php endif; ?>
                        </select>
                            <button type="submit" id="deleteAddressBtn" class="btn btn-danger">X</button>
                        </div>
                    </form>

                    <hr>

                    <!-- Update Address Form -->
                    <form method="POST" id="updateAddressForm">
                        <input type="hidden" id="updateAddressId" name="address_id">

                        <label for="barangay" class="form-label">Barangay</label>
                        <input type="text" id="barangay" name="barangay" class="form-control mb-2" required>

                        <label for="street" class="form-label">Street</label>
                        <input type="text" id="street" name="street" class="form-control mb-2" required>

                        <label for="landmark" class="form-label">Landmark</label>
                        <input type="text" id="landmark" name="landmark" class="form-control mb-2">

                        <label for="note" class="form-label">Note</label>
                        <textarea id="note" name="note" class="form-control mb-2" rows="3"></textarea>

                        <button type="submit" name="update_address" class="btn btn-primary w-100">Update Address</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript to handle address removal -->


    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addAddressForm" method="POST">
                        <input type="hidden" name="add_address">
                        
                        <label for="addBarangay" class="form-label">Barangay</label>
                        <input type="text" id="addBarangay" name="barangay" class="form-control mb-2" placeholder="Barangay" required>

                        <label for="addStreet" class="form-label">Street</label>
                        <input type="text" id="addStreet" name="street" class="form-control mb-2" placeholder="Street" required>

                        <label for="addLandmark" class="form-label">Landmark</label>
                        <input type="text" id="addLandmark" name="landmark" class="form-control mb-2" placeholder="Landmark">

                        <label for="addNote" class="form-label">Note</label>
                        <textarea id="addNote" name="note" class="form-control mb-2" rows="3" placeholder="Additional notes (optional)"></textarea>

                        <button type="submit" class="btn btn-success w-100">Add Address</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




<!-- Script to auto-fill address fields -->
<script>

document.getElementById("updateAddressModal").addEventListener("show.bs.modal", function () {
    this.removeAttribute("inert");
});

document.getElementById("updateAddressModal").addEventListener("hidden.bs.modal", function () {
    this.setAttribute("inert", "");
});

document.addEventListener("DOMContentLoaded", function () {
    const updateModal = document.getElementById("updateAddressModal");

    updateModal.addEventListener("show.bs.modal", function () {
        updateModal.removeAttribute("aria-hidden"); // Ensure modal is not hidden when open
    });

    updateModal.addEventListener("hidden.bs.modal", function () {
        updateModal.setAttribute("aria-hidden", "true"); // Re-add when closed
    });
});


    document.getElementById('addressSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        ['barangay', 'street', 'landmark', 'note'].forEach(field => {
            document.getElementById(field).value = selectedOption.getAttribute('data-' + field) || '';
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.getAttribute('data-target'));
            target.type = target.type === 'password' ? 'text' : 'password';
        });
    });

    // Password validation
    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            event.preventDefault();
        } else{
            alert('Password Updated');
        }
    });

    document.getElementById('deleteAddressBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent form submission
    const selectedAddress = document.getElementById('addressSelect').value;
    
    if (!selectedAddress) {
        alert('Please select an address to remove.');
        return;
    }

    if (confirm('Are you sure you want to remove this address?')) {
        fetch('delete_address.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ address_id: selectedAddress })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete address.');
            return response.text();
        })
        .then(() => {
            alert('Address deleted successfully!');
            window.location.href = '/USER_DASHBOARD/settings.php'; // Redirect after deletion
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});


document.addEventListener('DOMContentLoaded', function () {
    const addressSelect = document.getElementById('addressSelect');
    const barangayField = document.getElementById('barangay');
    const streetField = document.getElementById('street');
    const landmarkField = document.getElementById('landmark');
    const noteField = document.getElementById('note');
    const updateAddressForm = document.getElementById('updateAddressForm');

    // Address data from PHP (convert to JSON format in PHP)
    const addressData = <?= json_encode($addresses->fetch_all(MYSQLI_ASSOC)) ?>;

    // Fill form fields when an address is selected
    addressSelect.addEventListener('change', function () {
        const selectedAddress = addressData.find(address => address.address_id == this.value);

        if (selectedAddress) {
            barangayField.value = selectedAddress.barangay || '';
            streetField.value = selectedAddress.street || '';
            landmarkField.value = selectedAddress.landmark || '';
            noteField.value = selectedAddress.note || '';
        }
    });

    // Update address handler
    updateAddressForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('address_id', addressSelect.value);

        fetch('update_address.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Address updated successfully!');
                  location.reload(); // Reload to reflect changes
              } else {
                  alert('Failed to update address.');
              }
          }).catch(error => console.error('Error:', error));
    });
});


document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("#addAddressModal form").addEventListener("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);

        fetch("add_address.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);  // Show success message
                window.location.href = '/USER_DASHBOARD/settings.php'; // Redirect after deletion
            } else {
                alert(data.error); // Show error message
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});
</script>
</body>
</html>
