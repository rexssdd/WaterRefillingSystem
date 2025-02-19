<?php
require '/WaterRefillingSystem/php/dbconnect.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["productName"];
    $price = $_POST["productPrice"];
    $stock = $_POST["productStock"];
    $description = $_POST["productDescription"];
    $type = $_POST["productType"];
    $status = "available"; // Default status

    // Handle Image Upload
    $imagePath = null;
    if (isset($_FILES["productImage"]) && $_FILES["productImage"]["error"] == 0) {
        $imageDir = "uploads/";
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }
        $imagePath = $imageDir . basename($_FILES["productImage"]["name"]);
        move_uploaded_file($_FILES["productImage"]["tmp_name"], $imagePath);
    }

    // Insert into database
    $sql = "INSERT INTO product (name, stock, price, photo, status, product_type) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidsis", $name, $stock, $price, $imagePath, $status, $type);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    $stmt->close();
}
?>
