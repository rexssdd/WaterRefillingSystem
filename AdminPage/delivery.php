<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Team Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #343a40;
        }
        table {
            margin-top: 1rem;
        }
        .btn-success, .btn-danger {
            width: 100%;
        }
        .modal-header {
            background-color: #343a40;
            color: white;
        }
        .logout-button {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Delivery Team Dashboard</h2>
        
        <table class="table table-dark table-striped table-hover " >
 
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Confirm Delivery</th>
                    <th>Failed Delivery</th>
                </tr>
            </thead>
            <tbody id="delivery-orders">
                <!-- Orders with status 'delivery' will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Reason Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalLabel">Reason for Failed Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="failDeliveryForm">
                        <div class="mb-3">
                            <label for="failReason" class="form-label">Reason</label>
                            <textarea class="form-control" id="failReason" name="reason" required></textarea>
                        </div>
                        <input type="hidden" id="orderId" name="order_id">
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <button class="logout-button" onclick="location.href='/php/logout.php';">Logout</button>

    <script>
        async function fetchDeliveryOrders() {
            try {
                const response = await fetch('fetch_delivery_orders.php');
                const orders = await response.json();
                const tbody = document.getElementById('delivery-orders');
                tbody.innerHTML = '';

                orders.forEach(order => {
                    const row = `
                        <tr>
                            <td>${order.order_id}</td>
                            <td>${order.customer_name || 'Guest'}</td>
                            <td>${order.status}</td>
                            <td><button class="btn btn-success" onclick="confirmDelivery(${order.order_id})">Confirm</button></td>
                            <td><button class="btn btn-danger" onclick="showReasonModal(${order.order_id})">Fail</button></td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } catch (error) {
                console.error('Error fetching delivery orders:', error);
            }
        }

        async function confirmDelivery(orderId) {
            try {
                const response = await fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status: 'completed' })
                });
                const result = await response.json();
                if (result.success) fetchDeliveryOrders();
                else alert('Failed to update order status.');
            } catch (error) {
                console.error('Error confirming delivery:', error);
            }
        }

        function showReasonModal(orderId) {
            document.getElementById('orderId').value = orderId;
            new bootstrap.Modal(document.getElementById('reasonModal')).show();
        }

        document.getElementById('failDeliveryForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const orderId = document.getElementById('orderId').value;
            const reason = document.getElementById('failReason').value;

            try {
                const response = await fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status: 'failed', reason: reason })
                });
                const result = await response.json();
                if (result.success) {
                    fetchDeliveryOrders();
                    bootstrap.Modal.getInstance(document.getElementById('reasonModal')).hide();
                } else alert('Failed to update order status.');
            } catch (error) {
                console.error('Error failing delivery:', error);
            }
        });

        document.addEventListener('DOMContentLoaded', fetchDeliveryOrders);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
