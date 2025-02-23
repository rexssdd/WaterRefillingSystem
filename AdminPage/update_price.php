<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['product_id']) || !isset($_POST['new_price'])) {
        echo json_encode(['success' => false, 'message' => 'Missing input fields']);
        exit;
    }

    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $new_price = filter_var($_POST['new_price'], FILTER_VALIDATE_FLOAT);

    if (!$product_id || !$new_price || $new_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input values']);
        exit;
    }

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection error']);
        exit;
    }

    // Fetch old price before updating
    $query_old_price = "SELECT price FROM product WHERE product_id = ?";
    $stmt_old = $conn->prepare($query_old_price);
    if (!$stmt_old) {
        echo json_encode(['success' => false, 'message' => 'Database error fetching old price']);
        exit;
    }
    $stmt_old->bind_param('i', $product_id);
    $stmt_old->execute();
    $stmt_old->store_result();
    
    if ($stmt_old->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $stmt_old->bind_result($old_price);
    $stmt_old->fetch();
    $stmt_old->close();

    // Update price in the database
    $sql = "UPDATE product SET price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare update query']);
        exit;
    }
    $stmt->bind_param('di', $new_price, $product_id);

    if ($stmt->execute()) {
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['success' => false, 'message' => 'Admin session expired or not logged in']);
            exit;
        }

        $admin_id = filter_var($_SESSION['admin_id'], FILTER_VALIDATE_INT);
        if (!$admin_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid admin ID']);
            exit;
        }

        // Log the update in inventory logs
        $log_sql = "INSERT INTO inventory_price_logs (admin_id, product_id, old_price, new_price, log_time) VALUES (?, ?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        if (!$log_stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare log query']);
            exit;
        }
        $log_stmt->bind_param('iidd', $admin_id, $product_id, $old_price, $new_price);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Price updated and logged successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update price']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
