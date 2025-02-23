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
        input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .form-inline {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Delivery Team Dashboard</h2>
        
        <table class="table table-bordered table-hover">
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
                            <td>
                                <form class="form-inline" onsubmit="return failDelivery(event, ${order.order_id})">
                                    <input type="text" name="reason" placeholder="Reason for failure" required>
                                    <button type="submit" class="btn btn-danger">Fail</button>
                                </form>
                            </td>
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

        async function failDelivery(event, orderId) {
            event.preventDefault();
            const reason = event.target.reason.value;

            try {
                const response = await fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status: 'failed', reason: reason })
                });
                const result = await response.json();
                if (result.success) fetchDeliveryOrders();
                else alert('Failed to update order status.');
            } catch (error) {
                console.error('Error failing delivery:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', fetchDeliveryOrders);
    </script>
</body>
</html>
