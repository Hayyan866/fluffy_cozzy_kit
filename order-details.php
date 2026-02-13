<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "cats_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];

// Get order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect to orders page if no valid order ID provided
    header("Location: customer_dashboard.php");
    exit();
}
$order_id = $_GET['id'];

// Check if order belongs to this user (for security)
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// If no matching order found, redirect or show sample data for demonstration
$order = null;
if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
}
$stmt->close();

// Get order items
$order_items = [];
if ($order) {
    $sql = "SELECT oi.*, p.name, p.image_path 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_items = $stmt->get_result();
    $stmt->close();
} else {
    // For demonstration, we'll show sample data if no real order exists
    // This is just for display purposes
    $order = [
        'order_id' => $order_id,
        'created_at' => '2025-04-28 10:30:45',
        'updated_at' => '2025-04-29 16:22:18',
        'status' => 'delivered',
        'first_name' => 'Muhammad',
        'last_name' => 'Mairaj',
        'address' => '123 Main Street, Apartment 4B',
        'city' => 'Mumbai',
        'postal_code' => '400001',
        'phone' => '+91 98765 43210',
        'email' => 'mhmmdmairaj@gmail.com',
        'payment_method' => 'card',
        'shipping_fee' => 250.00,
        'discount_amount' => 150.00,
        'total_amount' => 2200.00
    ];
}

// Function to format currency
function formatCurrency($amount) {
    return 'PKR' . number_format($amount, 2);
}

