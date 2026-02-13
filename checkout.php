<?php
// Start session if not already started
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: login.php?redirect=checkout.php");
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

// Check if cart is empty
$cartQuery = "SELECT COUNT(*) as count FROM temp_cart WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $cartQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Redirect to cart page if cart is empty
    header("Location: cart.php?error=empty");
    exit();
}

// Get user information for pre-filling the form
$userQuery = "SELECT first_name, last_name, email FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$userInfo = mysqli_fetch_assoc($userResult);

// Process checkout form submission
$errors = [];
$success = false;
$orderId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postalCode = trim($_POST['postal_code']);
    $phone = trim($_POST['phone']);
    $paymentMethod = $_POST['payment_method'];
    
    // Simple validation
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (!in_array($paymentMethod, ['card', 'cod'])) $errors[] = "Valid payment method is required";
    
    // If no errors, process the order
    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Get cart items and calculate total
            $cartItemsQuery = "SELECT * FROM temp_cart WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $cartItemsQuery);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $cartItems = mysqli_stmt_get_result($stmt);
            
            $totalAmount = 0;
            $items = [];
            
            while ($item = mysqli_fetch_assoc($cartItems)) {
                $totalAmount += ($item['price'] * $item['quantity']);
                $items[] = $item;
            }
            
            // Add shipping fee
            $shippingFee = 250.00;
            $finalTotal = $totalAmount + $shippingFee;
            
            // Create order in database
            $createOrderQuery = "INSERT INTO orders (user_id, email, first_name, last_name, address, city, postal_code, phone, payment_method, shipping_fee, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = mysqli_prepare($conn, $createOrderQuery);
            mysqli_stmt_bind_param($stmt, "isssssssddd", $userId, $email, $firstName, $lastName, $address, $city, $postalCode, $phone, $paymentMethod, $shippingFee, $finalTotal);
            mysqli_stmt_execute($stmt);
            
            $orderId = mysqli_insert_id($conn);
            
            // Insert order items
            foreach ($items as $item) {
                $insertItemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insertItemQuery);
                mysqli_stmt_bind_param($stmt, "isid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
                mysqli_stmt_execute($stmt);
            }
            
            // Clear the cart
            $clearCartQuery = "DELETE FROM temp_cart WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $clearCartQuery);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Set success flag
            $success = true;
            
            // Store order ID in session for the order confirmation page
            $_SESSION['last_order_id'] = $orderId;
            
            // No immediate redirect, let the page display the success message
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $errors[] = "Order processing failed: " . $e->getMessage();
        }
    }
}

// Get cart items for display
$cartQuery = "SELECT tc.*, p.name, p.image_path 
              FROM temp_cart tc 
              LEFT JOIN products p ON tc.product_id = p.product_id 
              WHERE tc.user_id = ?";
