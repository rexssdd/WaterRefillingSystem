<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dbconnect.php';

header('Content-Type: application/json');
session_start();

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit;
}

// Read JSON input
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
    exit;
}

// Extract and validate inputs
$barangay = $data['barangay'] ?? null;
$street = $data['street'] ?? null;
$landmark = $data['landmark'] ?? null;
$note = $data['note'] ?? null;

if (!$barangay || !$street) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide barangay and street.']);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Prepare and execute the insert statement
$stmt = $conn->prepare("INSERT INTO address (user_id, barangay, street, landmark, note) VALUES (?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param('issss', $user_id, $barangay, $street, $landmark, $note);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Address added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add address: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Statement preparation failed.']);
}

$conn->close();
?>
