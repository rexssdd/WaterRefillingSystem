<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   
   <style>
        body {
            background-color: #f4f7f6;
        }

        .navbar {
            border-bottom: 2px solid #0066cc;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .tab-content {
            padding-top: 20px;
        }

        .table th, .table td {
            text-align: center;
        }

        .form-container {
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .btn-primary, .btn-secondary {
            font-weight: bold;
        }

        .card-header {
            background-color: #0066cc;
            color: white;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .chart-label {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .financial-summary {
            margin-top: 20px;
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
                        <a class="nav-link" href="adminemployee.php">Employees</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delivery.html">Delivery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sales.html">Sales Tracking</a>
                    </li>
                    <li class="nav-item ms-auto">
                        <button class="btn btn-danger" onclick="logout()">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sales Tracking Section -->
    <div class="container mt-4">
        <h2 class="card-header">Sales and ROI Tracker</h2>

        <!-- Time Period Button Group -->
        <div class="btn-group mb-3" role="group" aria-label="Time Period Selector">
            <button type="button" class="btn btn-outline-primary" onclick="updateTimePeriod('day')">Day</button>
            <button type="button" class="btn btn-outline-primary" onclick="updateTimePeriod('week')">Week</button>
            <button type="button" class="btn btn-outline-primary" onclick="updateTimePeriod('month')">Month</button>
            <button type="button" class="btn btn-outline-primary" onclick="updateTimePeriod('year')">Year</button>
        </div>

        <!-- Financial Summary Section -->
        <div class="card mt-4 financial-summary">
            <div class="card-body">
                <h4>Financial Summary</h4>
                <p>Total Revenue: $<span id="total-revenue">0.00</span></p>
                <p>Total Costs: $<span id="total-costs">0.00</span></p>
                <p>ROI: <span id="roi">0%</span></p>
                <p>Status: <span id="status">Neutral</span></p>
            </div>
        </div>

        <!-- Order Records Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h4>Orders Over Time (Line Graph)</h4>
                <div class="chart-container">
                    <div id="line-chart-label" class="chart-label">Day</div>
                    <canvas id="ordersLineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sales Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h4>Weekly and Monthly Sales (Bar Graph)</h4>
                <div class="chart-container">
                    <div id="bar-chart-label" class="chart-label">Week</div>
                    <canvas id="salesBarChart"></canvas>
                </div>
            </div>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let orders = [
            { id: 1, date: '2025-01-01', revenue: 500, cost: 700 },
            { id: 2, date: '2025-01-02', revenue: 1000, cost: 400 },
            { id: 3, date: '2025-01-03', revenue: 200, cost: 300 },
            { id: 4, date: '2025-01-04', revenue: 500, cost: 600 },
            { id: 5, date: '2025-02-01', revenue: 1200, cost: 500 },
            { id: 6, date: '2025-02-05', revenue: 800, cost: 400 },
            { id: 7, date: '2025-03-01', revenue: 1500, cost: 700 },
            { id: 8, date: '2025-03-10', revenue: 1100, cost: 600 }
        ];
    

        let weeklySales = [2000, 1500, 2200, 1900, 2300, 2500, 1800];  // Example data
        let monthlySales = [7000, 8000, 12000, 9000];  // Example data
        let currentPeriod = 'day';
    
        let lineChart = null;
        let barChart = null;
    
        function updateTimePeriod(period) {
            currentPeriod = period;
            document.getElementById('line-chart-label').textContent = period.charAt(0).toUpperCase() + period.slice(1);
            document.getElementById('bar-chart-label').textContent = (period === 'week') ? 'Week' : (period === 'year') ? 'Year' : 'Month';
            generateLineChart();
            generateBarChart();
            updateFinancialSummary();
        }
    

        function generateLineChart() {
            const ctx = document.getElementById('ordersLineChart').getContext('2d');
            let labels, revenueData, costData;
    
            if (lineChart) lineChart.destroy();  // Destroy previous chart
    
            if (currentPeriod === 'day') {
                labels = orders.map(order => order.date);
                revenueData = orders.map(order => order.revenue);
                costData = orders.map(order => order.cost);
            } else if (currentPeriod === 'week') {
                labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
                revenueData = [1500, 1800, 2000, 2200, 2400];
                costData = [1000, 1200, 1400, 1600, 1800];
            } else if (currentPeriod === 'month') {
                labels = ['Jan', 'Feb', 'Mar', 'Apr'];
                revenueData = [7000, 8000, 12000, 9000];
                costData = [5000, 6000, 8000, 7000];
            } else if (currentPeriod === 'year') {
                labels = ['2022', '2023', '2024', '2025'];
                revenueData = [100000, 120000, 130000, 150000];
                costData = [80000, 90000, 95000, 110000];
            }
        
            const data = {
                labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false
                    },
                    {
                        label: 'Cost',
                        data: costData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: false
                    }
                ]
            };
    
            lineChart = new Chart(ctx, {
                type: 'line',
                data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }
    
        function generateBarChart() {
    const ctx = document.getElementById('salesBarChart').getContext('2d');
    let data;

    if (barChart) barChart.destroy();  // Destroy previous chart

    if (currentPeriod === 'day') {
        data = {
            labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],  // Example daily data
            datasets: [
                {
                    label: 'Daily Sales ($)',
                    data: [500, 1000, 200, 500, 1200],  // Example daily sales data
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        };
    } else if (currentPeriod === 'week') {
        data = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
            datasets: [
                {
                    label: 'Weekly Sales ($)',
                    data: weeklySales,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        };
    } else if (currentPeriod === 'month') {
        data = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr'],
            datasets: [
                {
                    label: 'Monthly Sales ($)',
                    data: monthlySales,
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }
            ]
        };
    } else if (currentPeriod === 'year') {
        data = {
            labels: ['2022', '2023', '2024', '2025'],
            datasets: [
                {
                    label: 'Annual Sales ($)',
                    data: [100000, 120000, 130000, 150000],
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }
            ]
        };
    }

    barChart = new Chart(ctx, {
        type: 'bar',
        data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

    
        // Financial Summary Function (Placeholder for updating summary)
        function updateFinancialSummary() {
            let totalRevenue = 0;
            let totalCosts = 0;
            
            if (currentPeriod === 'day') {
                totalRevenue = orders.reduce((sum, order) => sum + order.revenue, 0);
                totalCosts = orders.reduce((sum, order) => sum + order.cost, 0);
            } else if (currentPeriod === 'week') {
                totalRevenue = 1500 + 1800 + 2000 + 2200 + 2400;
                totalCosts = 1000 + 1200 + 1400 + 1600 + 1800;
            } else if (currentPeriod === 'month') {
                totalRevenue = 7000 + 8000 + 12000 + 9000;
                totalCosts = 5000 + 6000 + 8000 + 7000;
            } else if (currentPeriod === 'year') {
                totalRevenue = 100000 + 120000 + 130000 + 150000;
                totalCosts = 80000 + 90000 + 95000 + 110000;
            }
    
            const roi = (totalRevenue - totalCosts) / totalCosts * 100;
            const status = roi > 10 ? "Profit" : (roi < -10 ? "Loss" : "Neutral");
    
            document.getElementById('total-revenue').textContent = totalRevenue.toFixed(2);
            document.getElementById('total-costs').textContent = totalCosts.toFixed(2);
            document.getElementById('roi').textContent = roi.toFixed(2) + "%";
            document.getElementById('status').textContent = status;
        }
    
        // Initial render
        updateTimePeriod('day');
    </script>
    
</body>
</html>
