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

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle filtering
$whereClause = "WHERE role IS NULL OR role != 'admin'";
$filterParams = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
    $filterParams['search'] = $search;
}

// Count total customers for pagination
$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countResult = $conn->query($countQuery);
$totalCustomers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCustomers / $limit);

// Get customers
$customersQuery = "SELECT user_id, first_name, last_name, email, created_at FROM users 
                  $whereClause
                  ORDER BY created_at DESC 
                  LIMIT $offset, $limit";
$customersResult = $conn->query($customersQuery);
$customers = [];

if ($customersResult && $customersResult->num_rows > 0) {
    while ($customer = $customersResult->fetch_assoc()) {
        $customers[] = $customer;
    }
}

// Get order counts for each customer
foreach ($customers as $key => $customer) {
    $orderCountQuery = "SELECT COUNT(*) as total_orders, 
                       SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
                       SUM(total_amount) as total_spent
                       FROM orders 
                       WHERE user_id = " . $customer['user_id'];
    $orderResult = $conn->query($orderCountQuery);
    
    if ($orderResult) {
        $orderData = $orderResult->fetch_assoc();
        $customers[$key]['total_orders'] = $orderData['total_orders'] ?? 0;
        $customers[$key]['completed_orders'] = $orderData['completed_orders'] ?? 0;
        $customers[$key]['total_spent'] = $orderData['total_spent'] ?? 0;
    } else {
        $customers[$key]['total_orders'] = 0;
        $customers[$key]['completed_orders'] = 0;
        $customers[$key]['total_spent'] = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Customers</title>
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
        .customer-badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .customer-badge-gold {
            color: #fff;
            background-color: #ffc107;
        }
        .customer-badge-silver {
            color: #fff;
            background-color: #6c757d;
        }
        .customer-badge-bronze {
            color: #fff;
            background-color: #cd7f32;
        }
        .customer-badge-new {
            color: #fff;
            background-color: #20c997;
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
                            <a class="nav-link" href="admin_orders.php">
                                <i class="bi bi-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_customers.php">
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
                    <h1 class="h2">Manage Customers</h1>
                </div>
                
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form action="" method="get" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Search customers..." value="<?php echo isset($filterParams['search']) ? htmlspecialchars($filterParams['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if (!empty($filterParams)): ?>
                                <a href="admin_customers.php" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                
                </div>
                
                <!-- Customer Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Customers</h6>
                                <h2><?php echo $totalCustomers; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">New This Month</h6>
                                <?php
                                $currentMonth = date('Y-m-01');
                                $newCustomersQuery = "SELECT COUNT(*) as count FROM users 
                                                      WHERE (role IS NULL OR role != 'admin') 
                                                      AND created_at >= '$currentMonth'";
                                $newCustomersResult = $conn->query($newCustomersQuery);
                                $newCustomers = $newCustomersResult->fetch_assoc()['count'] ?? 0;
                                ?>
                                <h2><?php echo $newCustomers; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Active Customers</h6>
                                <?php
                                $activeCustomersQuery = "SELECT COUNT(DISTINCT user_id) as count FROM orders 
                                                        WHERE user_id IS NOT NULL 
                                                        AND created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
                                $activeCustomersResult = $conn->query($activeCustomersQuery);
                                $activeCustomers = $activeCustomersResult->fetch_assoc()['count'] ?? 0;
                                ?>
                                <h2><?php echo $activeCustomers; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Avg. Lifetime Value</h6>
                                <?php
                                $avgLtvQuery = "SELECT AVG(total_amount) as avg_ltv FROM (
                                                SELECT user_id, SUM(total_amount) as total_amount 
                                                FROM orders 
                                                WHERE user_id IS NOT NULL 
                                                GROUP BY user_id
                                                ) as customer_totals";
                                $avgLtvResult = $conn->query($avgLtvQuery);
                                $avgLtv = $avgLtvResult->fetch_assoc()['avg_ltv'] ?? 0;
                                ?>
                                <h2>PKR<?php echo number_format($avgLtv, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customers Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No customers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                        <td><?php echo $customer['email']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                            <?php echo $customer['total_orders']; ?> 
                                            <?php if ($customer['total_orders'] > 0): ?>
                                                (<?php echo $customer['completed_orders']; ?> completed)
                                            <?php endif; ?>
                                        </td>
                                        <td>PKR <?php echo number_format($customer['total_spent'], 2); ?></td>
                                        <td>
                                            <?php
                                            // Determine customer status based on total spent
                                            if ($customer['total_spent'] > 1000) {
                                                echo '<span class="customer-badge customer-badge-gold">Gold</span>';
                                            } elseif ($customer['total_spent'] > 500) {
                                                echo '<span class="customer-badge customer-badge-silver">Silver</span>';
                                            } elseif ($customer['total_spent'] > 100) {
                                                echo '<span class="customer-badge customer-badge-bronze">Bronze</span>';
                                            } else {
                                                // Check if customer is new (less than 30 days)
                                                $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
                                                if (date('Y-m-d', strtotime($customer['created_at'])) >= $thirtyDaysAgo) {
                                                    echo '<span class="customer-badge customer-badge-new">New</span>';
                                                } else {
                                                    echo '<span class="text-muted">Regular</span>';
                                                }
                                            }
                                            ?>
                                        </td>
                                    
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Customers pagination">
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
    
    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="emailForm" action="admin_send_email.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="customer_id" name="customer_id">
                        <div class="mb-3">
                            <label for="email_to" class="form-label">To:</label>
                            <input type="email" class="form-control" id="email_to" name="email_to" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email_subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control" id="email_subject" name="email_subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_message" class="form-label">Message:</label>
                            <textarea class="form-control" id="email_message" name="email_message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Email modal function
        function sendEmail(customerId, email) {
            document.getElementById('customer_id').value = customerId;
            document.getElementById('email_to').value = email;
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        }
        
        
        
    </script>
</body>
</html>