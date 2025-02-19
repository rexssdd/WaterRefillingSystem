
<?php session_start(); 

$mysqli = new mysqli("localhost", "root", "", "wrsystem");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
 <!-- Bootstrap CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
          <li class="nav-item"><a class="nav-link active" href="adminproducts.html">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="adminemployee.html">Employees</a></li>
          <li class="nav-item"><a class="nav-link" href="deliveryteam.html">Delivery</a></li>
          <li class="nav-item"><a class="nav-link" href="orders.html">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="sales.html">Sales Tracking</a></li>
        </ul>
        <div class="ms-auto">
          <button class="btn btn-danger" onclick="logout()">Logout</button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Product Management Section -->
  <div class="container mt-4">
    <h2 class="card-header">Product Management</h2>
    
    <!-- Product Table -->
    <table class="table mt-4">
      <thead>
        <tr>
          <th>Product Code</th>
          <th>Product Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Description</th>
          <th>Type</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="productList">
        <?php
        $conn = new mysqli('localhost', 'root', '', 'wrsystem');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT * FROM product";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['product_id']}</td>
                        <td>{$row['name']}</td>
                        <td>₱" . number_format($row['price'], 2) . "</td>
                        <td>{$row['stock']}</td>
                        <td>{$row['description']}</td>
                        <td>" . ucfirst($row['product_type']) . "</td>
                        <td><span class='badge bg-" . ($row['status'] == 'available' ? 'success' : 'danger') . "'>" . ucfirst($row['status']) . "</span></td>
                        <td>
                            <button class='btn btn-primary btn-sm' onclick='addStock({$row['product_id']})'>Add Stock</button>
                            <button class='btn btn-secondary btn-sm' onclick='updatePrice({$row['product_id']})'>Update Price</button>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No products found</td></tr>";
        }
        $conn->close();
        ?>
      </tbody>
    </table>
  </div>

  <!-- Add Stock Modal -->
  <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addStockForm" onsubmit="addStockToProduct(event)">
            <div class="mb-3">
              <label for="addStockQuantity" class="form-label">Stock Quantity</label>
              <input type="number" class="form-control" id="addStockQuantity" required>
            </div>
            <input type="hidden" id="productIdForStock" />
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Add Stock</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Price Modal -->
<div class="modal fade" id="updatePriceModal" tabindex="-1" aria-labelledby="updatePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePriceModalLabel">Update Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatePriceForm">
                    <div class="mb-3">
                        <label for="newPrice" class="form-label">New Price</label>
                        <input type="number" class="form-control" id="newPrice" name="new_price" required>
                    </div>
                    <input type="hidden" id="productIdForPrice" name="product_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="updatePriceBtn" class="btn btn-success">Update Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS (make sure to load this after CSS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function addStock(productId) {
      document.getElementById("productIdForStock").value = productId;
      new bootstrap.Modal(document.getElementById("addStockModal")).show();
    }

    function addStockToProduct(event) {
      event.preventDefault();
      const productId = document.getElementById("productIdForStock").value;
      const quantity = document.getElementById("addStockQuantity").value;

      if (!productId || !quantity) {
        alert("Please fill in all fields.");
        return;
      }

      fetch('add_stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${quantity}`
})
.then(response => response.text())  // Get response as plain text
.then(data => {
    console.log("Raw Response:", data); // Debug: Log the response

    try {
        const jsonData = JSON.parse(data);  // Attempt to parse as JSON
        if (jsonData.success) {
            alert("Stock updated successfully!");
            location.reload();
        } else {
            alert("Error: " + jsonData.message);
        }
    } catch (e) {
        console.error("Invalid JSON response:", e);
        alert("Unexpected server response. Check console for details.");
    }
})
.catch(error => console.error("Error:", error));

    }

    function updatePrice(productId) {
      // Set the product ID to the hidden input and show the modal
      document.getElementById("productIdForPrice").value = productId;
      new bootstrap.Modal(document.getElementById("updatePriceModal")).show();
    }

    document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("updatePriceBtn").addEventListener("click", function (event) {
        event.preventDefault(); // Prevent form submission
        
        const productId = document.getElementById("productIdForPrice").value;
        const newPrice = document.getElementById("newPrice").value; // Corrected ID

        if (!productId || !newPrice || isNaN(newPrice) || newPrice <= 0) {
            alert("Please enter a valid price and select a product.");
            return;
        }

        console.log("Sending Data: ", { product_id: productId, new_price: newPrice });

        fetch('update_price.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${encodeURIComponent(productId)}&new_price=${encodeURIComponent(newPrice)}`
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response Data:", data);
            if (data.success) {
                alert("Price updated successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Fetch Error:", error));
    });
});
  </script>

</body>
</html>
