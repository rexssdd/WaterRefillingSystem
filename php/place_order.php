<?php
header('Content-Type: application/json');
ob_clean(); // Clears any previous output (like HTML errors)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Session error: user not logged in."]);
    exit;
}

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Fetch cart items with product prices and costs
    $cartQuery = $conn->prepare("
        SELECT c.product_id, c.quantity, m.price, m.cost
        FROM cart c
        JOIN product m ON c.product_id = m.product_id
        WHERE c.user_id = ?
    ");
    $cartQuery->bind_param("i", $user_id);
    $cartQuery->execute();
    $result = $cartQuery->get_result();
    $cart = $result->fetch_all(MYSQLI_ASSOC);

    $order_date = date('Y-m-d'); // Gets the current date in YYYY-MM-DD format
  
    $total = 0;
    $totalCost = 0;
    $quantities = $_POST['quantity'] ?? [];
    $validQuantities = true;

    foreach ($cart as $item) {
        $product_id = $item['product_id'];
        $quantity = intval($quantities[$product_id] ?? $item['quantity']);
        
        if ($quantity <= 0) {
            $validQuantities = false;
            break;
        }
    }
    
    if (!$validQuantities) {
        die(json_encode(['success' => false, 'message' => 'Invalid quantity for one or more items.']));
    }

    // Fetch the user's address if not provided
    $address_id = $_POST['address_id'] ?? null;

    if (!$address_id) {
        $addressQuery = $conn->prepare("SELECT address_id FROM address WHERE user_id = ? LIMIT 1");
        $addressQuery->bind_param("i", $user_id);
        $addressQuery->execute();
        $addressResult = $addressQuery->get_result();
        
        if ($addressResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No delivery address found.']);
            exit;
        }
        
        $addressRow = $addressResult->fetch_assoc();
        $address_id = $addressRow['address_id'] ?? null;
        
        if (!$address_id) {
            echo json_encode(['success' => false, 'message' => 'No delivery address found.']);
            exit;
        }
    }

    
    $conn->begin_transaction();

    
    try {
        $status = "pending"; // Order status should be "pending"
        
        // Log status to error log for debugging
        error_log("Order status: " . $status); // This will log the status to the PHP error log
        
        // Insert the order into the database
        $ordersStmt = $conn->prepare("
        INSERT INTO orders (user_id, order_date, product_id, quantity, total, status, address_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($cart as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $cost = $item['cost'];

            $subtotal = $price * $quantity;
            $totalCost += $cost * $quantity;
            $total += $subtotal;

            $ordersStmt->bind_param("issidss", $user_id, $order_date, $product_id, $quantity, $subtotal, $status, $address_id);
            $ordersStmt->execute();
            
        }
     
        // Get the last inserted order_id
        $order_id = $conn->insert_id;
        if (!$order_id) {
            error_log("Order insertion failed. Order ID is not available.");
            die(json_encode(['success' => false, 'message' => 'Order placement failed: Order ID not found.']));
        }
        
        // Payment handling
        $paymentMethod = $_POST['paymentMethod'] ?? 'cod';
        $moneyReceived = isset($_POST['money']) ? floatval($_POST['money']) : $total;
        $change = max(0, $moneyReceived - $total);
        $paymentStatus = ($paymentMethod === 'gcash') ? 'paid' : 'unpaid';

        // Insert into transaction
        $transactionStmt = $conn->prepare("
            INSERT INTO transaction (order_id, payment_type, total_price)
            VALUES (?, ?, ?)
        ");
        $transactionStmt->bind_param("isd", $order_id, $paymentMethod, $total);
        $transactionStmt->execute();
        $transaction_id = $conn->insert_id;

        // Log payment transaction
        $logStmt = $conn->prepare("
            INSERT INTO payment_transaction_logs (order_id, transaction_id, amount, payment_method, status, details)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $details = ($paymentMethod === 'gcash') ? "Payment completed via GCash" : "Payment pending for COD";
        $logStmt->bind_param("iissss", $order_id, $transaction_id, $total, $paymentMethod, $paymentStatus, $details);
        $logStmt->execute();

        // Calculate profit and ROI
        $profit = $total - $totalCost;
        $roi = ($totalCost > 0) ? ($profit / $totalCost) * 100 : 0;

        // Insert sales record
        $salesStmt = $conn->prepare("
            INSERT INTO sales (user_id, product_id, quantity, sale_amount, profit, roi, payment_method)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($cart as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $subtotal = $item['price'] * $quantity;
            $productCost = $item['cost'] * $quantity;
            $productProfit = $subtotal - $productCost;
            $productROI = ($productCost > 0) ? ($productProfit / $productCost) * 100 : 0;

            $salesStmt->bind_param("iiiddds", $user_id, $product_id, $quantity, $subtotal, $productProfit, $productROI, $paymentMethod);
            $salesStmt->execute();
        }

        $conn->commit();

        // Clear cart after successful order
        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param("i", $user_id);
        $clearCartStmt->execute();

        echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Order placement failed: ' . $e->getMessage()]);
    }
}
?>
