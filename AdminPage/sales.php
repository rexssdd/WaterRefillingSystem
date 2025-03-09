<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracking</title>
      
 <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      /* General Styles */
      body {
            background-color: #353544;
            color:white;
            font-family: 'Poppins', sans-serif;
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


/* Financial Summary Card */
.financial-summary {
    margin-top: 140px !important;
    margin-left: 400px !important;
    max-width: 65%;
    background: #353544;
    border-radius: 12px;
    color: #FFFFF0;
    box-shadow: 2px 4px 10px rgba(179, 178, 178, 0.93);
    transition: transform 0.2s ease-in-out;
}


/* Financial Summary Text */
.financial-summary h4 {
    font-weight: bold;
    color: wheat;
}

.financial-summary p {
    font-size: 16px;
    margin: 10px 0;
}

/* Status Styling */
#status {
    font-weight: bold;
    color: #17a2b8;
}

/* Chart Container */
.chart-container {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
}

/* Navigation Tabs */
.nav-pills {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    margin-left: -550px;
}

.nav-pills .nav-link {
    background-color: #e9ecef;
    color: #2c3e50;
    padding: 10px 15px;
    margin: 5px;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: #2c3e50;
    color: white;
}

.nav-pills .nav-link.active {
    background-color: #17a2b8;
    color: white;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .financial-summary {
        flex-direction: column;
        text-align: center;
    }

    .financial-summary .card-body {
        width: 100%;
    }

    .chart-container {
        width: 100%;
        overflow-x: auto;
    }
}
/* Center the Pie Chart */
#topSellingPieChart {
    max-width: 90%;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Pie Chart Container */
.chart-container {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Pie Chart Title */
.chart-container h5 {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
}

/* Add a Soft Glow Effect */
canvas {
    filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.2));
}
/* General Chart Styling */
.chart-container {
    background: #fff;
    margin-left: 400px !important;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 1220px;
    margin: auto;
    height: 50% !important;
}

/* Chart Titles */
.chart-container h5 {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
}

/* Chart Styling */
canvas {
    filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.2));
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
          
          margin-top: 130px;
          margin-left: 340px;
          max-width: 75%;
        }
        .logo{
        width: 40px;  /* Adjust size as needed */
        height: auto;
        gap: 2px;
        margin-right: 20px; 
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
        .pie-chart-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

#topSellingPieChart {
    width: 350px !important;
    height: 350px !important;
}

#pieChartLabels {
    list-style-type: none;
    padding: 0;
}

