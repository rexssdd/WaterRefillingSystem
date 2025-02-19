<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "wrsystem";
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$user_id = 1; // Replace with actual logged-in user ID

// Fetch order history
$order_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$order_result = $conn->query($order_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: black; color: #d4af37; font-family: 'Poppins', sans-serif; }
        .container { margin-top: 50px; }
        .table { color: #d4af37; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order History</h2>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td>P<?= number_format($order['total'], 2) ?></td>
                        <td><?= ucfirst($order['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
