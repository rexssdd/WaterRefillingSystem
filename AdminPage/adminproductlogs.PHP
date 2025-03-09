<?php
// Include database connection
include('/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php');

// Fetch logs from both `inventory_price_logs` and `inventory_stock_logs` tables
$sql_price_logs = "SELECT * FROM inventory_price_logs";
$result_price_logs = mysqli_query($conn, $sql_price_logs);

$sql_stock_logs = "SELECT * FROM inventory_stock_logs";
$result_stock_logs = mysqli_query($conn, $sql_stock_logs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Logs</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body{

background: #353544;
}
  .card-header { background-color: #0066cc; color: white; }
  .tab-content { display: none; }
  .tab-content.active { display: block;;
  }

.nav{
    color: wheat;
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
    margin-top: 50px;
    margin-left: 300px;
    max-width: 82%;
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
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        table{
            
            max-width: 82%;
            margin-left: 300px;
        }
          .nav-tabs .active{
            --bs-nav-tabs-link-active-color: black;
          }
        td{
            text-align: left;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
         <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
 
</head>
<body style="
background: #353544; color:wheat;">


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
        <a class= "uil uil-parcel log" href="delivery.php">Delivery</a>
        <a class= "uil uil-box log" href="orders.php">Orders</a>
        <a class= "uil uil-money-bill log" href="sales.php">Sales Tracking</a>
        
        <!-- Logout Button -->
        <a href="/php/logout.php" class="uil uil-signout logout" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>


    <div class="na" style="margin-top: 200px;"> 
         <nav class="navbar" style=" margin-left: 300px; margin-top: -180px;  max-width: 82%; color:white; background:rgba(26, 21, 21, 0.27); border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-box">Managing Orders</h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white;margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/php/logout.php"  class="uil uil-signout logout" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>
                </div>


    <div class="container-fluid mt-4" style="color:white; margin-top: 30px;">
    <ul class="nav nav-tabs mt-4 tab" style="color: white;">
        <li class="nav-item">
            <a class="nav-link active"   class="s" href="#" data-target="#price-logs">Price Logs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" class="s" href="#" data-target="#stock-logs">Stock Logs</a>
        </li>
    </ul>

    <!-- Price Logs Table -->
    <div id="price-logs" class="tab-content active">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Admin ID</th>
                    <th>Product ID</th>
                    <th>Old Price</th>
                    <th>New Price</th>
                    <th>Change Date</th>
                    <th>Log Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_price_logs)): ?>
                <tr>
                    <td><?php echo $row['log_id']; ?></td>
                    <td><?php echo $row['admin_id']; ?></td>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo $row['old_price']; ?></td>
                    <td><?php echo $row['new_price']; ?></td>
                    <td><?php echo $row['change_date']; ?></td>
                    <td><?php echo $row['log_time']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Stock Logs Table -->
    <div id="stock-logs" class="tab-content">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Product ID</th>
                    <th>Previous Stock</th>
                    <th>New Stock</th>
                    <th>Adjustment Type</th>
                    <th>Reason</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_stock_logs)): ?>
                <tr>
                    <td><?php echo $row['log_id']; ?></td>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo $row['previous_stock']; ?></td>
                    <td><?php echo $row['new_stock']; ?></td>
                    <td><?php echo $row['adjustment_type']; ?></td>
                    <td><?php echo $row['reason']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
    <script>


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


    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".nav-link").forEach(link => {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                document.querySelectorAll(".nav-link").forEach(el => el.classList.remove("active"));
                this.classList.add("active");

                document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
                document.querySelector(this.getAttribute("data-target")).classList.add("active");
            });
        });
    });
        // Function to show the respective table when the button is clicked
        function showTable(tableType) {
            // Hide all tables
            document.getElementById('price-logs').classList.remove('active');
            document.getElementById('stock-logs').classList.remove('active');

            // Show the selected table
            if (tableType === 'price') {
                document.getElementById('price-logs').classList.add('active');
            } else if (tableType === 'stock') {
                document.getElementById('stock-logs').classList.add('active');
            }
        }

        // Show Price Logs by default
        showTable('price');
    </script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
