<?php
include_once('/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php');
header('Content-Type: application/json');

$sql = "SELECT o.order_id, u.username AS customer_name, o.status 
        FROM orders o
        LEFT JOIN user u ON o.user_id = u.user_id
        WHERE o.status = 'to deliver'";

$result = $conn->query($sql);

$orders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'order_id' => $row['order_id'],
            'customer_name' => $row['customer_name'] ?? 'Guest',
            'status' => $row['status']
        ];
    }
}

echo json_encode($orders);
$conn->close();
?>
