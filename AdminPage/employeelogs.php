<?php
include '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Fetch active employees
$employeeQuery = "SELECT personnel_id, name FROM employees WHERE status = 'Active'";
$employees = $conn->query($employeeQuery);

// Fetch employees who are currently logged in
$activeEmployeesQuery = "SELECT e.personnel_id, e.name, e.position, d.login_time 
                         FROM employee_dtr d 
                         JOIN employees e ON d.personnel_id = e.personnel_id 
                         WHERE d.logout_time IS NULL";

$activeEmployees = $conn->query($activeEmployeesQuery);

// Fetch employees who have logged out with their positions and payroll
$pastEmployeesQuery = "
    SELECT e.name, e.position, e.salary, d.login_time, d.logout_time, 
           d.hours_worked, d.minutes_worked, d.seconds_worked
    FROM employee_dtr d
    JOIN employees e ON d.personnel_id = e.personnel_id
    WHERE d.logout_time IS NOT NULL
    AND d.logout_time >= NOW() - INTERVAL 15 DAY
";
$pastEmployees = $conn->query($pastEmployeesQuery);

// Initialize an array to store total hours and payroll by employee
// Initialize an array to store total hours, minutes, seconds, and payroll by employee
$employeeData = [];

while ($row = $pastEmployees->fetch_assoc()) {
    $employeeName = $row['name'];
    $hours = $row['hours_worked'];
    $minutes = $row['minutes_worked'];
    $seconds = $row['seconds_worked'];

    // Store or accumulate time and payroll per employee
    if (!isset($employeeData[$employeeName])) {
        $employeeData[$employeeName] = [
            'totalHours' => 0,
            'totalMinutes' => 0,
            'totalSeconds' => 0,
            'totalPayroll' => 0
        ];
    }

    // Accumulate time values
    $employeeData[$employeeName]['totalHours'] += $hours;
    $employeeData[$employeeName]['totalMinutes'] += $minutes;
    $employeeData[$employeeName]['totalSeconds'] += $seconds;
    
    // Convert excess seconds to minutes
    if ($employeeData[$employeeName]['totalSeconds'] >= 60) {
        $employeeData[$employeeName]['totalMinutes'] += floor($employeeData[$employeeName]['totalSeconds'] / 60);
        $employeeData[$employeeName]['totalSeconds'] %= 60;
    }

    // Convert excess minutes to hours
    if ($employeeData[$employeeName]['totalMinutes'] >= 60) {
        $employeeData[$employeeName]['totalHours'] += floor($employeeData[$employeeName]['totalMinutes'] / 60);
        $employeeData[$employeeName]['totalMinutes'] %= 60;
    }

    // Calculate payroll based on total hours worked
    $totalWorkedHours = $hours + ($minutes / 60) + ($seconds / 3600);
    $payroll = $totalWorkedHours * ($row['salary'] / 160);
    
    $employeeData[$employeeName]['totalPayroll'] += $payroll;
}



// Query to fetch past employees' work hours and payroll data
$workHoursPayrollQuery = "
    SELECT e.name, e.position, e.salary, 
           DATE_FORMAT(d.login_time, '%Y-%m-%d %h:%i:%s %p') AS formatted_login_time, 
           DATE_FORMAT(d.logout_time, '%Y-%m-%d %h:%i:%s %p') AS formatted_logout_time, 
           d.hours_worked, d.minutes_worked, d.seconds_worked
    FROM employee_dtr d
    JOIN employees e ON d.personnel_id = e.personnel_id
    WHERE d.logout_time IS NOT NULL
    ORDER BY d.login_time DESC";  // Order by most recent login time first

$workHoursPayrollResult = $conn->query($workHoursPayrollQuery);

// Fetch employee modification logs
$logQuery = "
    SELECT l.log_id, e.name AS employee_name, a.username AS admin_name, 
           l.old_salary, l.new_salary, l.old_position, l.new_position, 
           l.old_email, l.new_email, l.old_contact, l.new_contact, 
           l.modification_type, l.modified_at
    FROM employee_modification_logs l
    JOIN employees e ON l.employee_id = e.personnel_id
    JOIN admin a ON l.admin_id = a.admin_id
    ORDER BY l.modified_at DESC
