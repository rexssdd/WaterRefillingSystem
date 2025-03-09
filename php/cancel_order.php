<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// Process order cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Create a connection to the database
    $conn = new mysqli("localhost", "root", "", "wrsystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start transaction to handle cancellation and stock update atomically
    $conn->begin_transaction();

    try {
        // Fetch product_id and quantity associated with the order
        $stmt_order = $conn->prepare("SELECT product_id, quantity FROM orders WHERE order_id = ? AND status = 'pending'");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $stmt_order->bind_result($product_id, $quantity);
        $stmt_order->fetch();
        $stmt_order->close();

        if (!$product_id) {
            throw new Exception("Order not found or already cancelled");
        }

        // Update order status to "cancelled"
        $stmt_cancel = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND status = 'pending'");
        $stmt_cancel->bind_param("i", $order_id);
        if (!$stmt_cancel->execute()) {
            throw new Exception("Error cancelling order.");
        }

        // Get the current stock of the product
        $stmt_stock = $conn->prepare("SELECT stock FROM product WHERE product_id = ?");
        $stmt_stock->bind_param("i", $product_id);
        $stmt_stock->execute();
        $stmt_stock->bind_result($current_stock);
        $stmt_stock->fetch();
        $stmt_stock->close();

        if ($current_stock === null) {
            throw new Exception("Product not found.");
        }

        // Update the stock (add the quantity back)
        $new_stock = $current_stock + $quantity;
        $stmt_update_stock = $conn->prepare("UPDATE product SET stock = ? WHERE product_id = ?");
        $stmt_update_stock->bind_param("ii", $new_stock, $product_id);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error updating stock.");
        }

        // Log the stock update in inventory logs
        $adjustment_type = 'restock';
        $reason = "Order cancelled, stock reverted.";
        $stmt_log = $conn->prepare("INSERT INTO inventory_stock_logs (product_id, previous_stock, new_stock, adjustment_type, reason) 
                                   VALUES (?, ?, ?, ?, ?)");
        $stmt_log->bind_param("iiiss", $product_id, $current_stock, $new_stock, $adjustment_type, $reason);
        if (!$stmt_log->execute()) {
            throw new Exception("Failed to log stock adjustment.");
        }

        // Commit the transaction
        $conn->commit();

        // Store success message in session for display on the orders page
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Order cancelled and stock updated successfully.'
        ];

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Store error message in session for display on the orders page
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => $e->getMessage()
        ];
    } finally {
        // Close all database connections
        $conn->close();

        // Redirect back to orders page with the notification
        header("Location: /USER_DASHBOARD/order.php");
        exit();
    }
}
?>
