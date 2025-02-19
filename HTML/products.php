<?php
require_once(".../php/dbconnect.php");

// Initialize variables to avoid "undefined variable" warnings
$normal_products = [];
$refill_products = [];
$rental_products = [];

try {
    // Fetch Normal Products
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE category = 'normal'");
    $stmt->execute();
    $normal_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Refill Products
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE category = 'refill'");
    $stmt->execute();
    $refill_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Rental Products
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE category = 'rental'");
    $stmt->execute();
    $rental_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching products: " . $e->getMessage();
}
?>