";
$logResults = $conn->query($logQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee DTR</title>
    <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>

body{

    font-family: 'Poppins', sans-serif;
background: #353544;
}
  .card-header { background-color: #0066cc; color: white; }
  .tab-content { display: none; }
  .tab-content.active { display: block;;
  }

.nav{
    color: wheat;
}

  .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 10px 20px;
  }
  .sidebar a:hover, a.active {
      background: #0056b3;
  }

  .nav-link {
        color: white !important; /* Set link color to wheat */
    }
    
    .nav-link.active {
        color: wheat !important; /* Set active link color to white */
        background-color: #1e1e1e !important; /* Optional: Darker background for active tab */
    }
    .nav-link:hover{
        background-color: #1e1e1e;
    }
/* Sidebar Styling */
.sidebar {
width: 250px;
height: 100vh;
background: #1e1e2f  !important;
padding: 20px 0;
position: fixed;
top: 0;
left: 0;
display: flex;
flex-direction: column;
box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
}

.sidebar a, .dropdown-btn {
display: flex;
align-items: center;
padding: 15px 20px;
color: white;
text-decoration: none;
font-size: 16px;
transition: 0.3s;
cursor: pointer;
}

.sidebar a:hover, .dropdown-btn:hover {
background: #34344a;
}

.nav_logo {
text-align: center;
padding-bottom: 20px;
}

.logo {
width: 80px;
height: auto;
margin-bottom: 10px;
}

.Jz_Waters {
font-size: 20px;
color: white;
}

/* Dropdown Styling */
.dropdown {
display: none;
flex-direction: column;
background: #2a2a3c;
padding-left: 20px;
}

/* Dropdown container */
.dropdown {
display: block;
max-height: 0px;
overflow: hidden;
transition: max-height 0.3s ease-in-out;
}

/* Active dropdown (expands smoothly) */
.dropdown.active {
max-height: 200px; /* Adjust based on content */
}

/* Rotate arrow icon smoothly */
.uil-angle-down {
transition: transform 0.3s ease;
}

.uil-angle-down.rotate {
transform: rotate(180deg);
}


.dropdown a {
padding: 10px 20px;
font-size: 14px;
border-left: 3px solid transparent;
}

.dropdown a:hover {
border-left: 3px solid #007bff;
background: #34344a;
}

.dropdown-btn {
justify-content: space-between;
}

/* Active Dropdown */
.dropdown.active {
display: flex;
}

  .logout {
      position: absolute;
      bottom: 30px;
      width: 100%;
  }
  .content {
      margin-left: 250px;
      padding: 20px;
      width: 100%;
  }
  .card-header{
    color: white;
  }

  table {
  margin-top: 20px;
border-collapse: collapse;
border-radius: 8px;
overflow: hidden;
box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
background: #2c2c2c;
color: #d4af37;
font-family: 'Poppins', sans-serif;
text-align: left;
}

thead {
background: #1e1e1e;
color: #d4af37;
text-transform: uppercase;
letter-spacing: 1px;
}

thead th {
padding: 12px;
border-bottom: 2px solid #d4af37;
}

tbody tr:nth-child(odd) {
background: #333;
}

tbody tr:nth-child(even) {
background: #2a2a2a;
}

tbody tr:hover {
background: #444;
transition: background 0.3s ease-in-out;
}

td {
padding: 12px;
border-bottom: 1px solid #444;
}

th, td {
min-width: 120px;
text-align: center;
}


  .logo{
  width: 40px;  /* Adjust size as needed */
  height: auto;
  gap: 2px;
  margin-right: 20px; 
  }
  strong{
    color: gold;
    font-size: 24px;
  }
  .logout{
    background-color: red;
    border-radius: 14px;
  }
  a{

    display: inline;
    gap: 40px;
    font-size: 18px;
  }

  /* Navbar Styling */
.navbar {
position: fixed;
height: 60px;
background: #515151;
width: 1000px;
color: white;
display: flex;
justify-content: space-between;
border-radius: 50px;
padding: 0 20px;
transition: left 0.3s ease-in-out;
box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Menu Icon */
#menu-icon {
font-size: 24px;
cursor: pointer;
transition: transform 0.3s ease;
}

#menu-icon:hover {
transform: scale(1.1);
}

