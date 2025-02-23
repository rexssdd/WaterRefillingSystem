<?php

// Start session to check if the admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Admin is not logged in. Please log in first.']);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wrsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$admin_id = $_SESSION['admin_id']; // Get logged-in admin ID

// Check if required POST data is set
if (!isset($_POST['employee_id'], $_POST['name'], $_POST['position'], $_POST['salary'], $_POST['email'], $_POST['contact_number'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing input fields.']);
    exit();
}

$employee_id = filter_var($_POST['employee_id'], FILTER_VALIDATE_INT);
$name = trim($_POST['name']);
$position = trim($_POST['position']);
$salary = filter_var($_POST['salary'], FILTER_VALIDATE_FLOAT);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$contact_number = preg_replace('/\D/', '', $_POST['contact_number']); // Remove non-numeric characters

// Additional validation
if (!$employee_id || !$salary || $salary <= 0 || empty($name) || empty($position) || empty($email) || empty($contact_number)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input values.']);
    exit();
}

if (strlen($contact_number) < 10 || strlen($contact_number) > 15) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid contact number length.']);
    exit();
}

// Fetch old employee details before updating
$query_old = "SELECT e.name, e.position, e.salary, ed.email, ed.contact_number 
              FROM employees e 
              LEFT JOIN employee_data ed ON e.employee_id = ed.employee_id 
              WHERE e.employee_id = ?";
$stmt_old = $conn->prepare($query_old);
$stmt_old->bind_param("i", $employee_id);
$stmt_old->execute();
$stmt_old->store_result();

if ($stmt_old->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Employee not found.']);
    exit();
}

$stmt_old->bind_result($old_name, $old_position, $old_salary, $old_email, $old_contact_number);
$stmt_old->fetch();
$stmt_old->close();

// Determine modification type
$modification_type = [];
if ($old_salary != $salary) $modification_type[] = 'Salary Change';
if ($old_position != $position) $modification_type[] = 'Position Change';
if ($old_email != $email) $modification_type[] = 'Email Change';
if ($old_contact_number != $contact_number) $modification_type[] = 'Contact Change';

if (empty($modification_type)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No changes detected.']);
    exit();
}

$modification_type_str = implode(', ', $modification_type);

// Begin transaction to ensure consistency
$conn->begin_transaction();

try {
    // Update employee details in `employees` table
    $query_update = "UPDATE employees SET name = ?, position = ?, salary = ? WHERE employee_id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("ssdi", $name, $position, $salary, $employee_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Update employee contact details in `employee_data` table
    $query_update_data = "UPDATE employee_data SET email = ?, contact_number = ? WHERE employee_id = ?";
    $stmt_update_data = $conn->prepare($query_update_data);
    $stmt_update_data->bind_param("ssi", $email, $contact_number, $employee_id);
    $stmt_update_data->execute();
    $stmt_update_data->close();

    // Insert into `employee_modification_logs`
    $query_log = "INSERT INTO employee_modification_logs (admin_id, employee_id, old_salary, new_salary, old_position, new_position, old_email, new_email, old_contact, new_contact, modification_type, modified_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_log = $conn->prepare($query_log);
    $stmt_log->bind_param("iidssssssss", $admin_id, $employee_id, $old_salary, $salary, $old_position, $position, $old_email, $email, $old_contact_number, $contact_number, $modification_type_str);
    $stmt_log->execute();
    $stmt_log->close();

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Employee details updated successfully!']);
} catch (Exception $e) {
    // Rollback transaction on failure
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update employee details.', 'error' => $e->getMessage()]);
}

$conn->close();
?>
