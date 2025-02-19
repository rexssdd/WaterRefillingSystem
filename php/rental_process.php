<?php
include 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_rental'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $addressId = $_POST['address'];
    $rentalDuration = $_POST['rental_duration'];
    $rentalStart = $_POST['rental_start'];
    
    // Calculate rental end date
    $rentalEnd = date('Y-m-d', strtotime("$rentalStart + $rentalDuration days"));

    if (empty($addressId)) {
        echo "<script>alert('Please enter an address before renting.'); window.history.back();</script>";
        exit;
    }

    $query = "INSERT INTO rental_orders (user_id, product_id, address_id, rental_duration, rental_start, rental_end, status)
              VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisss", $userId, $productId, $addressId, $rentalDuration, $rentalStart, $rentalEnd);
    
    if ($stmt->execute()) {
        echo "<script>alert('Rental confirmed!'); window.location.href='order_history.php';</script>";
    } else {
        echo "<script>alert('Error processing rental.'); window.history.back();</script>";
    }
}
?>
