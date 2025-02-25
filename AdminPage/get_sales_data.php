<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('/xampp/htdocs/WaterRefillingSystem/php/adminlogin.php');

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT sale_date, sale_amount, profit, loss FROM sales";
$result = $conn->query($sql);

$salesData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $salesData[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($salesData);
?>
