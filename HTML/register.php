<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wrsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $_SESSION['toast_message'] = ["status" => "error", "message" => "Connection failed: " . $conn->connect_error];
    header("Location: getstartedPage.php");
    exit();
}

// Handle Registration
if (isset($_POST['register_submit'])) {
    $username = trim($_POST['register_username']);
    $email = trim($_POST['register_email']);
    $contact = trim($_POST['register_contact']);
    $password = $_POST['register_password'];
    $confirm_password = $_POST['register_confirm_password'];

    // Validate input fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "All fields are required."];
        header("Location: getstartedPage.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Invalid email format."];
        header("Location: getstartedPage.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Passwords do not match."];
        header("Location: getstartedPage.php");
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Username or email already taken."];
        header("Location: getstartedPage.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO user (username, password, email, contact_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $contact);

    if ($stmt->execute()) {
        $_SESSION['toast_message'] = ["status" => "success", "message" => "Registration successful!"];
    } else {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Error: " . $stmt->error];
    }
    header("Location: getstartedPage.php");
    exit();
}

// Handle Login
if (isset($_POST['login_submit'])) {
    $login_input = trim($_POST['login_email_or_username']);
    $password = $_POST['login_password'];

    if (empty($login_input) || empty($password)) {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "Please enter both username/email and password."];
        header("Location: getstartedPage.php");
        exit();
    }

    if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    }
    
    $stmt->bind_param("s", $login_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.html");
            exit();
        } else {
            $_SESSION['toast_message'] = ["status" => "error", "message" => "Invalid password."];
        }
    } else {
        $_SESSION['toast_message'] = ["status" => "error", "message" => "User not found."];
    }
    header("Location: getstartedPage.php");
    exit();
}

$conn->close();
?>