// Function to get status class
function getStatusClass($status) {
    switch ($status) {
        case 'delivered':
            return 'status-delivered';
        case 'processing':
            return 'status-processing';
        case 'shipped':
            return 'status-shipped';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
}

// Function to get the status timeline based on current status
function getStatusTimeline($status) {
    $timeline = [
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'delivered' => 4
    ];
    
    return $timeline[$status] ?? 1;
}

// Get timeline step based on order status
$timeline_step = 1;
if ($order) {
    $timeline_step = getStatusTimeline($order['status']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Fluffy Cozzy Kit</title>
    <style>
        :root {
            --primary-color: #6ABD45;
            --secondary-color: #333;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: #666;
            --white: #fff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --success-color: #6ABD45;
            --warning-color: #FFC107;
            --info-color: #17A2B8;
            --danger-color: #DC3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
        }

        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 80px;
        }

        .logo-text {
            line-height: 1.2;
        }

        .logo-text .green {
            color: var(--primary-color);
            font-size: 24px;
            font-weight: bold;
        }

        .logo-text .black {
            color: var(--secondary-color);
            font-size: 24px;
            font-weight: bold;
        }

        .nav-center {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-right {
            display: flex;
            list-style: none;
            gap: 20px;
            align-items: center;
        }

        .nav-center li a, .nav-right li a {
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-center li a:hover, .nav-right li a:hover {
            color: var(--primary-color);
        }

        .nav-center li a.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .search-bar {
            position: relative;
        }

        .search-bar input {
            padding: 8px 15px 8px 35px;
            border-radius: 20px;
            border: 1px solid var(--medium-gray);
            width: 200px;
            outline: none;
        }

        .search-bar img {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .breadcrumb {
            display: flex;
            list-style: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb li:not(:last-child)::after {
            content: "›";
            margin: 0 10px;
            color: var(--dark-gray);
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title h1 {
            color: var(--secondary-color);
            font-size: 28px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5aa038;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(106, 189, 69, 0.1);
        }

        .order-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .order-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .order-id {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .order-date {
            font-size: 14px;
            color: var(--dark-gray);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-delivered {
            background-color: rgba(106, 189, 69, 0.1);
            color: var(--success-color);
        }

        .status-processing {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .status-shipped {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        .status-pending {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .order-timeline {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
            position: relative;
        }

        .order-timeline::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 4px;
            background-color: var(--medium-gray);
            z-index: 1;
        }

        .timeline-step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--white);
            border: 4px solid var(--medium-gray);
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .step-active .step-icon {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step-completed .step-icon {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .timeline-step .step-label {
            font-size: 14px;
            color: var(--dark-gray);
            text-align: center;
        }

        .step-active .step-label,
        .step-completed .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .orders-table th {
            background-color: var(--light-gray);
            text-align: left;
            padding: 12px 15px;
            color: var(--secondary-color);
            font-weight: 600;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: middle;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .product-col {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .product-name {
            font-weight: 500;
            color: var(--secondary-color);
        }

        .product-category {
            font-size: 13px;
            color: var(--dark-gray);
        }

        .price-col {
            font-weight: 500;
            color: var(--secondary-color);
        }

        .summary-table {
            width: 350px;
            margin-left: auto;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--medium-gray);
            color: var(--dark-gray);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 18px;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .address-box {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: var(--border-radius);
        }

        .address-box h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .address-content {
            color: var(--dark-gray);
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 20px;
            text-align: center;
            margin-top: 50px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .nav-center {
                gap: 15px;
            }
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .order-timeline {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .order-timeline::before {
                top: 0;
                bottom: 0;
                left: 17px;
                right: auto;
                width: 4px;
                height: 100%;
            }

            .timeline-step {
                flex-direction: row;
                align-items: center;
                gap: 20px;
            }

            .summary-table {
                width: 100%;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }

            .product-col {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="cat.png" alt="Logo">
                <div class="logo-text">
                    <span class="green">Fluffy</span> 
                    <span class="green">cozzy</span><br>
                    <span class="black">kit</span>
                </div>
            </div>
            <ul class="nav-center">
                <li><a href="customer_dashboard.php">Home</a></li>
                <li><a href="adult-cats.php">Adult Cats</a></li>
                <li><a href="kittens.php">Kittens</a></li>
                <li><a href="cat-food.php">Cat Food</a></li>
                <li><a href="accessories.php">Accessories</a></li>
            </ul>

        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Breadcrumb -->
        <ul class="breadcrumb">
            <li><a href="customer_dashboard.php">Dashboard</a></li>
            <li>Order #<?php echo $order_id; ?></li>
        </ul>

        <!-- Page Title -->
        <div class="page-title">
            <h1>Order Details</h1>
            <a href="customer_dashboard.php" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Orders
            </a>
        </div>

        <!-- Order Status Card -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <div class="order-id">Order #<?php echo $order_id; ?></div>
                    <div class="order-date">Placed on <?php echo date("F d, Y", strtotime($order['created_at'])); ?></div>
                </div>
                <div class="order-status">
                    <span class="status-badge <?php echo getStatusClass($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="order-timeline">
                <div class="timeline-step <?php echo $timeline_step >= 1 ? 'step-completed' : ''; ?>">
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                    </div>
                    <div class="step-label">Order Placed</div>
                </div>
                <div class="timeline-step <?php echo $timeline_step >= 2 ? ($timeline_step == 2 ? 'step-active' : 'step-completed') : ''; ?>">
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="M6 8h.01"></path>
                            <path d="M12 8h.01"></path>
                            <path d="M18 8h.01"></path>
                            <path d="M6 12h.01"></path>
                            <path d="M12 12h.01"></path>
                            <path d="M18 12h.01"></path>
                            <path d="M6 16h.01"></path>
                            <path d="M12 16h.01"></path>
                            <path d="M18 16h.01"></path>
                        </svg>
                    </div>
                    <div class="step-label">Processing</div>
                </div>
                <div class="timeline-step <?php echo $timeline_step >= 3 ? ($timeline_step == 3 ? 'step-active' : 'step-completed') : ''; ?>">
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <div class="step-label">Shipped</div>
                </div>
                <div class="timeline-step <?php echo $timeline_step >= 4 ? 'step-active' : ''; ?>">
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="step-label">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="order-card">
            <h2>Order Items</h2>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($order_items) && $order_items && $order_items->num_rows > 0): ?>
                        <?php while ($item = $order_items->fetch_assoc()): ?>
                            <tr>
                                <td class="product-col">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-img">
                                    <?php else: ?>
                                        <img src="https://media.istockphoto.com/id/1430710496/photo/cat-toy-fish-isolated-on-white-background.jpg" alt="Product Image" class="product-img">
                                    <?php endif; ?>
                                    <div class="product-info">
                                        <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="product-category">Product ID: <?php echo $item['product_id']; ?></div>
                                    </div>
                                </td>
                                <td class="price-col"><?php echo formatCurrency($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="price-col"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Sample data for demonstration -->
                        <tr>
                            <td class="product-col">
                                <img src="https://media.istockphoto.com/id/1224543871/photo/dry-pet-food-in-bowl.jpg" alt="Royal Canin Cat Food" class="product-img">
                                <div class="product-info">
                                    <div class="product-name">Royal Canin Cat Food</div>
                                    <div class="product-category">Category: Cat Food</div>
                                </div>
                            </td>
                            <td class="price-col">PKR1,100.00</td>
                            <td>2</td>
                            <td class="price-col">PKR2,200.00</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Order Summary -->
            <div class="summary-table">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatCurrency($order['total_amount'] - $order['shipping_fee'] + $order['discount_amount']); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?php echo formatCurrency($order['shipping_fee']); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="summary-row">
                    <span>Discount</span>
                    <span>-<?php echo formatCurrency($order['discount_amount']); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Total</span>
                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="order-card">
            <h2>Order Information</h2>
            <div class="address-grid">
                <div class="address-box">
                    <h3>Shipping Address</h3>
                    <div class="address-content">
                        <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                        <p><?php echo htmlspecialchars($order['address']); ?></p>
                        <p><?php echo htmlspecialchars($order['city'] . ' ' . $order['postal_code']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                    </div>
                </div>
                <div class="address-box">
                    <h3>Payment Information</h3>
                    <div class="address-content">
                        <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] === 'card' ? 'Credit/Debit Card' : 'Cash on Delivery'; ?></p>
                        <?php if ($order['payment_method'] === 'card'): ?>
                        <p><strong>Card:</strong> Visa ending in 4832</p>
                        <p><strong>Status:</strong> Payment Successful</p>
                        <?php else: ?>
                        <p><strong>Status:</strong> To be paid on delivery</p>
                        <?php endif; ?>
                        <p><strong>Date:</strong> <?php echo date("F d, Y", strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                    <a href="#" class="btn btn-outline" onclick="trackOrder(<?php echo $order_id; ?>); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Track Order
                    </a>
                <?php endif; ?>
                <a href="#" class="btn btn-outline" onclick="downloadInvoice(<?php echo $order_id; ?>); return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download Invoice
                </a>
                <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                    <a href="#" class="btn btn-outline" style="color: #DC3545; border-color: #DC3545;" onclick="cancelOrder(<?php echo $order_id; ?>); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Cancel Order
                    </a>
                <?php endif; ?>
                <?php if ($order['status'] === 'delivered'): ?>
                    <a href="#" class="btn btn-primary" onclick="returnOrder(<?php echo $order_id; ?>); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12l-8-8v5H5v6h8v5l8-8z"></path>
                        </svg>
                        Return Items
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Fluffy Cozzy Kit. All rights reserved.</p>
    </footer>

    <!-- JavaScript -->
    <script>
        // Function to handle order tracking
        function trackOrder(orderId) {
            alert("Tracking information for Order #" + orderId + " will be displayed here.");
            // In a real application, this would redirect to a tracking page or show a modal with tracking info
        }

        // Function to handle invoice download
        function downloadInvoice(orderId) {
            alert("Invoice for Order #" + orderId + " will be downloaded.");
            // In a real application, this would trigger a download of the invoice PDF
        }

        // Function to handle order cancellation
        function cancelOrder(orderId) {
            if (confirm("Are you sure you want to cancel Order #" + orderId + "?")) {
                alert("Order #" + orderId + " has been cancelled.");
                // In a real application, this would send an AJAX request to cancel the order
                // and then reload the page or update the UI
                location.reload();
            }
        }

        // Function to handle order return
        function returnOrder(orderId) {
            alert("You will be redirected to the return process for Order #" + orderId);
            // In a real application, this would redirect to a return form page
        }

        // Initialize cart count
        document.addEventListener('DOMContentLoaded', function() {
            // This would typically be fetched from a session or local storage
            document.getElementById('cartCount').textContent = '0';
        });
    </script>
</body>
</html>