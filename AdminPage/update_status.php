<?php
// Assuming you've established a database connection earlier
$conn = new mysqli('localhost', 'root', '', 'wrsystem');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the product ID from the POST request
$product_id = $_POST['product_id'];

// Get the current status of the product from the database
$sql = "SELECT `status` FROM `product` WHERE `product_id` = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_status = $row['status'];
    
    // Toggle the status
    $new_status = ($current_status == 'available') ? 'unavailable' : 'available';
    
    // Update the status in the database
    $update_sql = "UPDATE `product` SET `status` = '$new_status' WHERE `product_id` = $product_id";
    
    if ($conn->query($update_sql) === TRUE) {
        // Send back the new status as a response
        echo json_encode(['success' => true, 'newStatus' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}

$conn->close();
?>
