<?php
session_start();
require '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php'; // Ensure correct DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); // 'admin', 'refiller', or 'delivery'

    if (empty($username) || empty($password)) {
        echo "Please fill in both fields.";
        exit;
    }

    // Admin login
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT admin_id, username, password FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: adminproducts.php");
            exit();
        } else {
            echo "Invalid username or password for admin.";
        }
    }
    
    // Employee login (refiller or delivery)
    elseif ($role === 'refiller' || $role === 'delivery') {
        $stmt = $conn->prepare("SELECT personnel_id, username, password, role FROM employee_data WHERE username = ? AND role = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if ($employee && password_verify($password, $employee['password'])) {
            $_SESSION['user_id'] = $employee['personnel_id'];
            $_SESSION['username'] = $employee['username'];
            $_SESSION['role'] = $employee['role'];

            if ($role === 'refiller') {
                header("Location: refiller_dashboard.php");
            } elseif ($role === 'delivery') {
                header("Location: delivery_dashboard.php");
            }
            exit();
        } else {
            echo "Invalid username or password for $role.";
        }
    }
    
    // Invalid role
    else {
        echo "Invalid role selected.";
    }

    $stmt->close();
    $conn->close();
}
?>
