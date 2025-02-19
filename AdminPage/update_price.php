<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging: Print the incoming POST data
    file_put_contents('debug_log.txt', print_r($_POST, true)); // Logs data for debugging

    if (!isset($_POST['product_id']) || !isset($_POST['new_price'])) {
        echo json_encode(['success' => false, 'message' => 'Missing input fields']);
        exit;
    }

    $product_id = (int) $_POST['product_id'];
    $new_price = (float) $_POST['new_price'];

    if ($product_id <= 0 || $new_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input values']);
        exit;
    }

    // Update price in database
    $sql = "UPDATE product SET price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('di', $new_price, $product_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update price']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();

?>
