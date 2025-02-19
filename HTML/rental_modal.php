<!-- Rental Modal -->
<div id="rentalModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Rental Details</h3>
        <form method="POST" id="rentalForm">
            <input type="hidden" name="product_id" id="modalProductId">

            <label for="productName">Product Name:</label>
            <input type="text" id="productName" readonly>

            <label for="address">Address:</label>
            <select name="address" id="userAddress" required>
                <option value="">-- Select Address --</option>
                <?php
                include 'db_connection.php';
                session_start();
                $userId = $_SESSION['user_id'];
                $query = "SELECT * FROM address WHERE user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['address_id']}'>{$row['street']}, {$row['barangay']} (Landmark: {$row['landmark']})</option>";
                    }
                } else {
                    echo "<option value=''>No address found. Please add an address.</option>";
                }
                ?>
            </select>

            <label for="rentalDuration">Rental Duration (Days):</label>
            <input type="number" name="rental_duration" id="rentalDuration" min="1" required>

            <label for="rentalStart">Start Date:</label>
            <input type="date" name="rental_start" id="rentalStart" required>

            <label for="rentalEnd">End Date:</label>
            <input type="text" id="rentalEnd" readonly>

            <button type="submit" name="confirm_rental" class="btn btn-success">Confirm Rental</button>
        </form>
    </div>
</div>
