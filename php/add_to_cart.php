<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

header('Content-Type: application/json'); // Set response to JSON

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'You must log in first to add items to your cart.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate input
if ($product_id <= 0 || $quantity < 1) {
    echo json_encode(['error' => 'Invalid product or quantity.']);
    exit;
}

// Check if item already exists in the cart
$check_cart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check_cart->bind_param("ii", $user_id, $product_id);
$check_cart->execute();
$result = $check_cart->get_result();
$check_cart->close();

if ($result->num_rows > 0) {
    // Update quantity
    $update_cart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $update_cart->bind_param("iii", $quantity, $user_id, $product_id);
    $update_cart->execute();
    $update_cart->close();
} else {
    // Insert new item
    $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert_cart->bind_param("iii", $user_id, $product_id, $quantity);
    $insert_cart->execute();
    $insert_cart->close();
}

echo json_encode(['success' => 'Item added to cart successfully!']);
exit;
?>
