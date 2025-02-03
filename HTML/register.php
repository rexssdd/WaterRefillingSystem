<?php
// Database connection
$servername = "localhost"; // Update with your server details
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "waterrefillingdatabase"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Getting form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if the password and confirm password match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match. Please try again.";
    } else {
        // Hash the password before storing it in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Get the current date for registration
        $registration_date = date('Y-m-d');

        // Prepare the SQL statement to insert data into the database
        $stmt = $conn->prepare("INSERT INTO `user` (`username`, `password`, `email`, `contact_number`, `registration_date`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashedPassword, $email, $contact_number, $registration_date);

        // Execute the query
        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement and connection
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>
