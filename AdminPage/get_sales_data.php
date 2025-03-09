<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Include database connection
require_once '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Check if connection exists
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get and validate the selected period
$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$validPeriods = ['day', 'week', 'month', 'year'];

if (!in_array($period, $validPeriods)) {
    echo json_encode(['error' => 'Invalid period value']);
    exit();
}

// Initialize response data
$data = [
    'labels' => [],
    'revenue' => [],
    'cost' => [],
    'sales' => [],
    'topSelling' => [],
];

// Prepare the query based on the selected period
switch ($period) {
    case 'day':
        $query = "SELECT DATE(s.sale_date) AS label, 
                         SUM(s.sale_amount) AS revenue, 
                         SUM(p.cost * s.quantity) AS cost
                  FROM sales s
                  JOIN product p ON s.product_id = p.product_id
                  WHERE s.sale_date >= CURDATE()
                  GROUP BY DATE(s.sale_date)";
        break;
    case 'week':
        $query = "SELECT YEARWEEK(s.sale_date, 1) AS label, 
                         SUM(s.sale_amount) AS revenue, 
                         SUM(p.cost * s.quantity) AS cost
                  FROM sales s
                  JOIN product p ON s.product_id = p.product_id
                  WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                  GROUP BY YEARWEEK(s.sale_date, 1)
                  ORDER BY YEARWEEK(s.sale_date, 1)";
        break;
        case 'month':
            $query = "SELECT MONTH(s.sale_date) AS label, 
                             SUM(s.sale_amount) AS revenue, 
                             SUM(p.cost * s.quantity) AS cost
                      FROM sales s
                      JOIN product p ON s.product_id = p.product_id
                      WHERE YEAR(s.sale_date) = YEAR(CURDATE())
                      GROUP BY MONTH(s.sale_date)
                      ORDER BY MONTH(s.sale_date)";
        
            $result = mysqli_query($conn, $query);
        
            // Initialize labels and revenue for all 12 months
            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            $revenueData = array_fill(0, 12, 0);
            $costData = array_fill(0, 12, 0);
            $salesData = array_fill(0, 12, 0);
        
            while ($row = mysqli_fetch_assoc($result)) {
                $monthIndex = (int)$row['label'] - 1;
                $revenueData[$monthIndex] = (float)$row['revenue'];
                $costData[$monthIndex] = (float)$row['cost'];
                $salesData[$monthIndex] = $revenueData[$monthIndex] - $costData[$monthIndex];
            }
        
            $data['labels'] = $months;
            $data['revenue'] = $revenueData;
            $data['cost'] = $costData;
            $data['sales'] = $salesData;
            break;
        
    case 'year':
        $query = "SELECT YEAR(s.sale_date) AS label, 
                         SUM(s.sale_amount) AS revenue, 
                         SUM(p.cost * s.quantity) AS cost
                  FROM sales s
                  JOIN product p ON s.product_id = p.product_id
                  GROUP BY YEAR(s.sale_date)
                  ORDER BY YEAR(s.sale_date)";
        break;
}

// Execute the query
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    exit();
}

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
    if ($period === 'month') {
        $data['revenue'][(int)$row['label'] - 1] = (float)$row['revenue'];
        $data['cost'][(int)$row['label'] - 1] = (float)$row['cost'];
        $data['sales'][(int)$row['label'] - 1] = (float)$row['revenue'] - (float)$row['cost'];
    } else {
        $data['labels'][] = $row['label'];
        $data['revenue'][] = (float)$row['revenue'];
        $data['cost'][] = (float)$row['cost'];
        $data['sales'][] = (float)$row['revenue'] - (float)$row['cost'];
    }
}

// Fill missing months with zero values
if ($period === 'month') {
    for ($i = 0; $i < 12; $i++) {
        if (!isset($data['revenue'][$i])) $data['revenue'][$i] = 0;
        if (!isset($data['cost'][$i])) $data['cost'][$i] = 0;
        if (!isset($data['sales'][$i])) $data['sales'][$i] = 0;
    }
}

// Fetch top-selling products
$topSellingQuery = "SELECT p.name AS product_name, SUM(s.quantity) AS total_sold
                     FROM sales s
                     JOIN product p ON s.product_id = p.product_id
                     GROUP BY s.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";

$topSellingResult = mysqli_query($conn, $topSellingQuery);
if ($topSellingResult) {
    $data['topSelling'] = mysqli_fetch_all($topSellingResult, MYSQLI_ASSOC);
} else {
    $data['topSelling'] = [];
}

// Calculate total revenue, total costs, and ROI
$totalRevenue = array_sum($data['revenue']);
$totalCosts = array_sum($data['cost']);
$roi = ($totalCosts > 0) ? (($totalRevenue - $totalCosts) / $totalCosts) * 100 : 0;

// Add financial summary to response
$data['total_revenue'] = $totalRevenue;
$data['total_costs'] = $totalCosts;
$data['roi'] = $roi;
$data['status'] = ($roi > 0) ? "Profit" : "Loss";

// Return JSON response

echo json_encode($data);

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_last_error_msg());
}

// Close database connection
mysqli_close($conn);
?>