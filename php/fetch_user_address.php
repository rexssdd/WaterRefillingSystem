<?php
header('Content-Type: application/json');
session_start(); // Ensure session is started
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

$userId = $_SESSION['user_id'];

$sql = "SELECT address_id, barangay, street, landmark, note FROM address WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["success" => false, "message" => "SQL execute failed: " . $stmt->error]);
    exit;
}

$addresses = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(["success" => true, "data" => $addresses]);
?>
