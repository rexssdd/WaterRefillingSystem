<?php
require '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php'; // Ensure this file contains the database connection

header('Content-Type: application/json');

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    $stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $addresses = [];
    while ($row = $result->fetch_assoc()) {
        $addresses[] = $row;
    }

    echo json_encode($addresses);
} else {
    echo json_encode(["error" => "User ID not provided"]);
}

$conn->close();
?>
