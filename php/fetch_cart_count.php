<?php
session_start();
require '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id > 0) {
    $cart_query = "SELECT COUNT(DISTINCT product_id) AS total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_data = $result->fetch_assoc();
    
    $cart_count = $cart_data['total_items'] ?? 0;
} else {
    $cart_count = 0;
}

echo json_encode(['cart_count' => $cart_count]);
?>
