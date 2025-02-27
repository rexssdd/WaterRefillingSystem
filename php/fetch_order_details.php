<?php
session_start();
require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = [];

// Fetch user's address
$addressQuery = $conn->prepare("SELECT barangay, street, landmark, note FROM address WHERE user_id = ?");
$addressQuery->bind_param("i", $user_id);
$addressQuery->execute();
$addressResult = $addressQuery->get_result();
$address = $addressResult->fetch_assoc();
$addressQuery->close();
$response['address'] = $address ?? ['barangay' => '', 'street' => '', 'landmark' => '', 'note' => ''];

// Fetch cart items
$cartQuery = $conn->prepare("SELECT c.product_id, p.product_name, p.price, c.quantity FROM cart c JOIN product p ON c.product_id = p.product_id WHERE c.user_id = ?");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cartResult = $cartQuery->get_result();
$items = [];
$total = 0;

while ($row = $cartResult->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $items[] = $row;
}

$cartQuery->close();
$response['items'] = $items;
$response['total'] = $total;

echo json_encode($response);
?>
