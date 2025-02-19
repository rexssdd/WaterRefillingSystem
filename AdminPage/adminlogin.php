<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "wrsystem");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $checkUser = $mysqli->query("SELECT * FROM admin WHERE username='$username'");
    if ($checkUser->num_rows > 0) {
        echo "<script>alert('Username already exists');</script>";
    } else {
        $mysqli->query("INSERT INTO admin (username, password) VALUES ('$username', '$password')");
        echo "<script>alert('Registration successful! You can now log in.');</script>";
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $result = $mysqli->query("SELECT * FROM admin WHERE username='$username'");
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            header("Location: adminproducts.php");
            exit();
        } else {
            echo "<script>alert('Invalid credentials');</script>";
        }
    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login & Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url("/images/bg-3.jpg") no-repeat center center/cover;
        }
        .container {
            width: 300px;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px 0px #0000001a;
            border-radius: 10px;
            text-align: center;
        }
        input {
            width: 250px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="register">Register</button>
        </form>
            
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login">Login</button>
            <div class="login_signup">Don't have an account? <a href="#" id="signup">Sign Up</a></div>
        </form>
    </div>
</body>
</html>
