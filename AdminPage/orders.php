

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f4f7f6; }
        .navbar { border-bottom: 2px solid #0066cc; }
        .navbar-brand { font-size: 1.8rem; font-weight: bold; }
        .table th, .table td { text-align: center; }
        .card { margin-bottom: 20px; }
        .btn-primary, .btn-secondary { font-weight: bold; }
        .card-header { background-color: #0066cc; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Water Refilling System Admin</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="card-header">Manage Orders</h2>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Pending Orders</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="pending-orders">
                                <!-- Pending Orders here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Preparing Orders</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="preparing-orders">
                                <!-- Preparing Orders here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Delivery Orders</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Amount</th>
                      <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="delivery-orders">
                        <!-- Orders to deliver will be displayed here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Completed & Cancelled Orders</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="completed-cancelled-orders">
                                <!-- Completed, Cancelled, Failed Orders here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

async function fetchOrders() {
    try {
        const response = await fetch('fetch_orders.php');
        const orders = await response.json();

        const pendingOrders = document.getElementById('pending-orders');
        const preparingOrders = document.getElementById('preparing-orders');
        const deliveryOrders = document.getElementById('delivery-orders');
        const completedCancelled = document.getElementById('completed-cancelled-orders');
        
        // Clear previous data
        pendingOrders.innerHTML = '';
        preparingOrders.innerHTML = '';
        deliveryOrders.innerHTML = '';
        completedCancelled.innerHTML = '';

        orders.forEach(order => {
            const row = `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.customer_name || 'Guest'}</td>
                    <td>${order.status}</td>
                    <td>${order.payment_status || 'unpaid'}</td>
                    <td>${order.amount || '0.00'}</td>
                    <td>${order.payment_method || 'N/A'}</td>
                    ${
                        order.status === 'pending'
                            ? `<td><button class="btn btn-success btn-sm" onclick="acceptOrder(${order.order_id})">Accept</button></td>`
                            : '<td>-</td>'
                    }
                </tr>
            `;

            if (order.status === 'pending') {
                pendingOrders.innerHTML += row;
            } else if (order.status === 'preparing') {
                preparingOrders.innerHTML += row;
            } else if (order.status === 'to deliver') {
                deliveryOrders.innerHTML += row;
            } else if (['completed', 'cancelled', 'failed'].includes(order.status)) {
                completedCancelled.innerHTML += row;
            }
        });
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
}

document.addEventListener("DOMContentLoaded", fetchOrders);

        async function fetchOrders() {
            try {
                const response = await fetch('fetch_orders.php');
                const orders = await response.json();

                const pendingOrders = document.getElementById('pending-orders');
                const preparingOrders = document.getElementById('preparing-orders');
                const deliveryOrders = document.getElementById('delivery-orders');
                const completedCancelled = document.getElementById('completed-cancelled-orders');
                
                pendingOrders.innerHTML = '';
                preparingOrders.innerHTML = '';
                deliveryOrders.innerHTML = '';
                completedCancelled.innerHTML = '';

                orders.forEach(order => {
                    const row = `
                        <tr>
                            <td>${order.order_id}</td>
                            <td>${order.customer_name || 'Guest'}</td>
                            <td>${order.status}</td>
                             <td>${order.amount || '0.00'}</td>
                              <td>${order.payment_method || 'N/A'}</td>
                             <td>${order.payment_status || 'unpaid'}</td>
                            ${order.status === 'pending' ? `<td><button class="btn btn-success btn-sm" onclick="acceptOrder(${order.order_id})">Accept</button></td>` : '<td>-</td>'}
                        </tr>
                    `;

                    if (order.status === 'pending') {
                        pendingOrders.innerHTML += row;
                    } else if (order.status === 'preparing') {
                        preparingOrders.innerHTML += row;
                    } else if (order.status === 'to deliver') {
                          deliveryOrders.innerHTML += row;
                    } else if (['completed', 'cancelled', 'failed'].includes(order.status)) {
                        completedCancelled.innerHTML += row;
                    }
                });
            } catch (error) {
                console.error('Error fetching orders:', error);
            }
        }

        document.addEventListener("DOMContentLoaded", fetchOrders);
       
        async function acceptOrder(orderId) {
    try {
        const response = await fetch('accept_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        });

        const result = await response.json();

        if (result.success) {
            alert("Order Accepted! Status will auto-update to 'To Deliver' after 5 seconds.");
            fetchOrders(); // Refresh order list
        } else {
            alert("Error: " + result.error);
        }
    } catch (error) {
        console.error("Error accepting order:", error);
    }
}


</script>

</body>
</html>
