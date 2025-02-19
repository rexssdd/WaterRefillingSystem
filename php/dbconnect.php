<?php
$host = "localhost"; // Change if using a different host
$dbname = "wrsystem"; // Ensure this is correct
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Exception: " . $e->getMessage()]);
    exit();
}
?>
