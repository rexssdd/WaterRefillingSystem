
<style>.rental-product-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 15px;
    border: 2px solid #007bff;
    border-radius: 10px;
    background-color: #f0f8ff; /* Light blue background for rentals */
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    width: 250px;
}

.rental-product-item .product-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
}

.rental-product-item h5 {
    color: #007bff;
    font-size: 18px;
    margin: 10px 0;
}

.rental-product-item p {
    font-size: 14px;
    color: #333;
}

.rental-product-item .btn-primary {
    width: 100%;
}
</style>


<div class="rental-product-item">
    <img src="<?php echo htmlspecialchars($product['photo']); ?>" class="product-image" alt="Rental Product Image">
    <h5><?php echo htmlspecialchars($product['name']); ?> (Rental)</h5>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p>Rental Price: ₱<?php echo number_format($product['price'], 2); ?></p>

    <button type="button" class="btn btn-primary rent-now-btn" 
        data-product-id="<?php echo $product['product_id']; ?>"
        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
        data-product-price="<?php echo $product['price']; ?>">
        Rent Now
    </button>
</div>
