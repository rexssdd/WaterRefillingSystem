<?php

include 'dbconnect.php'; // Ensure this file contains your database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["register_username"]);
    $email = trim($_POST["register_email"]);
    $contact = trim($_POST["register_contact"]);
    $password = trim($_POST["register_password"]);

    if (empty($username) || empty($email) || empty($contact) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    if (!preg_match('/^\d{11}$/', $contact)) {
        echo json_encode(["success" => false, "message" => "Invalid contact number"]);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (username, email, contact_number, PasswordHash) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $contact, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User registered successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
