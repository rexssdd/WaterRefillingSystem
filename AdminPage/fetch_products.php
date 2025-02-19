<?php
require 'db_connection.php'; // Ensure you have a file to connect to your database

$query = "SELECT product_code, product_name, price, stock, description, type, status FROM products";
$result = $conn->query($query);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
?>
