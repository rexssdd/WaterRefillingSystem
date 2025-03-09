<?php
session_start();
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit();
}

// Decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['cart_id'], $data['product_id'], $data['quantity'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters."]);
    exit();
}

$cart_id = intval($data['cart_id']);
$product_id = intval($data['product_id']);
$new_quantity = intval($data['quantity']);
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id === 0) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

// Use a transaction to handle concurrency
$conn->begin_transaction();
try {
    // Lock the product row to prevent race conditions
    $stmt = $conn->prepare("SELECT stock, price FROM product WHERE product_id = ? FOR UPDATE");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Product not found.");
    }

    $product = $result->fetch_assoc();
    $available_stock = $product['stock'];
    $price = $product['price'];

    // Check if the requested quantity is available
    if ($new_quantity > $available_stock) {
        throw new Exception("Insufficient stock.");
    }

    // Update the cart with the new quantity
    $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
    $update_stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
    $update_stmt->execute();

    // Calculate the new total price
    $new_total = number_format($new_quantity * $price, 2);

    $conn->commit();
    
    // Send success response
    echo json_encode(["status" => "success", "new_total" => $new_total, "redirect" => "/USER_DASHBOARD/cart.php"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

exit();

?>
