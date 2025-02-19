<?php
session_start();
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user addresses
$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();

// Handle updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_username'])) {
        $new_username = $_POST['username'];
        $stmt = $conn->prepare("UPDATE user SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        $_SESSION['message'] = ($stmt->execute()) ? "Username updated successfully." : "Failed to update username.";
    } elseif (isset($_POST['update_address'])) {
        $stmt = $conn->prepare("UPDATE address SET street=?, barangay=?, landmark=?, note=? WHERE address_id=? AND user_id=?");
        $stmt->bind_param("ssssii", $_POST['street'], $_POST['barangay'], $_POST['landmark'], $_POST['note'], $_POST['address_id'], $user_id);
    } elseif (isset($_POST['add_address'])) {
        $stmt = $conn->prepare("INSERT INTO address (user_id, street, barangay, landmark, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $_POST['street'], $_POST['barangay'], $_POST['landmark'], $_POST['note']);
    }
    
    $_SESSION['message'] = ($stmt->execute()) ? "Operation successful." : "Operation failed.";
    header("Location: settings.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - JZ Waters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            
        <a class="uil uil-box" href="dashboard.php" onclick="showProducts()" class="active"><strong class="x">Products</strong></a>
        <a href="cart.php" class="uil uil-shopping-cart log" onclick="showCart()">Cart (<span id="cart-count"><?php echo count($_SESSION['cart']); ?></span>)</a>
        <a href="#" class="uil uil-check-circle log" onclick="showOrderHistory()">Orders</a>
        <a href="#" class="uil uil-history log" onclick="showOrderHistory()">Order History</a>
        <a href="#" class="uil uil-cog log" onclick="showOrderHistory()">Settings</a>
        <a href="/php/logout.php" class="uil uil-signout log" onclick="confirmLogout()">Logout</a>
    </div>

<div class="container mt-5">
    <h2 class="text-center">Settings</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-warning"> <?= $_SESSION['message']; unset($_SESSION['message']); ?> </div>
    <?php endif; ?>

    <!-- Username Modification -->
    <div class="card p-3 mb-3">
        <h4>Change Username</h4>
        <form method="POST">
            <input type="text" name="username" class="form-control mb-2" value="<?= htmlspecialchars($user['username']); ?>" required>
            <button type="submit" name="update_username" class="btn btn-primary w-100">Update Username</button>
        </form>
    </div>

    <!-- Address Modal Trigger -->
    <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#addressModal">
        Manage Address
    </button>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($addresses->num_rows > 0): ?>
                    <?php while ($row = $addresses->fetch_assoc()): ?>
                        <form method="POST">
                            <input type="hidden" name="address_id" value="<?= $row['address_id']; ?>">
                            <?php foreach (["street", "barangay", "district", "city", "province"] as $field): ?>
                                <input type="text" name="<?= $field ?>" class="form-control mb-2" value="<?= htmlspecialchars($row[$field]); ?>" required>
                            <?php endforeach; ?>
                            <button type="submit" name="update_address" class="btn btn-primary w-100">Update Address</button>
                        </form>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No address found. Add your address first.</p>
                <?php endif; ?>
                
                <h5 class="mt-3">Add New Address</h5>
                <form method="POST">
                    <input type="hidden" name="add_address">
                    <?php foreach (["Street", "Barangay", "District", "City", "Province"] as $field): ?>
                        <input type="text" name="<?= strtolower($field) ?>" class="form-control mb-2" placeholder="<?= $field ?>" required>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-success w-100">Add Address</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
