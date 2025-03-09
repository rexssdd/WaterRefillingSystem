<?php
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $personnel_id = $_POST['personnel_id'];
    $action = $_POST['action'];

    // Check if employee exists
    $checkEmployee = "SELECT personnel_id FROM employees WHERE personnel_id = ?";
    $stmt = $conn->prepare($checkEmployee);
    $stmt->bind_param("i", $personnel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Employee does not exist!'); window.location.href = 'dtr_page.php';</script>";
        exit();
    }

    if ($action == "login") {
        // Check if the employee is already logged in
        $checkQuery = "SELECT * FROM employee_dtr WHERE personnel_id = ? AND logout_time IS NULL";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $personnel_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Employee is already logged in!'); window.location.href = 'dtr_page.php';</script>";
            exit();
        }

        // Insert login record
        $insertQuery = "INSERT INTO employee_dtr (personnel_id, login_time) VALUES (?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("i", $personnel_id);
        if ($stmt->execute()) {
            echo "<script>alert('Login recorded successfully!'); window.location.href = 'employeelogs.php';</script>";
        } else {
            echo "<script>alert('Error logging in!'); window.location.href = 'employeelogs.php';</script>";
        }
    } elseif ($action == "logout") {
        // Check if the employee has an active login session
        $checkQuery = "SELECT login_id, login_time FROM employee_dtr WHERE personnel_id = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $personnel_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            echo "<script>alert('No active login found for this employee!'); window.location.href = 'employeelogs.php';</script>";
            exit();
        }

        $login_id = $row['login_id'];

        // Update the logout time
        $updateLogoutQuery = "UPDATE employee_dtr SET logout_time = NOW() WHERE login_id = ?";
        $stmt = $conn->prepare($updateLogoutQuery);
        $stmt->bind_param("i", $login_id);
        $stmt->execute();

        // Calculate hours, minutes, and seconds worked
        $updateTimeQuery = "UPDATE employee_dtr 
                            SET hours_worked = FLOOR(TIMESTAMPDIFF(SECOND, login_time, logout_time) / 3600),
                                minutes_worked = FLOOR((TIMESTAMPDIFF(SECOND, login_time, logout_time) % 3600) / 60),
                                seconds_worked = TIMESTAMPDIFF(SECOND, login_time, logout_time) % 60
                            WHERE login_id = ?";
        $stmt = $conn->prepare($updateTimeQuery);
        $stmt->bind_param("i", $login_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Logout recorded successfully! Time worked updated.'); window.location.href = 'employeelogs.php';</script>";
        } else {
            echo "<script>alert('Error updating time worked!'); window.location.href = 'dtr_page.php';</script>";
        }
    }
}
?>
