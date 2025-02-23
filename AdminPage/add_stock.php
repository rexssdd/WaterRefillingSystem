<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Error: Admin ID is missing."]);
    exit;
}
$admin_id = $_SESSION['admin_id'];

$conn = new mysqli('localhost', 'root', '', 'wrsystem');
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Verify that admin_id exists in admin table
$sql_admin = "SELECT admin_id FROM admin WHERE admin_id = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("i", $admin_id);
$stmt_admin->execute();
$stmt_admin->store_result();
if ($stmt_admin->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid admin ID"]);
    exit;
}
$stmt_admin->close();

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(["success" => false, "message" => "Missing input fields"]);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$adjustment_type = 'restock';

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$sql = "SELECT stock FROM product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($previous_stock);
$stmt->fetch();
$stmt->close();

if ($previous_stock === null) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

$sql = "UPDATE product SET stock = stock + ? WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quantity, $product_id);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database update failed: " . $stmt->error]);
    exit;
}

$reason = "Restocked " . $quantity . " units.";
$new_stock = $previous_stock + $quantity;

$sql_log = "INSERT INTO inventory_stock_logs (admin_id, product_id, previous_stock, new_stock, adjustment_type, reason) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->bind_param("iiiiss", $admin_id, $product_id, $previous_stock, $new_stock, $adjustment_type, $reason);
if (!$stmt_log->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to log stock adjustment: " . $stmt_log->error]);
    exit;
}

echo json_encode(["success" => true, "message" => "Stock updated successfully!"]);

$stmt->close();
$stmt_log->close();
$conn->close();

?>
