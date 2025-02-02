<?php
// Start session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'waterrefillingdatabase';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        // Validate input
        if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['contact_number'])) {
            die("All fields are required.");
        }

        // Sanitize input
        $username = htmlspecialchars(strip_tags($_POST['username']));
        $password = $_POST['password'];
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $contact_number = htmlspecialchars(strip_tags($_POST['contact_number']));
        $registration_date = date("Y-m-d"); // Store current date

        if (!$email) {
            die("Invalid email format.");
        }

        // Insert user into database (correct column names)
        $stmt = $pdo->prepare("INSERT INTO user (username, password, email, contact_number, registration_date) VALUES (:username, :password, :email, :contact_number, :registration_date)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':registration_date', $registration_date);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='/WaterRefillingSystem/login.html';</script>";
        } else {
            echo "<script>alert('Registration failed.');</script>";
        }

    } elseif ($action === 'login') {
        if (empty($_POST['username']) || empty($_POST['password'])) {
            die("Username and password are required.");
        }

        $username = htmlspecialchars(strip_tags($_POST['username']));
        $password = $_POST['password'];

        // Check user credentials (correct column names)
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: /WaterRefillingSystem/dashboard.php"); // Redirect to dashboard
            exit;
        } else {
            echo "<script>alert('Invalid username or password.');</script>";
        }
    } else {
        echo "<script>alert('Invalid action.');</script>";
    }
}
?>
