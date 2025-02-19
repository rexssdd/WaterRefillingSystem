<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Ensure no extra output
ob_end_clean();

// Check if required fields are set
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(["success" => false, "message" => "Missing input fields"]);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$adjustment_type = 'restock'; // Default adjustment type

// Validate inputs
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Create a connection to the database
$conn = new mysqli('localhost', 'root', '', 'wrsystem');
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch previous stock before updating
$sql = "SELECT stock FROM product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($previous_stock);
$stmt->fetch();
$stmt->close();

// Check if product exists
if ($previous_stock === null) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

// Update product stock
$sql = "UPDATE product SET stock = stock + ? WHERE product_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $quantity, $product_id);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database update failed: " . $stmt->error]);
    exit;
}

// Log the stock adjustment
$admin_id = 1; // Replace with actual admin ID, potentially passed via session
$reason = "Restocked " . $quantity . " units.";
$new_stock = $previous_stock + $quantity;

$sql_log = "INSERT INTO inventory_stock_logs (admin_id, product_id, previous_stock, new_stock, adjustment_type, reason) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_log = $conn->prepare($sql_log);
if (!$stmt_log) {
    echo json_encode(["success" => false, "message" => "SQL preparation failed: " . $conn->error]);
    exit;
}

// The corrected bind_param format ensures strings are correctly passed
$stmt_log->bind_param("iiiiss", $admin_id, $product_id, $previous_stock, $new_stock, $adjustment_type, $reason);
if (!$stmt_log->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to log stock adjustment: " . $stmt_log->error]);
    exit;
}

// Send success response
echo json_encode(["success" => true, "message" => "Stock updated successfully!"]);

// Close connections
$stmt->close();
$stmt_log->close();
$conn->close();
?>
