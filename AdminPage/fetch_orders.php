<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('/xampp/htdocs/WaterRefillingSystem/php/adminlogin.php');

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "
    SELECT 
        o.order_id,
        u.username AS customer_name,
        p.name AS product_name,
        o.quantity,
        p.price,
        (o.quantity * p.price) AS total,
        o.status,
        IFNULL(ptl.status, 'unpaid') AS payment_status,
        IFNULL(ptl.amount, 0) AS amount,
        IFNULL(ptl.payment_method, 'N/A') AS payment_method
    FROM orders o
    LEFT JOIN user u ON o.user_id = u.user_id
    LEFT JOIN product p ON o.product_id = p.product_id
    LEFT JOIN payment_transaction_logs ptl ON o.order_id = ptl.order_id
    ORDER BY o.order_date DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
?>
