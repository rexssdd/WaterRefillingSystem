<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wrsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the data sent by AJAX
$employee_id = $_POST['employee_id'];
$status = $_POST['status'];

// Prepare the SQL query to update the employee's status
$sql = "UPDATE employees SET status = ? WHERE employee_id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters to the query
$stmt->bind_param("si", $status, $employee_id);

// Execute the query
if ($stmt->execute()) {
    echo "Status updated successfully!";
} else {
    echo "Error updating status: " . $stmt->error;
}

// Close the connection
$stmt->close();
$conn->close();
?>
