<?php
// Database connection
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = "";     // Your MySQL password
$database = "waterrefillingdatabase"; // Your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login and registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action === "register") {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];

        $stmt = $conn->prepare("INSERT INTO user (username, password, email, contact_number, registration_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $password, $email, $contact_number);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='/Login&Register/login.php';</script>";
        } else {
            echo "<script>alert('Registration failed!');</script>";
        }

        $stmt->close();

    } elseif ($action === "login") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            echo "<script>alert('Login successful!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Invalid username or password!');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Register</title>
    <link rel="stylesheet" href="/WaterRefillingSystem/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div class="forms" id="form-container">
            <!-- Login Form -->
            <div class="form1" id="login-form">
                <h2>Login</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <div class="switch" onclick="showRegister()">Don't have an account? Register</div>
            </div>

            <!-- Register Form -->
            <div class="form" id="register-form">
                <h2>Register</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="contact_number" placeholder="Contact Number" required>
                    <button type="submit">Register</button>
                </form>
                <div class="switch" onclick="showLogin()">Already have an account? Login</div>
            </div>
        </div>
    </div>
    <script src="/WaterRefillingSystem/javascript/script.js"></script>
</body>
</html>
