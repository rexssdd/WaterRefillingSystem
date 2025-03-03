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
$sql = "SELECT e.employee_id, e.name, e.position, e.salary, e.status, ed.email, ed.contact_number
        FROM employees e
        LEFT JOIN employee_data ed ON e.employee_id = ed.employee_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style >
        body{
            background: black;
        }
         .ms-auto{
      margin-left: auto !important;
    }

    td{
        max-width: 350px;
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
        background: black;
        border-color: black;
        box-shadow:rgb(140, 141, 143);
    }
    h4{
        color: white;
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Water Refilling System Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="adminproducts.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="adminemployee.php">Employees</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delivery.php">Delivery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.html">Sales Tracking</a>
                    </li>
                    <li class="nav-item ms-auto">
                        <button class="btn btn-danger" style="  margin-left: 1100px;" onclick="logout()">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Employee Management Section -->
    <div class="container mt-4">
        <h2 class="card-header">Employee Management</h2>
        <p>Manage employees here.</p>
   <!-- Add Employee Button -->
   <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add Employee</button>

        <!-- Employee Table -->
        <div class="card mt-4">
            <div class="card-body">
                <h4>Employee List</h4>
                <table class="table table-dark table-striped" id="employee-table">
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
            echo "<tr data-id='" . $row["employee_id"] . "'>";
            echo "<td>" . $row["employee_id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["position"] . "</td>";
            echo "<td>₱" . number_format($row["salary"], 2) . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["contact_number"] . "</td>";
            echo "<td>";
            $status_class = ($row["status"] == 'Active') ? 'btn-success' : 'btn-danger';
            echo "<button class='btn " . $status_class . " btn-sm' onclick='toggleStatus(" . $row["employee_id"] . ", \"" . $row["status"] . "\")'>" . ucfirst($row["status"]) . "</button>";
            echo "</td>";
            echo "<td style= 'display: flex; gap:14px; height: 50px; ' >";
            echo "<button class='btn btn-warning btn-sm' onclick='editEmployee(" . $row["employee_id"] . ")'>Edit</button>";
            echo "<button class='btn btn-danger btn-sm' onclick='deleteEmployee(" . $row["employee_id"] . ")'>Delete</button>";
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
    </div>

        <!-- Add Employee Modal -->
        <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
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
                        <h5>Account Registration</h5>
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
