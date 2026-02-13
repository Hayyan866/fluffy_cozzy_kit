<?php
// Include database connection
$db_host = 'localhost';
$db_user = 'root'; // Change to your database username
$db_password = ''; // Change to your database password
$db_name = 'cats_db';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}


// Check if admin is logged in
// session_start();
// if (!isset($_SESSION['admin_email'])) {
//     header("Location: admin_login.php");
//     exit;
// }

// Get order stats
$totalOrdersQuery = "SELECT COUNT(*) as total FROM orders";
$pendingOrdersQuery = "SELECT COUNT(*) as pending FROM orders WHERE order_status = 'pending'";
$processingOrdersQuery = "SELECT COUNT(*) as processing FROM orders WHERE order_status = 'processing'";
$shippedOrdersQuery = "SELECT COUNT(*) as shipped FROM orders WHERE order_status = 'shipped'";
$deliveredOrdersQuery = "SELECT COUNT(*) as delivered FROM orders WHERE order_status = 'delivered'";
$cancelledOrdersQuery = "SELECT COUNT(*) as cancelled FROM orders WHERE order_status = 'cancelled'";

$totalOrders = $conn->query($totalOrdersQuery)->fetch_assoc()['total'] ?? 0;
$pendingOrders = $conn->query($pendingOrdersQuery)->fetch_assoc()['pending'] ?? 0;
$processingOrders = $conn->query($processingOrdersQuery)->fetch_assoc()['processing'] ?? 0;
$shippedOrders = $conn->query($shippedOrdersQuery)->fetch_assoc()['shipped'] ?? 0;
$deliveredOrders = $conn->query($deliveredOrdersQuery)->fetch_assoc()['delivered'] ?? 0;
$cancelledOrders = $conn->query($cancelledOrdersQuery)->fetch_assoc()['cancelled'] ?? 0;

// Get recent orders
$recentOrdersQuery = "SELECT o.*, u.first_name, u.last_name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    ORDER BY o.created_at DESC 
                    LIMIT 10";
$recentOrdersResult = $conn->query($recentOrdersQuery);
$recentOrders = [];

if ($recentOrdersResult && $recentOrdersResult->num_rows > 0) {
    while ($order = $recentOrdersResult->fetch_assoc()) {
        $recentOrders[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .status-pending {
            color: #fd7e14;
        }
        .status-processing {
            color: #0d6efd;
        }
        .status-shipped {
            color: #198754;
        }
        .status-delivered {
            color: #198754;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Admin Dashboard</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="logout.php">Sign out</a>
            </div>
        </div>
    </header>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_dashboard.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_orders.php">
                                <i class="bi bi-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                     
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                                <i class="bi bi-calendar3"></i> Today
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Order Stats -->
                <div class="row mb-4">
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <h2><?php echo $totalOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-warning bg-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title">Pending</h5>
                                <h2><?php echo $pendingOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-primary bg-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title">Processing</h5>
                                <h2><?php echo $processingOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-info bg-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title">Shipped</h5>
                                <h2><?php echo $shippedOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-success bg-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title">Delivered</h5>
                                <h2><?php echo $deliveredOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-3">
                        <div class="card stat-card text-center bg-danger bg-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title">Cancelled</h5>
                                <h2><?php echo $cancelledOrders; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <h2>Recent Orders</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['order_id']; ?></td>
                                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>PKR <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo strtolower($order['order_status']); ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin_view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-3">
                    <a href="admin_orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>