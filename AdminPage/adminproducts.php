
<?php

session_start();
include_once("/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php");

if (!isset($_SESSION['admin_id'])) {
  die("Error: Admin ID is missing.");
}
$admin_id = $_SESSION['admin_id'];



// Handle logout
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: getstarted.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />


  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
                               <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
  <style>
    body {
            background-color: #353544;
            color:white;
            font-family: 'Poppins', sans-serif;
        }
 
      th{
      font-size:18px;
      width: 200px;
    }
   
    .sidebar {
      margin-top: 0px;
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

        table{
          
          margin-top: 50px;
          margin-left: 300px;
          max-width: 82%;
        }

        table {
    width: 100%;
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

.user-container{
  background-color: rgba(26, 21, 21, 0.27) ;
}
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
 <!-- Bootstrap CSS -->
  
 <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style=" background: #353544; ">
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

  <!-- Product Management Section -->
  <div class="container-fluid mt-4">
  <nav class="navbar" style=" margin-left: 300px;  max-width: 82%; color:white; background:rgba(26, 21, 21, 0.27); border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-box">Products</h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white;margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/php/logout.php"  class="uil uil-signout logout" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>
    <!-- Responsive Table -->
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead class="text-center">
                <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productList">
                <?php
                $conn = new mysqli('localhost', 'root', '', 'wrsystem');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $sql = "SELECT * FROM product";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['product_id']}</td>
                                <td>{$row['name']}</td>
                                <td>₱" . number_format($row['price'], 2) . "</td>
                                <td>{$row['stock']}</td>
                                <td>{$row['description']}</td>
                                <td>" . ucfirst($row['product_type']) . "</td>
                                <td class='text-center'>
                                    <span class='badge bg-" . ($row['status'] == 'available' ? 'success' : 'danger') . "'>" . ucfirst($row['status']) . "</span>
                                </td>
                                <td>
                                    <div class='d-flex justify-content-center gap-2'>
                                        <button class='btn btn-primary btn-sm' onclick='addStock({$row['product_id']})'>Add Stock</button>
                                        <button class='btn btn-success btn-sm' onclick='updatePrice({$row['product_id']})'>Update Price</button>
                                    </div>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No products found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>
 

  <!-- Add Stock Modal -->
  <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addStockForm" onsubmit="addStockToProduct(event)">
            <div class="mb-3">
              <label for="addStockQuantity" class="form-label">Stock Quantity</label>
              <input type="number" class="form-control" id="addStockQuantity" required>
            </div>
            <input type="hidden" id="productIdForStock" />
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Add Stock</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Price Modal -->
<div class="modal fade" id="updatePriceModal" tabindex="-1" aria-labelledby="updatePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePriceModalLabel">Update Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatePriceForm">
                    <div class="mb-3">
                        <label for="newPrice" class="form-label">New Price</label>
                        <input type="number" class="form-control" id="newPrice" name="new_price" required>
                    </div>
                    <input type="hidden" id="productIdForPrice" name="product_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="updatePriceBtn" class="btn btn-success">Update Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS (make sure to load this after CSS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>

document.getElementById("userIcon").addEventListener("click", function () {
        let dropdown = document.getElementById("dropdownMenu");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        let dropdown = document.getElementById("dropdownMenu");
        let userIcon = document.getElementById("userIcon");
        if (!userIcon.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
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

    
    function confirmLogout() {
         var logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));
         logoutModal.show();
            }
    function addStock(productId) {
      document.getElementById("productIdForStock").value = productId;
      new bootstrap.Modal(document.getElementById("addStockModal")).show();
    }

    function addStockToProduct(event) {
      event.preventDefault();
      const productId = document.getElementById("productIdForStock").value;
      const quantity = document.getElementById("addStockQuantity").value;

      if (!productId || !quantity) {
        alert("Please fill in all fields.");
        return;
      }

      fetch('add_stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${quantity}`
})
.then(response => response.text())  // Get response as plain text
.then(data => {
    console.log("Raw Response:", data); // Debug: Log the response

    try {
        const jsonData = JSON.parse(data);  // Attempt to parse as JSON
        if (jsonData.success) {
            alert("Stock updated successfully!");
            location.reload();
        } else {
            alert("Error: " + jsonData.message);
        }
    } catch (e) {
        console.error("Invalid JSON response:", e);
        alert("Unexpected server response. Check console for details.");
    }
})
.catch(error => console.error("Error:", error));

    }

    function updatePrice(productId) {
      // Set the product ID to the hidden input and show the modal
      document.getElementById("productIdForPrice").value = productId;
      new bootstrap.Modal(document.getElementById("updatePriceModal")).show();
    }

    document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("updatePriceBtn").addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission
        
        const productId = document.getElementById("productIdForPrice").value;
        const newPrice = document.getElementById("newPrice").value;

        if (!productId || !newPrice || isNaN(newPrice) || newPrice <= 0) {
            alert("Please enter a valid price and select a product.");
            return;
        }

        console.log("Sending Data:", `product_id=${productId}&new_price=${newPrice}`);

        fetch('update_price.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `product_id=${productId}&new_price=${newPrice}`
        })
        .then(response => response.text())  // Get text first
        .then(text => {
            console.log("Raw Response:", text); 
            try {
                return JSON.parse(text);  // Try to parse manually
            } catch (error) {
                throw new Error("Invalid JSON response: " + text);
            }
        })
        .then(data => {
            console.log("Parsed JSON:", data);
            if (data.success) {
                alert("Price updated successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Fetch Error:", error));  // Only one catch here
    });
});

  </script>

</body>
</html>
