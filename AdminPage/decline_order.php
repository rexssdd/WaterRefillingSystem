<?php
include_once('/xampp/htdocs/WaterRefillingSystem/php/adminlogin.php');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST["order_id"] ?? null;

    if ($order_id) {
        $sql = "UPDATE orders SET status = 'declined' WHERE order_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            echo json_encode(["error" => "Failed to prepare statement."]);
            exit;
        }

        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order declined successfully."]);
        } else {
            echo json_encode(["error" => "Failed to decline order."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid order ID."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid request method."]);
}
?>
