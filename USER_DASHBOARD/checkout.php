<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user session variables are properly set
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 0; // Default value if not logged in
}

$note = isset($_POST['note']) ? trim($_POST['note']) : NULL;
$payment_method = $_POST['payment_method']; // Ensure frontend sends payment method
$total_price = 0;
$order_date = date("Y-m-d");

// Fetch cart items
$query = "SELECT c.product_id, c.quantity, p.price, p.stock 
          FROM cart c 
          JOIN product p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Cart is empty. Please add items before placing an order.');
        window.location.href = 'cart.php';
    </script>";
    exit();
}

// Check stock availability before processing the order
$insufficient_stock = [];
while ($row = $result->fetch_assoc()) {
    if ($row['stock'] < $row['quantity']) {
        $insufficient_stock[] = "Product ID: " . $row['product_id'];
    }
}

// If stock is insufficient, show alert and redirect
if (!empty($insufficient_stock)) {
    $stock_message = "The following items have insufficient stock: " . implode(", ", $insufficient_stock) . ".";
    echo "<script>
        alert('$stock_message');
        window.location.href = 'cart.php';
    </script>";
    exit();
}

// Process order
$conn->begin_transaction();
try {
    // Reset result set for reprocessing
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $total_price += $row['quantity'] * $row['price'];

        // Insert into orders table
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, product_id, quantity, total, status) 
                                      VALUES (?, ?, ?, ?, ?, 'pending')");
        $order_stmt->bind_param("isiii", $user_id, $order_date, $row['product_id'], $row['quantity'], $total_price);
        $order_stmt->execute();

        // Reduce stock
        $update_stock = $conn->prepare("UPDATE product SET stock = stock - ? WHERE product_id = ?");
        $update_stock->bind_param("ii", $row['quantity'], $row['product_id']);
        $update_stock->execute();
    }

    // Insert into transaction table
    $transaction_stmt = $conn->prepare("INSERT INTO transaction (order_id, payment_type, total_price, money, `change`, date_time) 
                                        VALUES (LAST_INSERT_ID(), ?, ?, ?, ?, NOW())");
    $money_received = $_POST['money_received'];
    $change = $money_received - $total_price;
    $transaction_stmt->bind_param("sddd", $payment_method, $total_price, $money_received, $change);
    $transaction_stmt->execute();

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    $conn->commit();
    echo "<script>
        alert('Order placed successfully! Your change is PHP $change.');
        window.location.href = 'order_confirmation.php';
    </script>";
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>
        alert('Error: " . $e->getMessage() . "');
        window.location.href = 'cart.php';
    </script>";
    exit();
}
?>
