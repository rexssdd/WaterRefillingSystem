<?php
include_once('/xampp/htdocs/WaterRefillingSystem/php/adminlogin.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$order_id = $data['order_id'] ?? null;
$status = $data['status'] ?? null;
$reason = $data['reason'] ?? null;
$delivered_by = 'Delivery Team'; // Adjust this based on your login system

if ($order_id && $status) {
    // Update order status
    $updateOrderSql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateOrderSql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        // Insert delivery log
        $logSql = "INSERT INTO delivery_logs (order_id, status, reason, delivered_by) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($logSql);
        $stmt->bind_param("isss", $order_id, $status, $reason, $delivered_by);
        $stmt->execute();
        
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to update order status."]);
    }
    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid order ID or status."]);
}
?>
