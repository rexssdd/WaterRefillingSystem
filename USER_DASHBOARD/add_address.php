<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'wrsystem');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get user ID (assuming logged-in user)
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

// Get and sanitize inputs
$barangay = trim($_POST['barangay'] ?? '');
$street = trim($_POST['street'] ?? '');
$landmark = trim($_POST['landmark'] ?? '');
$note = trim($_POST['note'] ?? '');

// Validate inputs
if (empty($barangay) || empty($street)) {
    echo json_encode(['success' => false, 'error' => 'Barangay and Street are required.']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Prepare and execute query
    $stmt = $conn->prepare("INSERT INTO address (user_id, barangay, street, landmark, note) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $barangay, $street, $landmark, $note);

    if ($stmt->execute()) {
        $conn->commit(); // Commit transaction
        echo json_encode(['success' => true, 'message' => 'Address added successfully.']);
    } else {
        throw new Exception('Failed to add address: ' . $stmt->error);
    }
} catch (Exception $e) {
    $conn->rollback(); // Roll back if an error occurs
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
