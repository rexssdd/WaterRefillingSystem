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

// Check if the required data is set
if (isset($_POST['employee_id']) && isset($_POST['name']) && isset($_POST['position']) && isset($_POST['salary'])) {
    $employee_id = $_POST['employee_id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    
    // Prepare the SQL query to update the employee details
    // No need to bind $status as it is no longer in use
    $stmt = $conn->prepare("UPDATE employees SET name = ?, position = ?, salary = ? WHERE employee_id = ?");
    
    // Bind parameters: "ssdi" stands for string, string, double (for salary), and integer (for employee_id)
    $stmt->bind_param("ssdi", $name, $position, $salary, $employee_id);

    // Execute the query
    if ($stmt->execute()) {
        echo "Employee updated successfully!";
    } else {
        echo "Error updating employee: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Invalid input data!";
}

// Close the database connection
$conn->close();
?>
