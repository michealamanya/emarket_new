<?php
// db_connect.php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "e_market"; 

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f4f7fa;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            min-height: 100vh;
        }
        .sidebar a {
            color: white;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            padding: 20px;
        }
        .notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            background-color: #f8f9fa;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            z-index: 1050;
            text-align: center;
        }
        .card-icon {
            font-size: 50px;
            color: #007bff;
        }
    </style>
</head>
<body>

    <div class="d-flex">
        <nav class="sidebar p-3">
            <h4>Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="refreshPage()"><i class="bi bi-house-door"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/manage_users.php"><i class="bi bi-person"></i> Manage Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/view_messages.php"><i class="bi bi-envelope"></i> View Messages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/view_carts.php"><i class="bi bi-cart"></i> View Carts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/get_products.php"><i class="bi bi-box"></i> Manage Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/manage_categories.php"><i class="bi bi-tags"></i> Manage Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_requests.php"><i class="bi bi-bell"></i> User Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminManagement/settings.php"><i class="bi bi-gear"></i> Settings</a>
                </li>
                <li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between" href="adminManagement/generate_report.php">
        <span><i class="bi bi-file-earmark-arrow-down"></i> Generate Reports</span>
        <span class="badge bg-info rounded-pill">PDF</span> <!-- Example badge indicating report format -->
    </a>
</li>
            </ul>
        </nav>

        <div class="content flex-grow-1">
            <h2 class="mb-4">Dashboard Overview</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-person card-icon"></i>
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text" id="totalUsers">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-box card-icon"></i>
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text" id="totalProducts">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-cart card-icon"></i>
                            <h5 class="card-title">Total Orders</h5>
                            <p class="card-text" id="totalOrders">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-bell card-icon"></i>
                            <h5 class="card-title">Pending Requests</h5>
                            <p class="card-text" id="pendingRequests">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <canvas id="myChart" width="400" height="200"></canvas>

            <h3 class="mt-5">Latest User Messages</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Message</th>
                        <th scope="col">Date</th>
                    </tr>
                </thead>
                <tbody id="messageTable">
                    <tr>
                        <td colspan="3" class="text-center">Loading messages...</td>
                    </tr>
                </tbody>
            </table>

            <h3 class="mt-5">Manage Products</h3>
            <input type="text" id="productSearch" class="form-control mb-3" placeholder="Search for products...">
            <table class="table table-striped" id="productTable">
                <thead>
                    <tr>
                        <th scope="col">Product ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center">Loading products...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="notification" id="refreshNotification">
        <i class="bi bi-arrow-clockwise" style="font-size: 24px;"></i>
        <div id="notificationMessage">Refreshing...</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            fetchDashboardData();

            // Fetch data for dashboard
            function fetchDashboardData() {
                $.get('api/total_users.php', function(data) {
                    $('#totalUsers').text(data.total);
                });

                $.get('api/total_products.php', function(data) {
                    $('#totalProducts').text(data.total);
                });

                $.get('api/total_orders.php', function(data) {
                    $('#totalOrders').text(data.total);
                });

                $.get('api/pending_requests.php', function(data) {
                    $('#pendingRequests').text(data.total);
                });

                $.get('api/latest_messages.php', function(messages) {
                    const messageTable = $('#messageTable');
                    messageTable.empty();
                    messages.forEach(function(message) {
                        messageTable.append(`
                            <tr>
                                <td>${message.username}</td>
                                <td>${message.content}</td>
                                <td>${message.date}</td>
                            </tr>
                        `);
                    });
                });

                loadProducts();
                loadChartData(); // Load chart data
            }

            // Load products function
            function loadProducts() {
                $.get('api/get_products.php', function(products) {
                    const productTable = $('#productTable tbody');
                    productTable.empty();
                    products.forEach(function(product) {
                        productTable.append(`
                            <tr>
                                <td>${product.id}</td>
                                <td>${product.name}</td>
                                <td>${product.price}</td>
                                <td>${product.stock}</td>
                                <td>
                                    <a href="adminManagement/edit_product.php?id=${product.id}" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="adminManagement/delete_product.php?id=${product.id}" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        `);
                    });
                });
            }

            // Load chart data
            function loadChartData() {
                $.get('api/chart_data.php', function(data) {
                    const ctx = document.getElementById('myChart').getContext('2d');
                    const myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'User Registrations',
                                data: data.values,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            }

            // Refresh page function
            function refreshPage() {
                $('#refreshNotification').fadeIn();
                fetchDashboardData();
                setTimeout(function() {
                    $('#refreshNotification').fadeOut();
                }, 2000);
            }

            // Search functionality
            $('#productSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('#productTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
                });
            });
        });
    </script>
</body>
</html>
