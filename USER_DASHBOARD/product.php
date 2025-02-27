<!-- Products Section -->
<section class="products">
    <div class="main-content">
        <nav class="navbar navbar-light p-3 mb-4 rounded">
            <h2 id="page-title">Products</h2>
        </nav>

        <!-- Welcome message and address -->
        <div class="container mt-5">
            <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>! Have a good day using the system.</h2>
            <p class="text-center">Your registered address: <?php echo htmlspecialchars($address); ?></p>
        </div>

        <!-- Products List -->
        <div id="content" class="content">
            <h3>Available Products</h3>

            <?php
            $product_types = ['renting', 'refill', 'normal'];
            
            foreach ($product_types as $type):
                // Query to fetch products by type and status
                $product_query = "SELECT * FROM product WHERE product_type = '$type' AND status = 'available'";
                $product_result = $conn->query($product_query);
            ?>

            <h4 class="mt-4 text-capitalize"><?php echo htmlspecialchars($type); ?> Products</h4>
            <ul class="list-group">
                <?php if ($product_result->num_rows > 0): ?>
                    <?php while ($product = $product_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                <?php echo htmlspecialchars($product['description']); ?><br>
                                Price: ₱<?php echo number_format($product['price'], 2); ?><br>
                                Stock: <?php echo (int) $product['stock']; ?><br>
                                Status: <span class="text-success">Available</span>
                            </div>
                            <?php if (isset($_SESSION['username']) && $product['stock'] > 0): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <label for="quantity">Qty:</label>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo (int) $product['stock']; ?>" class="form-control d-inline-block w-auto" required>
                                    <button type="submit" name="add_to_cart" class="btn btn-warning">Add to Cart</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No <?php echo htmlspecialchars($type); ?> products available.</li>
                <?php endif; ?>
            </ul>

            <?php endforeach; ?>
        </div>
    </div>
</section>
