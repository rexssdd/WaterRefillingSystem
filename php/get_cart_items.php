<?php
// Set content type to JSON
header('Content-Type: application/json');
session_start();
require 'dbconnect.php';

// Get user ID from session (assuming user is logged in)
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

// SQL query to get cart items with product details
$sql = "SELECT 
            c.cart_id,
            p.name,
            p.price,
            c.quantity,
            (p.price * c.quantity) AS item_total
        FROM cart c
        JOIN product p ON c.product_id = p.product_id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

// Return cart items or an empty array if none
echo json_encode([
    "success" => true,
    "items" => $cartItems ?: []
]);
?>
