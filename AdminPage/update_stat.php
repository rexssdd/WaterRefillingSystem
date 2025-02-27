<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$database = "wrsystem";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['order_ids'])) {
        $order_ids = $_POST['order_ids'];
        
        // Prepare SQL statement to update the status to "to deliver"
        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
        $stmt = $conn->prepare("UPDATE orders SET status = 'to deliver' WHERE order_id IN ($placeholders)");
        
        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($order_ids)), ...$order_ids);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating orders: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No orders selected.']);
    }
}

$conn->close();
?>