/* Page Title */
#page-title {
font-size: 22px;
font-weight: bold;
display: flex;
align-items: center;
gap: 10px;
}

.x {
font-size: 24px;
}

/* User Container */
.user-container {
position: relative;
display: flex;
align-items: center;
cursor: pointer;
}

/* User Icon */
.user-icon {
font-size: 24px;
transition: transform 0.3s ease;
}

.user-icon:hover {
transform: scale(1.1);
}


/* Dropdown Menu */
.dropdown-menu {
  display: none;
  position: absolute;
  top: 50px;
  right: 10px;
  background: #232b2b;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  min-width: 120px;
  overflow: hidden;
  z-index: 1000;
}

.dropdown-menu a {
  display: block;
  padding: 10px;
  color: #d4af37;
  text-decoration: none;
  transition: 0.3s;
  text-align: center;
}

.dropdown-menu.active {
opacity: 1;
visibility: visible;
transform: translateY(0);
}

/* Dropdown Links */
.dropdown-menu a {
display: block;
padding: 10px;
color: black;
text-decoration: none;
font-size: 16px;
transition: background 0.3s ease;
}

.dropdown-menu a:hover {
background:rgb(243, 10, 21);
color: white;
}


.tab{
 color: wheat;
width: 1550px;
margin-top: 0px;
}

