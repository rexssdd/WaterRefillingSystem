<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparing Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
     <link rel = "stylesheet" href = "https://unicons.iconscout.com/release/v4.0.0/css/line.css"/>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                               <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: top;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
       
        button {
            position: absolute;
            bottom: 50px;
            right: 400px;
            padding: 10px 15px;
            background-color:rgb(11, 72, 116);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Orders Currently Being Prepared</h1>
        <form id="updateOrdersForm">
        <table class=" table table-dark table-striped table-hover">

                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Customer Name</th>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Products</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Orders will be inserted here dynamically -->
                </tbody>
            </table>
            <br>
            <button type="submit">Mark as To Deliver</button>
        </form>
    </div>
    <button class="logout-button" onclick="confirmLogout()">Logout</button>

<script>
    function confirmLogout() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = '/php/logout.php';
        }
    }
        document.addEventListener('DOMContentLoaded', function() {
            fetch('fetch_reffiler.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched orders:', data);
                    const tableBody = document.getElementById('ordersTableBody');
                    tableBody.innerHTML = '';
                    data.forEach(order => {
                        const row = `<tr>
                            <td><input type="checkbox" name="order_ids[]" value="${order.order_id}"></td>
                            <td>${order.customer_name}</td>
                            <td>${order.order_id}</td>
                            <td>${order.order_date}</td>
                            <td>${order.products}</td>
                            <td>${order.status}</td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => console.error('Error fetching preparing orders:', error));
        });

        document.getElementById('updateOrdersForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('update_stat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(error => console.error('Error updating orders:', error));
        });
    </script>
</body>
</html>
