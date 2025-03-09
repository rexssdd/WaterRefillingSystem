<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
          body{

      background: #353544;
    }
        .card-header { background-color: #0066cc; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block;;
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
    width: 1550px;
    margin-top: 0px;
}

.nav-link{
    color: white;
}
.nav-link:hover{
    color: white;
    background: #1e1e2f;
}
.mt-4{
    margin-top: 20px;
}
.nav-tabs .active{
    color: red;
    background-color: #1e1e2f;
}
.btn-success{
  width: 100px;
  color: white;
  background:  #284387 ;
  border-color: wheat;
}
.btn-success:hover{
    color: white;
    background: darkblue;
    border-color: wheat;
}
.btn-danger{
    background: #A52A2A;
    border-color: wheat;
    margin-left: 10px;
    width: 100px;
}
.btn-danger:hover{
    color: white;
    background: darkred;
    border-color: wheat;
}
    </style>
     <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
 
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

  <!-- Product Management Section -->
  <div class="container-fluid mt-4">
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


    <div class="container " style="margin-top: 200px;">

        <ul class="nav nav-tabs mt-4 tab" style="margin-top: 20px !important;">
            <li class="nav-item"><a class="nav-link active" href="#" data-target="#pending">Pending Orders</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-target="#preparing">Preparing Orders</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-target="#delivery">Delivery Orders</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-target="#completed">Completed & Cancelled Orders</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-target="#declined-orders-section">Decline Orders</a></li>
        </ul>
        
        <div id="pending" class="tab-content active">
            
        <table class="table table-dark table-striped" style="width: 120%;">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Amount</th><th>Payment Method</th><th>Payment Status</th><th>Action</th></tr>
                        </thead>
                        <tbody id="pending-orders"></tbody>
                    </table>
            
        </div>
        
        <div id="preparing" class="tab-content">
                <table class="table table-dark table-striped" style="width: 120%;">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment Method</th><th>Payment Status</th><th>Action</th></tr>
                        </thead>
                        <tbody id="preparing-orders"></tbody>
                    </table>
                </div>
            
        
        <div id="delivery" class="tab-content">
           
                <table class="table table-dark table-striped" style="width: 120%;">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Amount</th><th>Payment Method</th><th>Payment Status</th><th>Action</th></tr>
                        </thead>
                        <tbody id="delivery-orders"></tbody>
                    </table>
                </div>
           
        
        <div id="completed" class="tab-content">
        
                <table class="table table-dark table-striped" style="width: 120%;">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Amount</th><th>Payment Method</th><th>Payment Status</th><th>Action</th></tr>
                        </thead>
                        <tbody id="completed-cancelled-orders"></tbody>
                    </table>
                </div>
          

    <div id="declined-orders-section" class="tab-content">

        <table class="table table-dark table-striped" style="width: 120%;">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody id="declined-orders"></tbody>
            </table>
        </div>



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

    

$(document).ready(function() {
            $('.nav-link').click(function() {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $($(this).data('target')).addClass('active');
            });
        });
        async function fetchOrders() {
    try {
        const response = await fetch('fetch_orders.php');
        const orders = await response.json();

        const pendingOrders = document.getElementById('pending-orders');
        const preparingOrders = document.getElementById('preparing-orders');
        const deliveryOrders = document.getElementById('delivery-orders');
        const completedCancelled = document.getElementById('completed-cancelled-orders');
        const declinedOrders = document.getElementById('declined-orders'); // Declined orders section

        // Clear previous data
        pendingOrders.innerHTML = '';
        preparingOrders.innerHTML = '';
        deliveryOrders.innerHTML = '';
        completedCancelled.innerHTML = '';
        declinedOrders.innerHTML = '';

        orders.forEach(order => {
            const row = `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.customer_name || 'Guest'}</td>
                    <td>${order.status}</td>
                    <td>${order.amount || '0.00'}</td>
                    <td>${order.payment_method || 'N/A'}</td>
                    <td>${order.payment_status || 'unpaid'}</td>
                    ${
                        order.status === 'pending'
                            ? `<td>
                                <button class="btn btn-success btn-sm" onclick="acceptOrder(${order.order_id})">Accept</button>
                                <button class="btn btn-danger btn-sm" onclick="declineOrder(${order.order_id})">Decline</button>
                            </td>`
                            : '<td>-</td>'
                    }
                </tr>
            `;

            if (order.status === 'pending') {
                pendingOrders.innerHTML += row;
            } else if (order.status === 'preparing') {
                preparingOrders.innerHTML += row;
            } else if (order.status === 'to deliver') {
                deliveryOrders.innerHTML += row;
            } else if (['completed', 'cancelled', 'failed'].includes(order.status)) {
                completedCancelled.innerHTML += row;
            } else if (order.status === 'declined') {  
                declinedOrders.innerHTML += row;
            }
        });
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
}


document.addEventListener("DOMContentLoaded", fetchOrders);

async function fetchOrders() {
    try {
        const response = await fetch('fetch_orders.php');
        const orders = await response.json();

        const pendingOrders = document.getElementById('pending-orders');
        const preparingOrders = document.getElementById('preparing-orders');
        const deliveryOrders = document.getElementById('delivery-orders');
        const completedCancelled = document.getElementById('completed-cancelled-orders');
        const declinedOrders = document.getElementById('declined-orders'); // Added Declined Orders Section

        // Clear previous data
        pendingOrders.innerHTML = '';
        preparingOrders.innerHTML = '';
        deliveryOrders.innerHTML = '';
        completedCancelled.innerHTML = '';
        declinedOrders.innerHTML = '';

        orders.forEach(order => {
            const row = `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.customer_name || 'Guest'}</td>
                    <td>${order.status}</td>
                    <td>${order.amount || '0.00'}</td>
                    <td>${order.payment_method || 'N/A'}</td>
                    <td>${order.payment_status || 'unpaid'}</td>
                    ${
                        order.status === 'pending'
                            ? `<td>
                                <button class="btn btn-success btn-sm" onclick="acceptOrder(${order.order_id})">Accept</button>
                                <button class="btn btn-danger btn-sm" onclick="declineOrder(${order.order_id})">Decline</button>
                            </td>`
                            : '<td>-</td>'
                    }               
                </tr>
            `;

            if (order.status === 'pending') {
                pendingOrders.innerHTML += row;
            } else if (order.status === 'preparing') {
                preparingOrders.innerHTML += row;
            } else if (order.status === 'to deliver') {
                deliveryOrders.innerHTML += row;
            } else if (['completed', 'cancelled', 'failed'].includes(order.status)) {
                completedCancelled.innerHTML += row;
            } else if (order.status === 'declined') {  // Handling Declined Orders
                declinedOrders.innerHTML += row;
            }
        });
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
}


        document.addEventListener("DOMContentLoaded", fetchOrders);
       
        async function acceptOrder(orderId) {
    try {
        const response = await fetch('accept_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        });

        const result = await response.json();

        if (result.success) {
            alert("Order Accepted!");
            fetchOrders(); // Refresh order list
        } else {
            alert("Error: " + result.error);
        }
    } catch (error) {
        console.error("Error accepting order:", error);
    }
}
async function declineOrder(orderId) {
    if (!confirm("Are you sure you want to decline this order?")) {
        return;
    }

    try {
        const response = await fetch('decline_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        });

        const result = await response.json();

        if (result.success) {
            alert("Order declined successfully.");
            location.reload(); // Refresh to update the order status
        } else {
            alert("Failed to decline order: " + result.message);
        }
    } catch (error) {
        console.error("Error declining order:", error);
        alert("An error occurred while declining the order.");
    }
}


</script>

</body>
</html>
