<?php
// Start session if not already started
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: login.php?redirect=order_confirmation.php");
    exit();
}

// Include database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cats_db';

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get order ID from URL parameter or session
$orderId = null;
if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
} elseif (isset($_SESSION['last_order_id'])) {
    $orderId = intval($_SESSION['last_order_id']);
    // Clear from session to prevent accidental revisits
    unset($_SESSION['last_order_id']);
} else {
    // No order ID specified, redirect to dashboard
    header("Location: customer_dashboard.php");
    exit();
}

// Check if the order belongs to the logged-in user
$orderQuery = "SELECT * FROM orders WHERE order_id = ? AND (user_id = ? OR email = ?)";
$stmt = mysqli_prepare($conn, $orderQuery);
mysqli_stmt_bind_param($stmt, "iis", $orderId, $userId, $_SESSION['email']);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($orderResult) === 0) {
    // Order doesn't exist or doesn't belong to this user
    header("Location: customer_dashboard.php?error=invalid_order");
    exit();
}

// Get order details
$orderData = mysqli_fetch_assoc($orderResult);

// Get order items
$orderItemsQuery = "SELECT oi.*, p.name, p.image_path 
                   FROM order_items oi
                   LEFT JOIN products p ON oi.product_id = p.product_id
                   WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $orderItemsQuery);
mysqli_stmt_bind_param($stmt, "i", $orderId);
mysqli_stmt_execute($stmt);
$orderItems = mysqli_stmt_get_result($stmt);

// Calculate order summary
$subtotal = 0;
$orderProducts = [];

while ($item = mysqli_fetch_assoc($orderItems)) {
    $subtotal += ($item['price'] * $item['quantity']);
    $orderProducts[] = $item;
}

// Reset result pointer for displaying order items in the page
mysqli_data_seek($orderItems, 0);

// Format status for display
$statusClasses = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'cancelled' => 'status-cancelled'
];

