<?php
// Database connection
$host = "localhost"; // Your database host
$username = "root"; // Your database username
$password = ""; // Your database password
$database = "wrsystem"; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the products from the database
$sql = "SELECT * FROM product WHERE status = 'available'"; // Fetch only available products
$result = $conn->query($sql);

// Check if there are products
$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row; // Store product data in the array
    }
}


// Return products as JSON
echo json_encode($products);
?>
