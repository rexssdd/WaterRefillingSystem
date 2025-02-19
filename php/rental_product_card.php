<?php 
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '/xampp/htdocs/WaterRefillingSystem/php/dbconnect.php';

// Debugging: Check if session contains user_id
$isLoggedIn = isset($_SESSION['username']) ? true : false;
?>

<style>
.rental-product-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 250px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    margin: 10px;
    background-color: black;
}

.rental-product-item .product-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
}

.rental-product-item h5 {
    color: gold;
    font-size: 18px;
    margin: 10px 0;
}

.rental-product-item p {
    font-size: 14px;
    color: white;
}

.rental-product-item .btn-primary {
    color: white;
    width: 100%;
    padding: 8px;
    font-size: 16px;
    border-radius: 10px;
    border-color: gold;
    background-color: black;
}

.btn-primary:hover {
    color: #000;
    background-color: gold;
}
</style>

<div class="rental-product-item"
     data-status="<?php echo htmlspecialchars($product['status']); ?>"
     data-stock="<?php echo $product['stock']; ?>">

    <img src="<?php echo htmlspecialchars($product['photo']); ?>" class="product-image" alt="Rental Product Image">
    <h5><strong><?php echo htmlspecialchars($product['name']); ?> (Rental)</strong></h5>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p>Rental Price: ₱<?php echo number_format($product['price'], 2); ?></p>
    <p>Stock: <strong><?php echo $product['stock']; ?></strong></p>
    <p>Status: 
        <strong class="<?php echo ($product['status'] === 'Available') ? 'text-success' : 'text-danger'; ?>">
            <?php echo htmlspecialchars($product['status']); ?>
        </strong>
    </p>

    <!-- Rent Now Button (only visible if logged in & product is available) -->
    <?php if ($isLoggedIn && $product['stock'] > 0 && $product['status'] === 'Available'): ?>
            <button type="button" class="btn-primary rent-now-btn"
            data-product-id="<?php echo $product['product_id']; ?>"
            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
            data-product-price="<?php echo $product['price']; ?>"
            data-product-stock="<?php echo $product['stock']; ?>"
            data-product-status="<?php echo $product['status']; ?>">
            Rent Now
        </button>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

    document.querySelectorAll(".rental-product-item").forEach(product => {
        let productStatus = product.getAttribute("data-status");
        let stock = parseInt(product.getAttribute("data-stock"));
        let rentNowBtn = product.querySelector(".rent-now-btn");

        // Remove "Rent Now" button if user is not logged in or product is unavailable
        if (!isLoggedIn || stock < 1 || productStatus !== "Available") {
            if (rentNowBtn) rentNowBtn.remove();
        }
    });
});
</script>