#pieChartLabels li {
    font-size: 16px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.pie-label-color {
    width: 12px;
    height: 12px;
    display: inline-block;
    margin-right: 8px;
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

  /* Navbar Styling */
  .navbar {
    position: absolute;
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
</head>
<body>
  

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


   

    <div class="container-fluid mt-4">
    <div class="s">
<nav class="navbar" style=" margin-left: 300px; margin-top: -110px;  max-width: 82%; color:white; background:rgba(26, 21, 21, 0.27); border-radius: 50px;">
                <i  style="margin-left: 100px;" class="uil uil-bars fs-3" id="menu-icon" ></i>
                <h2 id="page-title" class="uil uil-money-bill">Sales Tracking</h2>
                 <!-- User Icon with Dropdown -->
            <div class="user-container">
                <i class="uil uil-user fs-3 user-icon" id="userIcon" style=" color:white;margin-right: 100px;"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/php/logout.php"  class="uil uil-signout logout" onclick="confirmLogout()">Logout</a>
                </div>
            </div>
                    </nav>
                    </div>
    <div class="card mt-4 financial-summary d-flex flex-row align-items-center justify-content-between ">
        <div class="card-body w-50">
            <h4>Financial Summary</h4>
            <p>Total Revenue: <span id="total-revenue">0.00</span></p>
            <p>Total Costs: <span id="total-costs">0.00</span></p>
            <p>ROI: <span id="roi">0%</span></p>
            <p>Status: <span id="status">Neutral</span></p>
        </div>
        <div class="pie-chart-container">
    <canvas id="topSellingPieChart"></canvas>
    <ul id="pieChartLabels"></ul> <!-- List for labels -->
</div>

    </div>

    <ul class="nav nav-pills my-3">
        <li class="nav-item"><a class="nav-link " id="bar-nav" href="#" onclick="fetchSalesData('day')">Daily Sales</a></li>
        <li class="nav-item"><a class="nav-link" id="line-nav" href="#" onclick="fetchSalesData('week')">Weekly Sales</a></li>
        <li class="nav-item"><a class="nav-link" id="month-nav" href="#" onclick="fetchSalesData('month')">Monthly Sales</a></li>
        <li class="nav-item"><a class="nav-link" id="year-nav" href="#" onclick="fetchSalesData('year')">Yearly Sales</a></li>
    </ul>

    <div class="chart-container" id="bar-chart-container">
        <canvas id="salesBarChart"></canvas>
    </div>
    <div class="chart-container" id="line-chart-container" style="display: none;">
        <canvas id="salesLineChart"></canvas>
    </div>
</div>

<script>
let lineChart, barChart, pieChart;


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


document.addEventListener("DOMContentLoaded", function () {
    fetchSalesData('day'); // Default to daily sales
});


function fetchSalesData(period) {
    fetch(`get_sales_data.php?period=${period}`)
        .then(response => response.json())
        .then(data => {
            updateCharts(data, period);
            updateFinancialSummary(data);
        })
        .catch(error => console.error("Error fetching sales data:", error));
}

function updateCharts(data, period) {
    const lineCtx = document.getElementById("salesLineChart").getContext("2d");
    const barCtx = document.getElementById("salesBarChart").getContext("2d");
    const pieCtx = document.getElementById("topSellingPieChart").getContext("2d");

    const labels = data.labels || []; // Correct labels for bar and line chart
    const revenue = data.revenue || [];
    const dailySales = data.sales || [];
    const productNames = data.topSelling.map(p => p.product_name) || [];
    const topSellingData = data.topSelling.map(p => p.total_sold) || [];

    if (barChart) barChart.destroy();
    if (lineChart) lineChart.destroy();

    // 🟢 **Fix: Use `labels` for dates/times instead of product names**
    if (period === 'day') {
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: labels, // Correct: Use sales timestamps or dates
                datasets: [{
                    label: 'Daily Sales',
                    data: dailySales,
                    backgroundColor: 'green'
                }]
            }
        });
        document.getElementById("bar-chart-container").style.display = "block";
        document.getElementById("line-chart-container").style.display = "none";
    } else {
        lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels, // Correct labels for time-based data
                datasets: [{
                    label: 'Sales',
                    data: revenue,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
                    fill: true
                }]
            }
        });
        document.getElementById("bar-chart-container").style.display = "none";
        document.getElementById("line-chart-container").style.display = "block";
    }

    // 🟢 **Make Pie Chart Bigger & Add Labels to Side**
    if (pieChart) pieChart.destroy();
pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: productNames, // Labels won't be displayed on the chart itself
        datasets: [{
            data: topSellingData,
            backgroundColor: ['red', 'blue', 'yellow', 'green', 'purple']
        }]
    },
    options: {
        responsive: false, 
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // 🔴 This removes the labels from the top of the chart
            }
        }
    }
});

// Update Pie Chart Labels List
const labelList = document.getElementById("pieChartLabels");
labelList.innerHTML = "";
productNames.forEach((name, index) => {
    const listItem = document.createElement("li");
    listItem.innerHTML = `<span class="pie-label-color" style="background-color: ${['red', 'blue', 'yellow', 'green', 'purple'][index]};"></span> ${name}`;
    labelList.appendChild(listItem);
});

}

function updateFinancialSummary(data) {
    document.getElementById("total-revenue").innerText = `₱${data.total_revenue.toFixed(2)}`;
    document.getElementById("total-costs").innerText = `₱${data.total_costs.toFixed(2)}`;
    document.getElementById("roi").innerText = `${data.roi.toFixed(2)}%`;
    let statusElement = document.getElementById("status");
    statusElement.innerText = data.status;
    statusElement.style.color = data.status === "Profit" ? "green" : data.status === "Loss" ? "red" : "black";
}
</script>
</body>
</html>