$stmt = mysqli_prepare($conn, $cartQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$cartItems = mysqli_stmt_get_result($stmt);

// Calculate cart totals
$subtotal = 0;
$cartProducts = [];

while ($item = mysqli_fetch_assoc($cartItems)) {
    $subtotal += ($item['price'] * $item['quantity']);
    $cartProducts[] = $item;
}

$shippingFee = 250.00; // Fixed shipping fee
$total = $subtotal + $shippingFee;

// Reset result pointer for displaying cart items in the page
mysqli_data_seek($cartItems, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fluffy Cozzy Kit</title>
    <!-- Fix CSS file references with absolute paths -->
    <link rel="stylesheet" href="/css/kitten.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/checkout.css">
    
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
        
        /* Checkout Styles */
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .checkout-container h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        /* Form Styles */
        .checkout-form {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .checkout-form h2 {
            color: #4CAF50;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        /* Payment Methods */
        .payment-methods {
            margin: 1.5rem 0;
        }
        
        .payment-method {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        /* Card Payment Fields */
        #card_payment_fields {
            border-top: 1px solid #eee;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .return-to-cart {
            padding: 10px 20px;
            background-color: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .return-to-cart:hover {
            background-color: #e0e0e0;
        }
        
        .place-order-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .place-order-btn:hover {
            background-color: #45a049;
        }
        
        /* Order Summary */
        .order-summary {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .order-summary h2 {
            color: #4CAF50;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        
        .cart-items-summary {
            margin-bottom: 2rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .cart-item-summary {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .item-image {
            position: relative;
            margin-right: 1rem;
        }
        
        .item-image img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .item-quantity {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-details h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
        }
        
        .item-total {
            font-weight: bold;
        }
        
        /* Summary Totals */
        .summary-totals {
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
        
        /* Error Message */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        
        .error-message ul {
            margin-left: 20px;
        }
        
        /* Success Message Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 60%;
            max-width: 500px;
            text-align: center;
            animation: modalFadeIn 0.4s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-title {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .modal-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .modal-button:hover {
            background-color: #45a049;
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
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .nav-center {
                display: none;
            }
            
            .navbar {
                padding: 1rem;
                justify-content: space-between;
            }
            
            .logo img {
                height: 40px;
            }
            
            .modal-content {
                width: 90%;
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

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <h2>Order Placed Successfully!</h2>
            </div>
            <div class="modal-body">
                <p>Your order has been placed successfully.</p>
                <p>Order ID: <strong><?php echo $orderId; ?></strong></p>
                <p>Thank you for shopping with Fluffy Cozzy Kit!</p>
            </div>
            <div class="modal-footer">
                <button class="modal-button" onclick="window.location.href='order_confirmation.php?order_id=<?php echo $orderId; ?>'">View Order Details</button>
            </div>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="checkout-container">
        <h1>Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <!-- Customer Information Form -->
            <div class="checkout-form">
                <h2>Shipping Information</h2>
                <form method="post" action="checkout.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo isset($userInfo['first_name']) ? htmlspecialchars($userInfo['first_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo isset($userInfo['last_name']) ? htmlspecialchars($userInfo['last_name']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($userInfo['email']) ? htmlspecialchars($userInfo['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="payment_card" name="payment_method" value="card" checked>
                            <label for="payment_card">Credit/Debit Card</label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="payment_cod" name="payment_method" value="cod">
                            <label for="payment_cod">Cash on Delivery</label>
                        </div>
                    </div>
                    
                    <!-- Card payment fields (shown only when card payment is selected) -->
                    <div id="card_payment_fields">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_name">Name on Card</label>
                            <input type="text" id="card_name" name="card_name">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="cart.php" class="return-to-cart">Return to Cart</a>
                        <button type="submit" class="place-order-btn">Place Order</button>
                    </div>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="cart-items-summary">
                    <?php foreach ($cartProducts as $item): ?>
                        <div class="cart-item-summary">
                            <div class="item-image">
                                <img src="<?php echo !empty($item['image']) ? $item['image'] : (!empty($item['image_path']) ? $item['image_path'] : 'images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-price">Rs. <?php echo number_format($item['price'], 2); ?></p>
                                </div>
                            <div class="item-total">
                                Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Rs. <?php echo number_format($shippingFee, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rs. <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
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
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><img src="facebook.png" alt="Facebook"></a>
                    <a href="#"><img src="instagram.png" alt="Instagram"></a>
                    <a href="#"><img src="twitter.png" alt="Twitter"></a>
                    <a href="#"><img src="youtube.png" alt="YouTube"></a>
                </div>
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

        // Payment method toggle
        document.getElementById('payment_card').addEventListener('change', function() {
            document.getElementById('card_payment_fields').style.display = 'block';
        });
        
        document.getElementById('payment_cod').addEventListener('change', function() {
            document.getElementById('card_payment_fields').style.display = 'none';
        });

        // Show success modal if order was placed successfully
        <?php if ($success): ?>
        window.onload = function() {
            document.getElementById('successModal').style.display = 'block';
        }
        <?php endif; ?>
    </script>
</body>
</html>