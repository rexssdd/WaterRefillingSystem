<?php
header('Content-Type: application/json');
ob_clean();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d');
    $total = 0;

    // Fetch cart items with product prices and stock
    $cartQuery = $conn->prepare("SELECT c.product_id, c.quantity, p.price, p.stock FROM cart c JOIN product p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $cartQuery->bind_param("i", $user_id);
    $cartQuery->execute();
    $result = $cartQuery->get_result();
    $cart = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($cart)) {
        echo json_encode(["success" => false, "message" => "Your cart is empty."]);
        exit;
    }

    // Validate stock
    foreach ($cart as $item) {
        if ($item['quantity'] > $item['stock']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock for some items.']);
            exit;
        }
    }

    // Fetch user's address
    $address_id = $_POST['address_id'] ?? null;

    if (!$address_id) {
        $addressQuery = $conn->prepare("SELECT address_id FROM address WHERE user_id = ? LIMIT 1");
        $addressQuery->bind_param("i", $user_id);
        $addressQuery->execute();
        $addressResult = $addressQuery->get_result();

        if ($addressResult->num_rows > 0) {
            $addressRow = $addressResult->fetch_assoc();
            $address_id = intval($addressRow['address_id']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No delivery address found.']);
            exit;
        }
    }

    if (!$address_id) {
        echo json_encode(['success' => false, 'message' => 'Address ID is missing.']);
        exit;
    }

    // Get payment details
    $paymentMethod = isset($_POST['paymentMethod']) ? trim($_POST['paymentMethod']) : 'cod';
    if (empty($paymentMethod)) {
        $paymentMethod = 'cod'; // Default to COD
    }

    $moneyReceived = isset($_POST['money']) ? floatval($_POST['money']) : $total;
    $change = max(0, $moneyReceived - $total);
    $paymentStatus = ($paymentMethod === 'gcash') ? 'paid' : 'unpaid';

    // Start transaction
    $conn->begin_transaction();

    try {
        // ✅ Step 1: Insert order
        $status = "pending";
        $ordersStmt = $conn->prepare("
            INSERT INTO orders (user_id, order_date, product_id, quantity, total, status, address_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
    
        $updateStockStmt = $conn->prepare("UPDATE product SET stock = stock - ? WHERE product_id = ?");
        $logStockStmt = $conn->prepare("
            INSERT INTO inventory_stock_logs (product_id, previous_stock, new_stock, adjustment_type, reason)
            VALUES (?, ?, ?, 'deduction', ?)
        ");
    
        foreach ($cart as $item) {
            $product_id = $item['product_id'];
            $quantity = intval($item['quantity']);
            $price = $item['price'];
            $subtotal = $price * $quantity;
            $total += $subtotal;
    
            $ordersStmt->bind_param("issidsi", $user_id, $order_date, $product_id, $quantity, $subtotal, $status, $address_id);
    
            if (!$ordersStmt->execute()) {
                throw new Exception('Order insertion failed: ' . $ordersStmt->error);
            }
    
            $order_id = $conn->insert_id; // ✅ Get the order_id
    
            // ✅ Step 2: Insert into `transaction` table
            $insertTransactionStmt = $conn->prepare("
                INSERT INTO transaction (order_id, payment_type, total_price)
                VALUES (?, ?, ?)
            ");
    
            $insertTransactionStmt->bind_param("isd", $order_id, $paymentMethod, $total);
    
            if (!$insertTransactionStmt->execute()) {
                throw new Exception('Transaction insertion failed: ' . $insertTransactionStmt->error);
            }
    
            $transaction_id = $conn->insert_id; // ✅ Get the transaction_id
    
            // ✅ Step 3: Insert into `payment_transaction_logs`
            $insertPaymentLogStmt = $conn->prepare("
                INSERT INTO payment_transaction_logs (order_id, transaction_id, amount, payment_method, status, details)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
    
            $paymentStatus = ($paymentMethod === 'gcash') ? 'paid' : 'unpaid';
            $details = "Payment for order #$order_id";
            $insertPaymentLogStmt->bind_param("iidsss", $order_id, $transaction_id, $total, $paymentMethod, $paymentStatus, $details);
    
            if (!$insertPaymentLogStmt->execute()) {
                throw new Exception('Payment log insertion failed: ' . $insertPaymentLogStmt->error);
            }
    
            // ✅ Step 4: Reduce stock and log inventory change
            $previousStock = $item['stock'];
            $newStock = $previousStock - $quantity;
            $reason = "Stock reduced due to order #$order_id by user #$user_id";
    
            $updateStockStmt->bind_param("ii", $quantity, $product_id);
            $updateStockStmt->execute();
    
            $logStockStmt->bind_param("iiis", $product_id, $previousStock, $newStock, $reason);
            $logStockStmt->execute();
        }
    
        // ✅ Step 5: Clear user's cart
        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param("i", $user_id);
        $clearCartStmt->execute();
    
        $conn->commit();
    
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id]);
    
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Order placement failed: ' . $e->getMessage()]);
    }
    }
?>
