<?php
// Start session if not already started
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

var_dump($_SESSION); // Check if session exists
exit;


require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id']; // Get logged-in user ID
$payment_method = $data['payment_method'];
$order_date = date('Y-m-d');

if (empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    exit;
}

$conn->begin_transaction();

try {
    foreach ($data['items'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $total = $item['price'] * $quantity;

        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, product_id, quantity, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiii", $user_id, $order_date, $product_id, $quantity, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id; // Get last inserted order ID
        $stmt->close();

        // Reduce stock in products table
        $stmt = $conn->prepare("UPDATE product SET stock = stock - ? WHERE product_id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();
        $stmt->close();

        // Insert transaction
        $money_received = ($payment_method === 'cod') ? null : $total;
        $stmt = $conn->prepare("INSERT INTO transaction (order_id, payment_type, total_price, money) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isdd", $order_id, $payment_method, $total, $money_received);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
        $stmt->close();

        // Insert payment log
        $stmt = $conn->prepare("INSERT INTO payment_transaction_logs (order_id, transaction_id, amount, payment_method, status, details) VALUES (?, ?, ?, ?, 'unpaid', 'Payment pending')");
        $stmt->bind_param("iids", $order_id, $transaction_id, $total, $payment_method);
        $stmt->execute();
        $stmt->close();
    }

    // Clear the user's cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Order failed: ' . $e->getMessage()]);
}

$conn->close();
?>
