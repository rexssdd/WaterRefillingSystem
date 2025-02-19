<?php
header('Content-Type: application/json'); // Ensure JSON response
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

try {
    require_once("../php/dbconnect.php"); // Adjust path as needed

    global $pdo;

    if (!$pdo) {
        die(json_encode(["error" => "Database connection failed!"]));
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $response["error"] = "Invalid request method.";
        echo json_encode($response);
        exit;
    }

    $username = trim($_POST["register_username"] ?? '');
    $email = trim($_POST["register_email"] ?? '');
    $contact = trim($_POST["register_contact"] ?? '');
    $password = trim($_POST["register_password"] ?? '');
    $confirmPassword = trim($_POST["register_confirm_password"] ?? '');

    // Check for empty fields
    if (empty($username) || empty($email) || empty($contact) || empty($password) || empty($confirmPassword)) {
        $response["error"] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = "Invalid email format.";
        echo json_encode($response);
        exit;
    }

    // Validate contact number (must start with '09' and have 11 digits)
    if (!preg_match('/^09\d{9}$/', $contact)) {
        $response["error"] = "Contact number must start with '09' and be exactly 11 digits.";
        echo json_encode($response);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $response["error"] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    // Check if username, email, or contact number already exists
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE username = ? OR email = ? OR contact_number = ?");
    $stmt->execute([$username, $email, $contact]);

    if ($stmt->rowCount() > 0) {
        $response["error"] = "Username, email, or contact number is already registered.";
        echo json_encode($response);
        exit;
    }

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO user (username, email, contact_number, password) VALUES (?, ?, ?, ?)");

    if ($stmt->execute([$username, $email, $contact, $hashedPassword])) {
        $response["success"] = "Registration successful!";
    } else {
        $response["error"] = "Database error.";
    }
} catch (Exception $e) {
    $response["error"] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>
