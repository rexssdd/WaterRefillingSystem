<?php
header('Content-Type: application/json'); // Ensure JSON response
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

try {
    // Database connection
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "wrsystem";

    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $response["error"] = "Invalid request method.";
        echo json_encode($response);
        exit;
    }

    // Sanitize and trim inputs
    $username = trim($_POST["register_username"] ?? '');
    $email = trim($_POST["register_email"] ?? '');
    $contact = trim($_POST["register_contact"] ?? '');
    $password = trim($_POST["register_password"] ?? '');
    $confirmPassword = trim($_POST["register_confirm_password"] ?? '');

    // Validation checks
    if (empty($username) || empty($email) || empty($contact) || empty($password) || empty($confirmPassword)) {
        $response["error"] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = "Invalid email format.";
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^09\d{9}$/', $contact)) {
        $response["error"] = "Contact number must start with '09' and be exactly 11 digits.";
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirmPassword) {
        $response["error"] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    // Check for existing user
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ? OR contact_number = ?");
    $stmt->bind_param("sss", $username, $email, $contact);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response["error"] = "Username, email, or contact number is already registered.";
        echo json_encode($response);
        exit;
    }

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO user (username, email, contact_number, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $contact, $hashedPassword);

    if ($stmt->execute()) {
        $response["success"] = "Registration successful! Login now!";
    } else {
        $response["error"] = "Database error: " . $stmt->error;
    }
} catch (Exception $e) {
    $response["error"] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>
