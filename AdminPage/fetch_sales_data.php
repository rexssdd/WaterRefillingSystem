let currentPeriod = 'day';
let lineChart = null;
let barChart = null;
let pieChart = null;

window.onload = function () {
    updateTimePeriod('day'); // Fetch initial data
    showGraph('line'); // Default display
};

// Update the selected time period and refresh charts
function updateTimePeriod(period) {
    currentPeriod = period;
    document.getElementById('line-chart-label').textContent = capitalizeFirst(period);
    document.getElementById('bar-chart-label').textContent = period === 'week' ? 'Week (Per Day)' : period === 'year' ? 'Year' : 'Month';

    fetchSalesData().then(salesData => {
        generateLineChart(salesData);
        generateBarChart(salesData);
        generatePieChart(salesData);
        updateFinancialSummary(salesData);
    });
}

// Fetch sales data from the database
function fetchSalesData() {
    return fetch(`get_sales_data.php?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            console.log("Fetched Data:", data); 

            if (!data || !Array.isArray(data.labels)) {
                console.error("Invalid data format:", data);
                return { labels: ["No Data"], revenue: [0], cost: [0], sales: [0], topSelling: {} };
            }

            return {
                labels: data.labels.length ? data.labels : ["No Data"],
                revenue: data.revenue.length ? data.revenue : [0],
                cost: data.cost.length ? data.cost : [0],
                sales: data.sales.length ? data.sales : [0],
                topSelling: Object.keys(data.topSelling).length ? data.topSelling : { "No Data": 1 }
            };
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            return { labels: ["No Data"], revenue: [0], cost: [0], sales: [0], topSelling: { "No Data": 1 } };
        });
}

// Display error message without removing canvas
function displayNoDataMessage(containerId, message = "No data available") {
    let container = document.getElementById(containerId);
    let messageElement = container.querySelector(".no-data-message");

    if (!messageElement) {
        messageElement = document.createElement("p");
        messageElement.className = "no-data-message";
        messageElement.style.textAlign = "center";
        messageElement.style.color = "red";
        container.appendChild(messageElement);
    }

    messageElement.textContent = message;
}

// Generate line chart with error handling
function generateLineChart(salesData) {
    try {
        const ctx = document.getElementById('ordersLineChart').getContext('2d');

        if (lineChart) lineChart.destroy();

        if (!salesData.labels || salesData.labels.length === 0) {
            displayNoDataMessage('line-chart-container');
            return;
        }

        lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: salesData.revenue,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Cost',
                        data: salesData.cost,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true
                    }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    } catch (error) {
        console.error("Error generating line chart:", error);
        displayNoDataMessage('line-chart-container', "Error loading line chart.");
    }
}

// Generate bar chart with error handling
function generateBarChart(salesData) {
    try {
        const ctx = document.getElementById('salesBarChart').getContext('2d');

        if (barChart) barChart.destroy();

        if (!salesData.sales || salesData.sales.length === 0) {
            displayNoDataMessage('bar-chart-container');
            return;
        }

        barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Sales',
                    data: salesData.sales,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    } catch (error) {
        console.error("Error generating bar chart:", error);
        displayNoDataMessage('bar-chart-container', "Error loading bar chart.");
    }
}


function generatePieChart(salesData) {
    try {
        console.log("Received sales data:", salesData);

        // Ensure the canvas element exists
        const canvas = document.getElementById('topSellingPieChart');
        if (!canvas) {
            console.error("Error: Canvas element 'topSellingPieChart' not found.");
            return;
        }

        const ctx = canvas.getContext('2d');

        // Destroy previous chart instance if it exists
        if (window.pieChart) {
            window.pieChart.destroy();
            console.log("Previous pie chart destroyed.");
        }

        // Validate `topSelling` data
        if (!salesData.topSelling || Object.keys(salesData.topSelling).length === 0) {
            console.warn("No top-selling data available.");
            displayNoDataMessage('topSellingPieChart', "No data available.");
            return;
        }

        // Extract labels and values
        const productNames = Object.keys(salesData.topSelling);
        const productQuantities = Object.values(salesData.topSelling);

        console.log("Pie Chart Labels:", productNames);
        console.log("Pie Chart Data:", productQuantities);

        // Ensure chart data is valid
        if (productQuantities.every(val => val === 0)) {
            console.warn("All product quantities are zero. No chart will be displayed.");
            displayNoDataMessage('topSellingPieChart', "No sales data available.");
            return;
        }

        // Create a new chart instance
        window.pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: productNames,
                datasets: [{
                    data: productQuantities,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF9F40', '#4BC0C0']
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false 
            }
        });

    } catch (error) {
        console.error("Error generating pie chart:", error);
        displayNoDataMessage('topSellingPieChart', "Error loading pie chart.");
    }
}

// Helper function to show error or no data message
function displayNoDataMessage(canvasId, message) {
    const canvas = document.getElementById(canvasId);
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = "16px Arial";
        ctx.fillStyle = "red";
        ctx.textAlign = "center";
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }
}

// Update financial summary with error handling
function updateFinancialSummary(salesData) {
    try {
        if (!salesData.revenue.length || !salesData.cost.length) {
            displayNoDataMessage('financial-summary');
            return;
        }

        const totalRevenue = salesData.revenue.reduce((acc, curr) => acc + curr, 0);
        const totalCosts = salesData.cost.reduce((acc, curr) => acc + curr, 0);
        const roi = totalCosts > 0 ? ((totalRevenue - totalCosts) / totalCosts) * 100 : 0;

        document.getElementById('total-revenue').textContent = totalRevenue.toFixed(2);
        document.getElementById('total-costs').textContent = totalCosts.toFixed(2);
        document.getElementById('roi').textContent = roi.toFixed(2) + '%';
        document.getElementById('status').textContent = roi >= 0 ? 'Profitable' : 'Loss';
    } catch (error) {
        console.error("Error updating financial summary:", error);
        displayNoDataMessage('financial-summary', "Error loading financial summary.");
    }
}

// Capitalize first letter of a string
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

window.showGraph = function (type) {
    ['line', 'bar'].forEach(chart => {
        let chartContainer = document.getElementById(`${chart}-chart-container`);
        let navItem = document.getElementById(`${chart}-nav`);

        if (chartContainer) {
            chartContainer.style.display = chart === type ? 'block' : 'none';
        }

        if (navItem) {
            navItem.classList.toggle('active', chart === type);
        }
    });
};


// Navigation event listeners
// 
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('line-nav').addEventListener('click', () => showGraph('line'));
    document.getElementById('bar-nav').addEventListener('click', () => showGraph('bar'));
   });

document.addEventListener("DOMContentLoaded", function () {
    window.showGraph('line'); // Set default graph if needed
});
