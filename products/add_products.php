<?php
// Include your database connection file
include "dbconnect.php"; // Change the path as per your setup

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form
    $name = $conn->real_escape_string($_POST["name"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $stock = intval($_POST["stock"]);
    $price = floatval($_POST["price"]);
    $product_type = $conn->real_escape_string($_POST["product_type"]);
    $status = "available"; // Default status

    // Generate a random product ID manually (bypassing AUTO_INCREMENT)
    $product_id = strtoupper(uniqid('P', true)); // Random ID like "P5f7d80b8c7d2e1"

    // Handle file upload
    $photo = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create upload directory if it doesn't exist
        }

        $photo = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $upload_dir . $photo;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    }

    // Prepare the SQL query to insert product data into the database
    $sql = "INSERT INTO product (product_id, name, description, stock, price, photo, status, product_type)
            VALUES ('$product_id', '$name', '$description', $stock, $price, '$photo', '$status', '$product_type')";

    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
</head>
<body>

    <h2>Add Product</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" value="0" required><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="photo">Photo:</label>
        <input type="file" id="photo" name="photo"><br><br>

        <label for="product_type">Product Type:</label>
        <select id="product_type" name="product_type" required>
            <option value="normal">Normal</option>
            <option value="refill">Refill</option>
            <option value="renting">Renting</option>
        </select><br><br>

        <button type="submit">Add Product</button>
    </form>

</body>
</html>
