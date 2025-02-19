<style>
    .product-item {
        display: flex;
        flex-direction: column;
        align-items: center; /* Center-align all elements */
        justify-content: center;
        text-align: center; /* Center text content */
        width: 250px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        margin: 10px;
        background-color: #fff;
    }

    .product-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 10px;
    }

    .product-item h5 {
        font-size: 18px;
        margin: 10px 0;
    }

    .product-item p {
        font-size: 14px;
        color: #555;
    }

    .form-control {
        width: 100%;
        text-align: center;
    }

    .btn-warning {
        width: 100%;
        padding: 8px;
        font-size: 16px;
    }
</style>

<div class="product-item">
    <img src="<?php echo htmlspecialchars($product['photo']); ?>" class="product-image" alt="Product Image">
    <h5><?php echo htmlspecialchars($product['name']); ?></h5>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
    
    <form method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="1" min="1" class="form-control mb-2" required>
        <button type="submit" name="add_to_cart" class="btn btn-warning">Add to Cart</button>
    </form>
</div>
