<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dbconnect.php';

header('Content-Type: application/json');
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit;
}

// Fetch the latest address and note
$stmt = $conn->prepare("SELECT barangay, street, landmark, note FROM address WHERE user_id = ? ORDER BY address_id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data ?: ['status' => 'error', 'message' => 'No address found.']);

$stmt->close();
$conn->close();
?>