$statusClass = isset($statusClasses[$orderData['status']]) ? $statusClasses[$orderData['status']] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Fluffy Cozzy Kit</title>
    <!-- Fix CSS file references with absolute paths -->
    <link rel="stylesheet" href="/css/kitten.css">
    <link rel="stylesheet" href="/css/style.css">
    
    <!-- Add inline CSS in case external files are not accessible -->
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }
        
        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            margin-right: 10px;
        }
        
        .logo-text .green {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .logo-text .black {
            color: #333;
            font-weight: bold;
        }
        
        .nav-center, .nav-right {
            display: flex;
            list-style: none;
        }
        
        .nav-center li, .nav-right li {
            margin: 0 10px;
        }
        
        .nav-center a, .nav-right a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-center a:hover, .nav-right a:hover {
            color: #4CAF50;
        }
        
        /* Account Dropdown */
        .account-dropdown {
            position: relative;
        }
        
        .account-dropdown img {
            height: 24px;
            cursor: pointer;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 4px;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-menu a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        
        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }
        
        .logout-btn {
            width: 100%;
            padding: 12px 16px;
            border: none;
            background-color: #f44336;
            color: white;
            cursor: pointer;
            text-align: left;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        
        /* Order Confirmation Styles */
        .confirmation-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .confirmation-header h1 {
            color: #4CAF50;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-header p {
            color: #666;
        }
        
        .order-success {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }
        
        .order-success svg {
            margin-right: 1rem;
            color: #4CAF50;
        }
        
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .order-info, .shipping-info {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .info-section {
            margin-bottom: 1.5rem;
        }
        
        .info-section h3 {
            color: #4CAF50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            text-align: right;
        }
        
        .order-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .status-processing {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .status-shipped {
            background-color: #e8f5e9;
            color: #1b5e20;
        }
        
        .status-delivered {
            background-color: #e8f5e9;
            color: #1b5e20;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #b71c1c;
        }
        
        .order-items {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .order-items h2 {
            color: #4CAF50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-list {
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            margin-right: 1rem;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
        }
        
        .item-quantity {
            color: #666;
            font-size: 14px;
        }
        
        .item-total {
            font-weight: 500;
            margin-left: 1rem;
            min-width: 100px;
            text-align: right;
        }
        
        .order-summary {
            margin-top: 1.5rem;
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #333;
            border-bottom: none;
            margin-top: 1rem;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #45a049;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        /* Print Styles */
        @media print {
            header, footer, .action-buttons {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .confirmation-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .order-details-grid, .order-items, .order-info, .shipping-info {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .info-section, .order-success {
                page-break-inside: avoid;
            }
        }
        
        /* Footer Styles */
        footer {
            background-color: #333;
            color: #fff;
            padding: 3rem 0 1rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 0 20px;
        }
        
        .footer-section h3 {
            color: #4CAF50;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: #4CAF50;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 1rem;
        }
        
        .social-links img {
            width: 24px;
            height: 24px;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #444;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-center {
                display: none;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            .logo img {
                height: 40px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                text-align: center;
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
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <li class="account-dropdown">
                    <a href="#" id="accountLink">
                        <img src="account.png" alt="Account Logo">
                    </a>
                    <div class="dropdown-menu" id="accountDropdown">
                        <a href="customer_dashboard.php" id="myProfileLink">My Profile</a>
                        <a href="customer_dashboard.php?action=edit" id="editProfileLink">Edit Profile</a>
                        <button class="logout-btn" id="logoutBtn" onclick="window.location.href='logout.php'">Logout</button>
                    </div>
                </li>
            </ul>
        </div>
    </header>

    <!-- Order Confirmation Content -->
    <div class="confirmation-container">
        <div class="confirmation-header">
            <h1>Order Confirmation</h1>
            <p>Thank you for your order. We've received your purchase request.</p>
        </div>
        
        <div class="order-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <p>Your order has been placed successfully and is now being processed. You'll receive updates on your order status via email.</p>
        </div>
        
        <div class="order-details-grid">
            <div class="order-info">
                <div class="info-section">
                    <h3>Order Information</h3>
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value">#<?php echo str_pad($orderData['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($orderData['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Status:</span>
                        <span class="info-value">
                            <span class="order-status <?php echo $statusClass; ?>">
                                <?php echo ucfirst($orderData['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo $orderData['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card'; ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Customer Information</h3>
                    <div class="info-row">
                        <span class="info-label">Customer Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['first_name'] . ' ' . $orderData['last_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['phone']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="shipping-info">
                <div class="info-section">
                    <h3>Shipping Information</h3>
                    <div class="info-row">
                        <span class="info-label">Shipping Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['address']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">City:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['city']); ?></span>
                    </div>
                    <?php if (!empty($orderData['postal_code'])): ?>
                    <div class="info-row">
                        <span class="info-label">Postal Code:</span>
                        <span class="info-value"><?php echo htmlspecialchars($orderData['postal_code']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="info-section">
                    <h3>Delivery Information</h3>
                    <div class="info-row">
                        <span class="info-label">Estimated Delivery:</span>
                        <span class="info-value">
                            <?php 
                            // Estimate delivery date (3-5 business days from order date)
                            $orderDate = new DateTime($orderData['created_at']);
                            $minDelivery = clone $orderDate;
                            $maxDelivery = clone $orderDate;
                            $minDelivery->modify('+3 weekdays');
                            $maxDelivery->modify('+5 weekdays');
                            
                            echo date('F j', $minDelivery->getTimestamp()) . ' - ' . date('F j, Y', $maxDelivery->getTimestamp());
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Shipping Method:</span>
                        <span class="info-value">Standard Shipping</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="order-items">
            <h2>Order Items</h2>
            
            <div class="item-list">
                <?php foreach ($orderProducts as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="<?php echo !empty($item['image_path']) ? $item['image_path'] : 'images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-price">Rs. <?php echo number_format($item['price'], 2); ?></div>
                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div class="item-total">
                        Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Rs. <?php echo number_format($orderData['shipping_fee'], 2); ?></span>
                </div>
                <?php if ($orderData['discount_amount'] > 0): ?>
                <div class="summary-row">
                    <span>Discount</span>
                    <span>- Rs. <?php echo number_format($orderData['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rs. <?php echo number_format($orderData['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="customer_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="#" class="btn btn-primary" onclick="window.print(); return false;">Print Order Details</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Fluffy Cozzy Kit is dedicated to providing loving homes for cats and high-quality products for cat owners.</p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@fluffycozzykit.com</p>
                <p>Phone: +94 123 456 789</p>
            </div>
            <div class="footer-section">
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for updates on new arrivals and special offers.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email">
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Fluffy Cozzy Kit. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for UI interactions -->
    <script>
        // Account dropdown toggle
        document.getElementById('accountLink').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('accountDropdown').classList.toggle('show');
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.matches('#accountLink') && !e.target.matches('#accountLink img')) {
                var dropdown = document.getElementById('accountDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>