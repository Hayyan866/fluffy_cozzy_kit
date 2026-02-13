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

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$order_id = $_GET['id'];

// Handle status update
$statusUpdateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $updateQuery = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newStatus, $order_id);
    
    if ($stmt->execute()) {
        $statusUpdateMessage = '<div class="alert alert-success">Order status updated successfully.</div>';
    } else {
        $statusUpdateMessage = '<div class="alert alert-danger">Failed to update order status.</div>';
    }
    $stmt->close();
}

// Get order details
$orderQuery = "SELECT o.*, u.first_name, u.last_name, u.email 
               FROM orders o 
               LEFT JOIN users u ON o.user_id = u.user_id 
               WHERE o.order_id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    header("Location: admin_orders.php");
    exit;
}

$order = $orderResult->fetch_assoc();
$stmt->close();

// Get order items
$orderItemsQuery = "SELECT oi.*, p.name, p.image_path 
                   FROM order_items oi 
                   JOIN products p ON oi.product_id = p.product_id 
                   WHERE oi.order_id = ?";
$stmt = $conn->prepare($orderItemsQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderItemsResult = $stmt->get_result();
$orderItems = [];

while ($item = $orderItemsResult->fetch_assoc()) {
    $orderItems[] = $item;
}
$stmt->close();

// Calculate subtotal
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order #<?php echo $order_id; ?> - Admin</title>
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
        .order-details-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
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
                    <h1 class="h2">Order #<?php echo $order_id; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="admin_orders.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                        <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Order
                        </button>
                    </div>
                </div>
                
                <?php echo $statusUpdateMessage; ?>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card order-details-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order Details</h5>
                                <span class="badge bg-<?php 
                                    if ($order['order_status'] == 'pending') echo 'warning';
                                    else if ($order['order_status'] == 'processing') echo 'primary';
                                    else if ($order['order_status'] == 'shipped') echo 'info';
                                    else if ($order['order_status'] == 'delivered') echo 'success';
                                    else if ($order['order_status'] == 'cancelled') echo 'danger';
                                    ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] == 'cod' ? 'Cash on Delivery' : 'Credit Card'; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['updated_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card order-details-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orderItems as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($item['image_path']): ?>
                                                                <img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['name']; ?>" class="product-img me-2">
                                                            <?php else: ?>
                                                                <div class="bg-light product-img me-2 d-flex align-items-center justify-content-center">
                                                                    <i class="bi bi-image text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <span><?php echo $item['name']; ?></span>
                                                        </div>
                                                    </td>
                                                    <td>PKR <?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td class="text-end">PKR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                                <td class="text-end">PKR <?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Shipping</strong></td>
                                                <td class="text-end">PKR <?php echo number_format($order['shipping_fee'], 2); ?></td>
                                            </tr>
                                            <?php if ($order['discount_amount'] > 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Discount</strong></td>
                                                <td class="text-end">- PKR <?php echo number_format($order['discount_amount'], 2); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                                                <td class="text-end"><strong>PKR <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card order-details-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                               
                            </div>
                        </div>
                        
                        <div class="card order-details-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                                <p><?php echo $order['address']; ?></p>
                                <p><?php echo $order['city'] . ($order['postal_code'] ? ', ' . $order['postal_code'] : ''); ?></p>
                                <p><?php echo $order['phone']; ?></p>
                            </div>
                        </div>
                        
                        <div class="card order-details-card">
                            <div class="card-header">
                                <h5 class="mb-0">Update Order Status</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="pending" <?php if ($order['order_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="processing" <?php if ($order['order_status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                            <option value="shipped" <?php if ($order['order_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="delivered" <?php if ($order['order_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="cancelled" <?php if ($order['order_status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>