.nav-link .active{
color: black;
}
.nav-link:hover{
color: black;
background: #1e1e2f;
}
.mt-4{
margin-top: 20px;
}
.nav-tabs{
    margin-top: 150px;
    margin-left: 130px;
    width: 1300px;
    color: wheat;
}
.nav-tabs .active{
color: red;
background-color: #1e1e2f;
}
     h1 {
            text-align: center;
        }

        h2 {
            margin-top: 20px;
        }


        /* Hide all tables by default */
        .table-container {
            display: none;
        }

        .active {
            display: block;
            color: black;
        }

        /* Style for buttons */
        .btn {
            padding: 20px 50px;
            background-color: #01017a;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background-color: #45a049;
        }

          .nav-tabs .active{
            --bs-nav-tabs-link-active-color: black;
          }
        
        .tab-table {
        
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .tab-table td {
            padding: 10px;
            font-size: 18px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: #f8f9fa;
            transition: background 0.3s;
        }
        .tab-table td.active {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }

        
.sidebar {
    margin-top: 0px;
    margin-left: 0px;
          width: 250px;
          height: 102vh;
          position: fixed;
          background: dark;
          background-color:rgb(5, 48, 95) ;
          padding-top: 20px;
      }
      .sidebar a {
          color: white;
          text-decoration: none;
          display: block;
          padding: 10px 20px;
      }
      .sidebar a:hover, a.active {
          background: #0056b3;
      }
      .logout {
          position: absolute;
          bottom: 30px;
          width: 100%;
      }
      .content {
          margin-left: 250px;
          padding: 20px;
          width: 100%;
      }
      .card-header{
        color: white;
      }

      table{
        margin-top: 10px;
        margin-left: 130px;
        width: 1500px;
      }
      .logo{
      width: 40px;  /* Adjust size as needed */
      height: auto;
      gap: 2px;
      margin-right: 20px; 
      }
      strong{
        color: gold;
        font-size: 24px;
      }
      .logout{
        background-color: red;
        border-radius: 14px;
      }
      a{

        display: inline;
        gap: 40px;
        font-size: 18px;
      } 
      .emp{
        margin-top: 50px;
      }
      .form-control{
        max-width: 300px;
      }

     .nav-link {
        color: wheat !important; /* Default color */
    }
    .nav-link.active {
        color: white !important; /* Highlight active tab */
        background-color: #555;
    }
    .tab-content .tab-pane {
        display: none;
    }
    .tab-content .tab-pane.active {
        display: block;
    }

    .ss{
        margin-left: 130px;
       max-width: 100%;
       
    }
    td{
        text-align: left;
    }
    .btn-success{
       font-weight: bolder;
        font-family: 'Poppins', sans-serif;
        font-size: 28px;
        margin-left: 20px;
        width: 250px;
        border-color: white !important;
        background:rgb(15, 122, 1) !important  ;
    }
    .btn-success:hover{

        background: #2a2a2a!important;
        color:rgb(255, 255, 255) !important;
    }
    option{
        color: black !important;
    }
    </style>
    <script>
  
    </script>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
    
</head>
<body style="
background: #353544; color:wheat;">
<div class="container mt-4">
    <h2 class="text-center"></h2>

   
<div class="sidebar">
  <a href="#" class="nav_logo">
                <img src="/images/Jz.png" alt="Jz Waters Logo" class="logo">
                <Strong class="Jz_Waters">Jz Waters</Strong>
            </a>
            <div class="dropdown-btn" onclick="toggleDropdown('productsDropdown')">
            <span><i class="uil uil-box"></i> Products</span>
            <i class="uil uil-angle-down"></i>
        </div>
        <div id="productsDropdown" class="dropdown">
            <a href="adminproducts.php">Manage Products</a>
            <a href="adminproductlogs.php">Product Logs</a>
        </div>
        <div class="dropdown-btn" onclick="toggleDropdown('employeesDropdown')">
            <span><i class="uil uil-users-alt "></i> Employees</span>
            <i class="uil uil-angle-down"></i>
        </div>
        <div id="employeesDropdown" class="dropdown">
            <a href="adminemployee.php">Manage Employees</a>
            <a href="employeelogs.php">Employee Logs</a>
        </div> 
       
        <a class= "uil uil-box log" href="orders.php">Orders</a>
        <a class= "uil uil-money-bill log" href="sales.php">Sales Tracking</a>
        
        <!-- Logout Button -->
        <a href="/php/logout.php" class="uil uil-signout logout" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>


    <div class="na" style="margin-top: 200px;"> 
         <nav class="navbar" style=" margin-left: -5px; margin-top: -180px; width:1550px; color:white; background:rgba(26, 21, 21, 0.27); border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-users-alt">Employee Daily Time Record</h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white;margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/php/logout.php"  class="uil uil-signout logout" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>
                </div>

<div class="emp">
    <!-- Employee Login Form -->
    <form action="dtr_process.php" method="POST">
        <div class="mb-3">
            <label for="personnel_id" class="form-label">Select Employee:</label>
            <select class="form-control" name="personnel_id" required>
                <option value="">-- Select Employee --</option>
                <?php while ($row = $employees->fetch_assoc()) { ?>
                    <option value="<?= $row['personnel_id'] ?>"><?= $row['name'] ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="action" value="login" class="btn btn-success">Log In</button>
    </form>

    </div>
    <!-- Table-Style Navigation -->
    <ul class="nav nav-tabs mt-4 tab" style="color: white;">
    <li class="nav-item">
        <a class="nav-link active" href="#" data-target="#workingEmployees">Currently Working Employees</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" data-target="#workHoursPayroll">Work Hours & Payroll</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" data-target="#totalPayroll">Total Payroll</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" data-target="#modificationLogs">Modification Logs</a>
    </li>
</ul>




    <!-- Currently Working Employees Section -->
    <div id="workingEmployees" class="content-section active">
    <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Position</th> 
                    <th>Login Time</th>
                    <th>Action</th> 
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $activeEmployees->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['position'] ?></td>
                        <td><?= $row['login_time'] ?></td>
                        <td>
                            <form action="dtr_process.php" method="POST">
                                <input type="hidden" name="personnel_id" value="<?= $row['personnel_id'] ?>">
                                <button type="submit" name="action" value="logout" class="btn btn-danger btn-sm">
                                    Log Out
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>



    <div id="modificationLogs" class="content-section">
    <table class="table table-dark table-striped ss">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Admin Name</th>
                <th>Modification Type</th>
                <th>New Salary</th>
                <th>New Position</th>
                <th>New Email</th>
                <th>New Contact</th>
                <th>Modified At</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Check if modification logs are available
            if ($logResults->num_rows > 0) {
                while ($row = $logResults->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                        <td><?= htmlspecialchars($row['admin_name']) ?></td>
                        <td><?= htmlspecialchars($row['modification_type']) ?></td>
                        <td>₱<?= number_format($row['new_salary'], 2) ?></td>
                       
                        <td><?= htmlspecialchars($row['new_position']) ?></td>
                       
                        <td><?= htmlspecialchars($row['new_email']) ?></td>
                       
                        <td><?= htmlspecialchars($row['new_contact']) ?></td>
                        <td><?= htmlspecialchars($row['modified_at']) ?></td>
                    </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='12' class='text-center'>No modification logs available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

   <!-- Work Hours and Payroll Section -->
<div id="workHoursPayroll" class="content-section">

    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Position</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Hours Worked</th>
                <th>Payroll</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Check if work hours payroll data is available
            if ($workHoursPayrollResult->num_rows > 0) {
                while ($row = $workHoursPayrollResult->fetch_assoc()) {
                    $hours = $row['hours_worked'];
                    $minutes = $row['minutes_worked'];
                    $seconds = $row['seconds_worked'];
                    
                    // Convert total worked time into hours, minutes, and seconds format
                    $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                    $workedHours = floor($totalSeconds / 3600);
                    $remainingMinutes = floor(($totalSeconds % 3600) / 60);
                    $remainingSeconds = $totalSeconds % 60;
                    
                    // Calculate payroll (assuming salary is an hourly rate, based on 160 hours in a month)
                    $payroll = ($hours + ($minutes / 60) + ($seconds / 3600)) * ($row['salary'] / 160);
            ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['formatted_login_time']) ?></td>
                        <td><?= htmlspecialchars($row['formatted_logout_time']) ?></td>
                        <td><?= sprintf("%02d:%02d:%02d", $workedHours, $remainingMinutes, $remainingSeconds) ?></td>
                        <td>₱<?= number_format($payroll, 2) ?></td>
                    </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No completed work sessions yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


    <!-- Total Payroll Section -->
    <div id="totalPayroll" class="content-section">
       
    <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Total Hours Worked</th>
                    <th>Total Payroll</th>
                </tr>
            </thead>
            <tbody>
                    <?php
                    if (count($employeeData) > 0) {
                        foreach ($employeeData as $name => $data) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($name) . "</td>";
                            echo "<td>" . $data['totalHours'] . " hours " . $data['totalMinutes'] . " minutes " . $data['totalSeconds'] . " seconds</td>";
                            echo "<td>₱" . number_format($data['totalPayroll'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No data for the last 15 days.</td></tr>";
                    }
                    ?>
                </tbody>

        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".nav-link");
    const tabPanes = document.querySelectorAll(".content-section");

    tabs.forEach(tab => {
        tab.addEventListener("click", function (e) {
            e.preventDefault();

            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");

            // Hide all content sections
            tabPanes.forEach(pane => pane.classList.remove("active"));

            // Show selected content section
            const target = this.getAttribute("data-target");
            document.querySelector(target).classList.add("active");
        });
    });
});

function switchTab(section) {
    const sectionElement = document.getElementById(section);
    const tabElement = document.getElementById('tab' + section);

    console.log(sectionElement, tabElement); // Debugging log

    if (sectionElement && tabElement) {
        document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-table td').forEach(el => el.classList.remove('active'));

        sectionElement.classList.add('active');
        tabElement.classList.add('active');
    } else {
        console.error("Section or Tab not found for:", section);
    }
}



  document.addEventListener("DOMContentLoaded", function () {
    const dropdownButtons = document.querySelectorAll(".dropdown-btn");

    dropdownButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const dropdown = this.nextElementSibling;
            const icon = this.querySelector(".uil-angle-down");

            // If dropdown is already active, close it
            if (dropdown.classList.contains("active")) {
                dropdown.style.maxHeight = "0px";
                setTimeout(() => dropdown.classList.remove("active"), 300);
            } else {
                // Close other dropdowns
                document.querySelectorAll(".dropdown").forEach((otherDropdown) => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.style.maxHeight = "0px";
                        setTimeout(() => otherDropdown.classList.remove("active"), 300);
                        otherDropdown.previousElementSibling
                            .querySelector(".uil-angle-down")
                            .classList.remove("rotate");
                    }
                });

                // Open this dropdown
                dropdown.classList.add("active");
                dropdown.style.maxHeight = dropdown.scrollHeight + "px"; 
            }

            // Rotate icon
            icon.classList.toggle("rotate");
        });
    });
});

</script>


    </body>
    </html>
