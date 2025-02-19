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
$sql = "SELECT employee_id, name, position, salary, status FROM employees";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adminproducts.html">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="adminemployee.html">Employees</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="deliveryteam.html">Delivery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.html">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.html">Sales Tracking</a>
                    </li>
                    <li class="nav-item ms-auto">
                        <button class="btn btn-danger" onclick="logout()">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Employee Management Section -->
    <div class="container mt-4">
        <h2 class="card-header">Employee Management</h2>
        <p>Manage employees here.</p>

        <!-- Employee Table -->
        <div class="card mt-4">
            <div class="card-body">
                <h4>Employee List</h4>
                <table class="table table-striped" id="employee-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employee-list">
                        <!-- Dynamically load employee data -->
                        <?php
                        if ($result->num_rows > 0) {
                            // Output each employee
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . $row["employee_id"] . "'>";
                                echo "<td>" . $row["employee_id"] . "</td>";
                                echo "<td>" . $row["name"] . "</td>";
                                echo "<td>" . $row["position"] . "</td>";
                                echo "<td>₱" . number_format($row["salary"], 2) . "</td>";
                                echo "<td>";
                                // Toggle button for status
                                $status_class = ($row["status"] == 'Active') ? 'btn-success' : 'btn-danger';
                                $status_text = ucfirst($row["status"]);
                                echo "<button class='btn " . $status_class . " btn-sm' onclick='toggleStatus(" . $row["employee_id"] . ", \"" . $row["status"] . "\")'>" . $status_text . "</button>";
                                echo "</td>";
                                echo "<td>";
                                echo "<button class='btn btn-warning btn-sm' onclick='editEmployee(" . $row["employee_id"] . ")'>Edit</button>";
                                echo "<button class='btn btn-danger btn-sm' onclick='deleteEmployee(" . $row["employee_id"] . ")'>Delete</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No employees found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
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
                        <label for="employee-id" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employee-id" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="employee-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="employee-name">
                    </div>
                    <div class="mb-3">
                        <label for="employee-position" class="form-label">Position</label>
                        <select class="form-select" id="employee-position">
                            <option value="Manager">Manager</option>
                            <option value="Sales">Sales</option>
                            <option value="Technician">Technician</option>
                            <option value="Clerk">Clerk</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="employee-salary" class="form-label">Salary</label>
                        <input type="number" class="form-control" id="employee-salary">
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
    const name = employeeRow.children[1].textContent;
    const position = employeeRow.children[2].textContent;
    const salary = employeeRow.children[3].textContent.replace('₱', '');

    // Populate the modal with employee data (no status)
    document.getElementById('employee-id').value = id;
    document.getElementById('employee-name').value = name;
    document.getElementById('employee-position').value = position;
    document.getElementById('employee-salary').value = salary;

    // Show the modal
    new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
}

       // Save changes made to the employee in the modal
function saveEmployeeChanges() {
    const id = document.getElementById('employee-id').value;
    const name = document.getElementById('employee-name').value;
    const position = document.getElementById('employee-position').value;
    const salary = document.getElementById('employee-salary').value;
    
    // Since we removed the status field, do not reference it
    // const status = document.getElementById('employee-status').value;  // Remove this line

    // Send the updated data to the server using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_employee.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Employee updated successfully!');
            // Reload the page or update the table dynamically with the new data
            window.location.reload();
        } else {
            alert("Error updating employee. Please try again.");
        }
    };
    // Send the updated data without the status
    xhr.send(`employee_id=${id}&name=${name}&position=${position}&salary=${salary}`);
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
