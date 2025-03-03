<?php
require 'dbconnect.php'; // Database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address_id = $_POST['address_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $barangay = $_POST['barangay'] ?? '';
    $street = $_POST['street'] ?? '';
    $landmark = $_POST['landmark'] ?? '';
    $note = $_POST['note'] ?? '';

    if ($address_id && $user_id) {
        $stmt = $conn->prepare("UPDATE address SET barangay = ?, street = ?, landmark = ?, note = ? WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $barangay, $street, $landmark, $note, $address_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Address updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update address."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
