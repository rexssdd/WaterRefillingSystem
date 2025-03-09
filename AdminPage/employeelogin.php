<?php
session_start();
include_once("/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php");

// Check database connection
if (!$conn) {
    die("<script>alert('Database connection failed.');</script>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim(strtolower($_POST['username']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT personnel_id, password, position FROM employee_data WHERE LOWER(username) = ?");
    if (!$stmt) {
        die("<script>alert('SQL error.');</script>");
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $employee['password'])) {
            $_SESSION['personnel_id'] = $employee['personnel_id'];
            $_SESSION['position'] = $employee['position'];

            if (strtolower($employee['position']) == 'refiller') {
                header("Location: refillerpage.php");
                exit();
            } elseif (strtolower($employee['position']) == 'delivery') {
                header("Location: deliverypage.php");
                exit();
            } else {
                echo "<script>alert('Invalid position.'); window.location.href='login.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url("/images/bg-3.jpg") no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
            width: 350px;
        }
        h1 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }
        .input-box {
            margin-bottom: 20px;
        }
        .input-box input {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: url("/images/bg-3.jpg") no-repeat center center/cover;
            color: wheat;
        }
        .container1 {
            display: flex;
            width: 80%;
            max-width: 1200px;
            background: rgba(26, 26, 32, 0.27);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0px 8px 20px rgba(255, 252, 252, 0.8);
            gap: 50px;
        }
        .about-logo {
            width: 450px;
            height: auto;
            border-radius: 10px;
        }
        .divider {
            width: 3px;
            background-color:rgb(241, 241, 241);
            border-radius: 5px;
        }
        .login-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        h1 {
            font-size: 36px;
            text-align: center;
            background: #007bff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    background: rgba(36, 33, 33, 0.36);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0px 4px 15px rgba(212, 212, 212, 0.6);
}

form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
}

.input-box {
    position: relative;
    width: 100%;
}

.input-box input {
    width: 100%;
    padding: 15px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.9);
}

button {
    margin-top: 30px;
    width: 80%;
    padding: 15px;
    background: #007bff;
    border: none;
    font-size: 18px;
    font-weight: bold;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #0056b3;
}
.input-box {
    margin-left: 90px;
    margin-top: 20px;
            position: relative;
            margin-bottom: 10px;
        }
        .input-box input {
            width: 80%;
            padding: 15px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.9);
        }
        .input-box i {
            align-self: center;
            position: absolute;
            right: 120px;
            font-size: 24px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color:rgb(3, 3, 3);
        }
    </style>
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
                               <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

  
</head>
<body>

<section class="container1">
        <img src="/images/Jz.png" alt="JZ Waters Logo" class="about-logo">

        <div class="divider"></div>

        <div class="login-section">
            <div class="container">
                <h2 style="align-self:center;">Employee Login</h2>
                <form method="POST">
                    <div class="input-box">
                        <input type="text" name="username" placeholder="Username" required>
                        <i class="uil uil-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i class="uil uil-eye-slash" id="togglePassword"></i>
                    </div>
                    <button type="submit" name="login">Login</button>
                    <h3 style="align-self:center;">
                <a href="adminlogin.php" style="text-decoration: none; color: inherit;">Login as Admin</a>
            </h3>
         </form>
            </div>
        </div>
    </section>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.classList.toggle('uil-eye-slash');
            this.classList.toggle('uil-eye');
        });
    </script>

</body>
</html>
