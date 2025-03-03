<?php
header('Content-Type: application/json');
include 'dbconnect.php'; // Adjust this path for your setup

$response = [];

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $address_id = $_POST['address_id'] ?? null;
        $user_id = 1; // Replace this with the actual user session ID
        $street = $_POST['street'];
        $barangay = $_POST['barangay'];
        $district = $_POST['district'];
        $city = $_POST['city'];
        $province = $_POST['province'];

        if ($address_id) {
            // Update existing address
            $stmt = $conn->prepare("UPDATE address SET street=?, barangay=?, district=?, city=?, province=? WHERE address_id=?");
            $stmt->bind_param("sssssi", $street, $barangay, $district, $city, $province, $address_id);
        } else {
            // Insert new address
            $stmt = $conn->prepare("INSERT INTO address (user_id, street, barangay, district, city, province) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $user_id, $street, $barangay, $district, $city, $province);
        }

        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Address saved successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to save address.'];
        }

        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Invalid request.'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

$conn->close();
echo json_encode($response);
?>
