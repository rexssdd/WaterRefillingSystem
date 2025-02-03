<?php
include 'dbconnect.php'; // Include the database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
    if (isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['username'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to fetch user data
        $query = "SELECT * FROM user WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Start session and store user info
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php"); // Redirect to dashboard
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
    }

    // Handle signup
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        // Check if passwords match
        if ($password !== $confirmPassword) {
            echo "Passwords do not match.";
            exit();
        }

        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $query = "INSERT INTO user (username, password, email, contact_number, registration_date) VALUES (?, ?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $hashedPassword, $email, $contact_number);

        if ($stmt->execute()) {
            echo "Registration successful.";
            header("Location: login.php"); // Redirect to login page
        } else {
            echo "Error: " . $stmt->error;
        }
    }
} else {
    echo "Invalid request method.";
}
?>
