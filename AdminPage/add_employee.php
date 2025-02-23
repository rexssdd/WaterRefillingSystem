<?php

include_once('/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $position = trim($_POST["position"]);
    $salary = trim($_POST["salary"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash password
    $email = trim($_POST["email"]);
    $contact_number = trim($_POST["contact_number"]);
    $status = "Active"; // Default status

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert into employees table
        $stmt1 = $conn->prepare("INSERT INTO employees (name, position, salary, status) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param("ssds", $name, $position, $salary, $status);
        $stmt1->execute();

        // Get last inserted employee_id
        $employee_id = $conn->insert_id;

        // Insert into employee_data table
        $stmt2 = $conn->prepare("INSERT INTO employee_data (username, password, email, contact_number, position, employee_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssssi", $username, $password, $email, $contact_number, $position, $employee_id);
        $stmt2->execute();

        // Commit transaction
        $conn->commit();

        echo "Employee added successfully!";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction if error occurs
        echo "Error: " . $e->getMessage();
    }

    // Close statements
    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>
