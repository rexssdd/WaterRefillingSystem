<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];

    // Create a connection to the database
    $conn = new mysqli("localhost", "root", "", "wrsystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start transaction to handle cancellation and stock update atomically
    $conn->begin_transaction();

    try {
        // Fetch product_id and quantity (if added) from rental_orders table
        $stmt_rental = $conn->prepare("SELECT product_id, quantity FROM rental_orders WHERE rental_id = ? AND status = 'pending'");
        $stmt_rental->bind_param("i", $rental_id);
        $stmt_rental->execute();
        $stmt_rental->bind_result($product_id, $quantity); // Assuming quantity is added or default is 1
        $stmt_rental->fetch();
        $stmt_rental->close();

        if (!$product_id) {
            throw new Exception("Rental not found or already cancelled");
        }

        // Update rental order status to "cancelled"
        $stmt_cancel = $conn->prepare("UPDATE rental_orders SET status = 'cancelled' WHERE rental_id = ? AND status = 'pending'");
        $stmt_cancel->bind_param("i", $rental_id);
        if (!$stmt_cancel->execute()) {
            throw new Exception("Error cancelling rental.");
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
        $new_stock = $current_stock + $quantity; // Use quantity from rental
        $stmt_update_stock = $conn->prepare("UPDATE product SET stock = ? WHERE product_id = ?");
        $stmt_update_stock->bind_param("ii", $new_stock, $product_id);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error updating stock.");
        }

        // Log the stock update in inventory logs
        $adjustment_type = 'restock';
        $reason = "Rental cancelled, stock reverted.";
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
            'message' => 'Rental cancelled and stock updated successfully.'
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
        header("Location: /USER_DASHBOARD/orders.php");
        exit();
    }
}
?>
