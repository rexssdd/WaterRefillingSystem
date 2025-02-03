<?php
// Start the session
session_start();

// Database connection (replace with your database details)
$servername = "localhost";
$username = "root"; // default username for MySQL
$password = ""; // default password for MySQL
$dbname = "waterrefillingdatabase"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevent SQL injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to find user by email
    $sql = "SELECT * FROM user WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User found, fetch data
        $row = $result->fetch_assoc();
        $stored_password = $row['password']; // Stored hashed password

        // Verify password
        if (password_verify($password, $stored_password)) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];

            // Redirect to dashboard or home page
            header("Location: dashboard.php");
            exit;
        } else {
            // Password is incorrect
            echo "<p style='color:red;'>Invalid password!</p>";
        }
    } else {
        // Email not found
        echo "<p style='color:red;'>Email not found!</p>";
    }
}

$conn->close();
?>
