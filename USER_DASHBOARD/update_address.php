<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'wrsystem');

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get and sanitize inputs
$address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
$barangay = trim($_POST['barangay'] ?? '');
$street = trim($_POST['street'] ?? '');
$landmark = trim($_POST['landmark'] ?? '');

// Validate inputs
if ($address_id <= 0 || empty($barangay) || empty($street)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    exit;
}

// Prepare and execute update statement
$stmt = $conn->prepare("UPDATE address SET barangay = ?, street = ?, landmark = ? WHERE address_id = ?");
$stmt->bind_param("sssi", $barangay, $street, $landmark, $address_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Address updated successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update address: ' . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();
?>
