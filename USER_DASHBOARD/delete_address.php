<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'wrsystem');

if ($conn->connect_error) {
    $_SESSION['message'] = 'Database connection failed.';
    header('Location: /USER_DASHBOARD/cart.php');
    exit();
}

// Check if address_id is provided
if (empty($_POST['address_id'])) {
    $_SESSION['message'] = 'No address selected.';
    header('Location: /USER_DASHBOARD/cart.php');
    exit();
}

$address_id = intval($_POST['address_id']);

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM address WHERE address_id = ?");
$stmt->bind_param("i", $address_id);
$stmt->execute();

// Set session message based on result
$_SESSION['message'] = $stmt->affected_rows > 0 
    ? 'Address deleted successfully!' 
    : 'Address not found or already removed.';

$stmt->close();
$conn->close();

// Redirect to settings page
header('Location: /USER_DASHBOARD/settings.php');
exit();
?>
