<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "wrsystem";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Corrected SQL query
$sql = "SELECT 
            o.order_id,
            u.username AS customer_name,
            o.order_date,
            o.status,
            GROUP_CONCAT(CONCAT(p.name, ' (x', o.quantity, ')') SEPARATOR ', ') AS products
        FROM orders o
        JOIN user u ON o.user_id = u.user_id
        JOIN product p ON o.product_id = p.product_id
        WHERE o.status = 'preparing'
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

$orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode($orders);
} else {
    echo json_encode(["error" => $conn->error]);
}

$conn->close();
?>
