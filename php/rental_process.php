<?php
header('Content-Type: application/json');
include 'dbconnect.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

// Ensure all required fields are set
$requiredFields = ['product_id', 'address', 'rental_duration', 'rental_start'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(["status" => "error", "message" => "Missing or empty field: $field"]);
        exit;
    }
}

$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'];
$addressId = $_POST['address'];
$rentalDuration = (int)$_POST['rental_duration'];
$rentalStart = $_POST['rental_start'];

// Calculate rental end date
$rentalEnd = date('Y-m-d', strtotime("$rentalStart + $rentalDuration days"));

// Get product price and stock
$priceQuery = "SELECT price, stock FROM product WHERE product_id = ?";
$stmt = $conn->prepare($priceQuery);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(["status" => "error", "message" => "Product not found."]);
    exit;
}

$rentalPrice = $product['price'];
$stock = $product['stock'];

if ($stock <= 0) {
    echo json_encode(["status" => "error", "message" => "Product is out of stock."]);
    exit;
}

$totalPrice = $rentalPrice * $rentalDuration;

// Start transaction
$conn->begin_transaction();

try {
    // Insert into rental_orders table
    $orderQuery = "INSERT INTO rental_orders (user_id, product_id, address_id, rental_duration, rental_start, rental_end, status, total_price)
                   VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("iiisssd", $userId, $productId, $addressId, $rentalDuration, $rentalStart, $rentalEnd, $totalPrice);
    $stmt->execute();
    $stmt->close();

    // Insert into renting table
    $rentalQuery = "INSERT INTO renting (user_id, product_id, rental_date, return_date, status, total_price)
                    VALUES (?, ?, ?, ?, 'active', ?)";
    $stmt = $conn->prepare($rentalQuery);
    $stmt->bind_param("iissd", $userId, $productId, $rentalStart, $rentalEnd, $totalPrice);
    $stmt->execute();
    $stmt->close();

    // Reduce stock by 1
    $updateStockQuery = "UPDATE product SET stock = stock - 1 WHERE product_id = ?";
    $stmt = $conn->prepare($updateStockQuery);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(["status" => "success", "message" => "Product rented successfully"]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Error processing rental."]);
}
?>
