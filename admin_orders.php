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

// Process status update if submitted
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $conn->real_escape_string($_POST['status']);
    
    $updateQuery = "UPDATE orders SET order_status = '$newStatus' WHERE order_id = $orderId";
    
    if ($conn->query($updateQuery)) {
        $statusMessage = "Order status updated successfully.";
        $statusType = "success";
    } else {
        $statusMessage = "Failed to update order status: " . $conn->error;
        $statusType = "danger";
    }
}

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle filtering
$whereClause = "";
$filterParams = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $whereClause .= " WHERE order_status = '$status'";
    $filterParams['status'] = $status;
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause = !empty($whereClause) ? $whereClause . " AND " : " WHERE ";
    $whereClause .= "(o.order_id LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%')";
    $filterParams['search'] = $search;
}

// Count total orders for pagination
$countQuery = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.user_id" . $whereClause;
$countResult = $conn->query($countQuery);
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Get orders
$ordersQuery = "SELECT o.*, u.first_name, u.last_name, u.email 
               FROM orders o 
               JOIN users u ON o.user_id = u.user_id
               $whereClause
               ORDER BY o.created_at DESC 
               LIMIT $offset, $limit";
$ordersResult = $conn->query($ordersQuery);
$orders = [];

if ($ordersResult && $ordersResult->num_rows > 0) {
    while ($order = $ordersResult->fetch_assoc()) {
        $orders[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
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
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_orders.php">
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
                    <h1 class="h2">Manage Orders</h1>
                </div>
                
                <?php if (isset($statusMessage)): ?>
                    <div class="alert alert-<?php echo $statusType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $statusMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form action="" method="get" class="d-flex">
                            <select name="status" class="form-select me-2" style="max-width: 200px;">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo (isset($filterParams['status']) && $filterParams['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo (isset($filterParams['status']) && $filterParams['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo (isset($filterParams['status']) && $filterParams['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo (isset($filterParams['status']) && $filterParams['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo (isset($filterParams['status']) && $filterParams['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="text" name="search" class="form-control me-2" placeholder="Search orders..." value="<?php echo isset($filterParams['search']) ? htmlspecialchars($filterParams['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <?php if (!empty($filterParams)): ?>
                                <a href="admin_orders.php" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <!-- <button type="button" class="btn btn-outline-primary" onclick="exportOrders()"> -->
                            <!-- <i class="bi bi-download"></i> Export -->
                        </button>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['order_id']; ?></td>
                                        <td>
                                            <?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                                            <small><?php echo $order['email']; ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <form method="post" action="" class="status-form">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <select name="status" class="form-select form-select-sm status-select" data-initial="<?php echo $order['order_status']; ?>">
                                                    <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary d-none update-btn">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="admin_view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                    
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Orders pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($filterParams) ? '&' . http_build_query($filterParams) : ''; ?>">Previous</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($filterParams) ? '&' . http_build_query($filterParams) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($filterParams) ? '&' . http_build_query($filterParams) : ''; ?>">Next</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show update button when status is changed
        document.querySelectorAll('.status-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const initialValue = this.getAttribute('data-initial');
                const updateBtn = this.closest('form').querySelector('.update-btn');
                
                if (this.value !== initialValue) {
                    updateBtn.classList.remove('d-none');
                } else {
                    updateBtn.classList.add('d-none');
                }
            });
        });
        
        // Export orders function
        function exportOrders() {
            let url = 'admin_export_orders.php';
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('status') || urlParams.has('search')) {
                url += '?' + urlParams.toString();
            }
            
            window.location.href = url;
        }
    </script>
</body>
</html>