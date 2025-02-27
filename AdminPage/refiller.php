<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparing Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0077cc;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            padding: 10px 15px;
            background-color: #0077cc;
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
            <table>
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

    <script>
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
