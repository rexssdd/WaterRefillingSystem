<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wrsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee data
$sql = "SELECT e.personnel_id, e.name, e.position, e.salary, e.status, ed.email, ed.contact_number
        FROM employees e
        LEFT JOIN employee_data ed ON e.personnel_id = ed.employee_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
     <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                               <!-- Google Fonts -->
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style >
        body{
            background: #353544;
        }
         .ms-auto{
      margin-left: auto !important;
    }

 .mt-4{
       
        margin-top: 200px;
         margin-left: 350px;
       width:75%;
    }
   
    .btn-sm{
        width: 100px;
    }
    .card-header{
        color: white;
    }
    p{
        color: white;
    }

    .card-body{
        margin-left: 300px;
        width: 82%;
        background: #353544;
      
        box-shadow:rgb(113, 114, 117);
    }
    h4{
        color: white;
    }

    /* Sidebar Styling */
.sidebar {
    width: 250px;
    height: 100vh;
    background: #1e1e2f;
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
          margin-left:0px;
    width: 100px;
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
    width: calc(100% - 320px);
    height: 60px;
    background: #515151;
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


        .content {
            margin-left: 100px;
            padding: 20px;
            width: 100%;
        }
        .card-header{
          color: white;
        }

        .logo{
        width: 40px;  /* Adjust size as needed */
        height: auto;
        gap: 2px;
        margin-right: 20px; 
        }
        strong{
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
        .mb-3{
            margin-top: 40px;
        }
        .card-header{
            
            margin-top: 50px;
            margin-left: 45%;
        }
        strong{
            color: gold;
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
        position: absolute;
        top: 40px;
        right: 0;
        background: #333;
        border-radius: 8px;
        overflow: hidden;
        display: none;
        flex-direction: column;
        min-width: 150px;
    }
    .dropdown-menu a {
        color: white;
        padding: 10px;
        display: block;
        text-decoration: none;
        transition: background 0.3s ease;
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
    color: white;
    text-decoration: none;
    font-size: 16px;
    transition: background 0.3s ease;
}

.dropdown-menu a:hover {
    background:rgb(243, 10, 21);
    color: white;
}

    .show-dropdown {
        display: flex;
    }

.add{
    margin-top: 120px;
margin-left: 300px;
width: 200px;
border-radius: 10px;
border-color: white;
}
.add:hover{
    background: black;
}

.btn-warning{
    color: white;
    background: #284387;
    border-color: white;
}

.btn-danger{
    color: white;
    background: red;
    border-color: white;
}
.btn-warning:hover{
    background: red ;
    color: white;
}
.btn-danger:hover{
    background: #284387;
}


    </style>
</head>
<body>
    <!-- Navigation Bar -->
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

    <nav class="navbar" style=" margin-left: 300px; margin-top: 20px;  max-width: 82%; color:white; background: #515151; border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-users-alt">Employees</h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white; margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/php/logout.php"  class="uil uil-signout logout" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>

                 <!-- Add Employee Button -->
   <button class="btn btn-primary mb-3 add" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add Employee</button>

        <!-- Employee Table -->
            <div class="card-body">
                <table class=" table table-dark table-striped" id="employee-table">
                <thead>
    <tr>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Position</th>
        <th>Salary</th>
        <th>Email</th>
        <th>Contact Number</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody id="employee-list">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr data-id='" . $row["personnel_id"] . "'>";
            echo "<td>" . $row["personnel_id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["position"] . "</td>";
            echo "<td>₱" . number_format($row["salary"], 2) . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["contact_number"] . "</td>";
            echo "<td>";
            $status_class = ($row["status"] == 'Active') ? 'btn-success' : 'btn-danger';
            echo "<button class='btn " . $status_class . " btn-sm' onclick='toggleStatus(" . $row["personnel_id"] . ", \"" . $row["status"] . "\")'>" . ucfirst($row["status"]) . "</button>";
            echo "</td>";
            echo "<td style= 'display: flex; gap:14px; height: 50px; ' >";
            echo "<button class='btn btn-warning btn-sm' onclick='editEmployee(" . $row["personnel_id"] . ")'>Edit</button>";
            echo "<button class='btn btn-danger btn-sm' onclick='deleteEmployee(" . $row["personnel_id"] . ")'>Delete</button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No employees found</td></tr>";
    }
    ?>
</tbody>

                </table>
            </div>
            
        </div>
 

        <!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm">
                    <div class="mb-3">
                        <label for="employee-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="employee-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="employee-position" class="form-label">Position</label>
                        <select class="form-select" id="employee-position" required>
                            <option value="Manager">Manager</option>
                            <option value="Sales">Sales</option>
                            <option value="Technician">Technician</option>
                            <option value="Clerk">Clerk</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="employee-salary" class="form-label">Salary</label>
                        <input type="number" class="form-control" id="employee-salary" required>
                    </div>
                    <div class="mb-3">
                        <label for="employee-status" class="form-label">Status</label>
                        <select class="form-select" id="employee-status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <hr>
                    <button class="btn btn-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#accountRegistration">
                        Account Registration ▼
                    </button>
                    <div class="collapse mt-3" id="accountRegistration">
                        <div class="mb-3">
                            <label for="employee-username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="employee-username" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="employee-email" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee-contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="employee-contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="employee-password" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addEmployee()">Add Employee</button>
            </div>
        </div>
    </div>
</div>

   <!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <div class="mb-3">
                        <label for="edit-employee-id" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="edit-employee-id" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="edit-employee-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-employee-name">
                    </div>
                    <div class="mb-3">
                        <label for="edit-employee-position" class="form-label">Position</label>
                        <select class="form-select" id="edit-employee-position">
                            <option value="Manager">Manager</option>
                            <option value="Sales">Sales</option>
                            <option value="Technician">Technician</option>
                            <option value="Clerk">Clerk</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-employee-salary" class="form-label">Salary</label>
                        <input type="number" class="form-control" id="edit-employee-salary">
                    </div>
                    <div class="mb-3">
                        <label for="edit-employee-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-employee-email">
                    </div>
                    <div class="mb-3">
                        <label for="edit-employee-contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="edit-employee-contact">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveEmployeeChanges()">Save changes</button>
            </div>
        </div>
    </div>
</div>

    <script>

  // Toggle dropdown menu
  document.getElementById("userIcon").addEventListener("click", function () {
        document.getElementById("dropdownMenu").classList.toggle("show-dropdown");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        let dropdown = document.getElementById("dropdownMenu");
        let userIcon = document.getElementById("userIcon");

        if (!userIcon.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove("show-dropdown");
        }
    });
    
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

        // Logout function
        function logout() {
            alert('Logging out!');
            // Redirect to login page or handle logout functionality
        }

        // Toggle employee status function
        function toggleStatus(id, currentStatus) {
            const newStatus = (currentStatus === 'Active') ? 'Inactive' : 'Active';
            const statusButton = document.querySelector(`#employee-table tr[data-id="${id}"] td button`);

            // Update status button text and style
            statusButton.textContent = newStatus;
            statusButton.classList.toggle('btn-success');
            statusButton.classList.toggle('btn-danger');

            // Send the new status to the server using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_employee_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log(`Status updated for Employee ID ${id} to ${newStatus}`);
                    location.reload();
                } else {
                    alert("Error updating status. Please try again.");
                    // Revert button UI changes
                    statusButton.textContent = currentStatus;
                    statusButton.classList.toggle('btn-success');
                    statusButton.classList.toggle('btn-danger');
                    
                }
            };
            xhr.send("employee_id=" + id + "&status=" + newStatus);
        }

   // Edit employee function
   function editEmployee(id) {
        const employeeRow = document.querySelector(`#employee-table tr[data-id="${id}"]`);
        
        if (!employeeRow) {
            alert("Employee row not found.");
            return;
        }

        const name = employeeRow.children[1].textContent.trim();
        const position = employeeRow.children[2].textContent.trim();
        const salary = employeeRow.children[3].textContent.replace(/[^\d.]/g, '').trim();
        const email = employeeRow.children[4].textContent.trim();
        const contact = employeeRow.children[5].textContent.trim();

        // Populate modal fields with employee data
        document.getElementById('edit-employee-id').value = id;
        document.getElementById('edit-employee-name').value = name;
        document.getElementById('edit-employee-position').value = position;
        document.getElementById('edit-employee-salary').value = salary;
        document.getElementById('edit-employee-email').value = email;
        document.getElementById('edit-employee-contact').value = contact;

        // Show the modal
        new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
    }

    // Save employee changes function
    function saveEmployeeChanges() {
        const id = document.getElementById('edit-employee-id').value;
        const name = document.getElementById('edit-employee-name').value.trim();
        const position = document.getElementById('edit-employee-position').value;
        const salary = document.getElementById('edit-employee-salary').value.trim();
        const email = document.getElementById('edit-employee-email').value.trim();
        const contact = document.getElementById('edit-employee-contact').value.trim();

        // Validate input fields
        if (!id || !name || !position || !salary || !email || !contact) {
            alert("Please fill in all fields.");
            return;
        }

        // Validate email format
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert("Invalid email format.");
            return;
        }

        // Validate salary
        if (isNaN(salary) || parseFloat(salary) <= 0) {
            alert("Invalid salary amount.");
            return;
        }

        // Send data via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_employee.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                } catch (e) {
                    alert("Unexpected server response.");
                }
            } else {
                alert("Error updating employee. Please try again.");
            }
        };

        // Send updated data
        const params = `employee_id=${id}&name=${encodeURIComponent(name)}&position=${encodeURIComponent(position)}&salary=${salary}&email=${encodeURIComponent(email)}&contact_number=${encodeURIComponent(contact)}`;
        xhr.send(params);
    }


        // Delete employee function
        function deleteEmployee(id) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_employee.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Employee deleted successfully!');
                    document.querySelector(`#employee-table tr[data-id="${id}"]`).remove();
                } else {
                    alert("Error deleting employee. Please try again.");
                }
            };
            xhr.send("employee_id=" + id);
        }


        function addEmployee() {
            const name = document.getElementById("employee-name").value;
            const position = document.getElementById("employee-position").value;
            const salary = document.getElementById("employee-salary").value;
            const status = document.getElementById("employee-status").value;
            const username = document.getElementById("employee-username").value;
            const password = document.getElementById("employee-password").value;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "add_employee.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert("Employee added successfully!");
                    location.reload();
                } else {
                    alert("Error adding employee.");
                }
            };
            xhr.send(`name=${name}&position=${position}&salary=${salary}&status=${status}&username=${username}&password=${password}`);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
