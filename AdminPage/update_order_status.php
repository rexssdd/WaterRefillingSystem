<?php
header('Content-Type: application/json'); // Ensure response is JSON
include_once('/xampp/htdocs/WaterRefillingSystem/php/adminlogin.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;

    if (!$order_id) {
        echo json_encode(["success" => false, "message" => "Invalid Order ID"]);
        exit;
    }

    // Ensure `order_id` is an integer
    $order_id = intval($order_id);

    // Update order status to 'preparing'
    $query = "UPDATE orders SET status = 'preparing' WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Order updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database update failed"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
