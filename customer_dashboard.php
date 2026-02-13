<?php
// Start the session to manage user login state
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Change to your DB username
$password = ""; // Change to your DB password
$dbname = "cats_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = false;
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// Check if user is admin
if ($isLoggedIn) {
    $userEmail = $_SESSION['email'];
    $adminQuery = "SELECT * FROM users WHERE email = '$userEmail' AND role = 'admin'";
    $adminResult = $conn->query($adminQuery);
    if ($adminResult && $adminResult->num_rows > 0) {
        $isAdmin = true;
    }
}

// Get user information
$userInfo = [];
if ($isLoggedIn) {
    $userQuery = "SELECT * FROM users WHERE user_id = $userId";
    $userResult = $conn->query($userQuery);
    if ($userResult && $userResult->num_rows > 0) {
        $userInfo = $userResult->fetch_assoc();
    }
}

// Check if edit profile action is requested
$editProfile = isset($_GET['action']) && $_GET['action'] === 'edit';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Update user information
    $updateQuery = "UPDATE users SET 
                    first_name = '$firstName', 
                    last_name = '$lastName', 
                    email = '$email'
                    WHERE user_id = $userId";
    
    if ($conn->query($updateQuery)) {
        // Update session information
        $_SESSION['email'] = $email;
        // Redirect to remove the 'edit' parameter
        header("Location: customer_dashboard.php?update=success");
        exit();
    } else {
        $updateError = "Error updating profile: " . $conn->error;
    }
}

// Get user's order count
$orderCountQuery = "SELECT COUNT(*) as total FROM orders WHERE user_id = $userId";
$orderCountResult = $conn->query($orderCountQuery);
$orderCount = ($orderCountResult && $orderCountResult->num_rows > 0) ? 
              $orderCountResult->fetch_assoc()['total'] : 0;

// Get user's cat food subscription count
$subscriptionCountQuery = "SELECT COUNT(*) as total FROM order_items oi 
                          JOIN orders o ON oi.order_id = o.order_id 
                          JOIN products p ON oi.product_id = p.product_id
                          WHERE o.user_id = $userId AND p.category_id = 3 
                          AND o.status = 'delivered'";
$subscriptionCountResult = $conn->query($subscriptionCountQuery);
$subscriptionCount = ($subscriptionCountResult && $subscriptionCountResult->num_rows > 0) ? 
                    $subscriptionCountResult->fetch_assoc()['total'] : 0;

// For demo purposes, setting loyalty points
$loyaltyPoints = 250;

// Get recent orders
$recentOrdersQuery = "SELECT o.order_id, o.created_at, o.total_amount, o.status 
                     FROM orders o
                     WHERE o.user_id = $userId
                     ORDER BY o.created_at DESC
                     LIMIT 5";
$recentOrdersResult = $conn->query($recentOrdersQuery);

// Get recommended products (cats)
$recommendedQuery = "SELECT p.*, pd.detail_value as breed 
                    FROM products p 
                    JOIN product_details pd ON p.product_id = pd.product_id 
                    WHERE pd.detail_key = 'breed' AND (p.category_id = 1 OR p.category_id = 2)
                    ORDER BY RAND()
                    LIMIT 4";
$recommendedResult = $conn->query($recommendedQuery);

// Get cart items if user is logged in
$cartItems = [];
$cartCount = 0;
if ($isLoggedIn) {
    $cartQuery = "SELECT p.product_id, p.name, p.price, p.image_path, c.quantity 
                 FROM temp_cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = $userId";
    $cartResult = $conn->query($cartQuery);
    
    if ($cartResult && $cartResult->num_rows > 0) {
        while ($row = $cartResult->fetch_assoc()) {
            $cartItems[] = $row;
            $cartCount += $row['quantity'];
        }
    }
}

// Handle cart actions via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure user is logged in
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Login required']);
        exit;
    }
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update':
            if (isset($_POST['productId']) && isset($_POST['quantity'])) {
                $productId = (int)$_POST['productId'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    // Remove item
                    $deleteSql = "DELETE FROM temp_cart WHERE user_id = $userId AND product_id = $productId";
                    $deleteResult = $conn->query($deleteSql);
                    
                    if ($deleteResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . $conn->error]);
                    }
                } else {
                    // Update quantity
                    $updateSql = "UPDATE temp_cart SET quantity = $quantity WHERE user_id = $userId AND product_id = $productId";
                    $updateResult = $conn->query($updateSql);
                    
                    if ($updateResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID or quantity not provided']);
            }
            exit;
            
        case 'remove':
            if (isset($_POST['productId'])) {
                $productId = (int)$_POST['productId'];
                $deleteSql = "DELETE FROM temp_cart WHERE user_id = $userId AND product_id = $productId";
                $deleteResult = $conn->query($deleteSql);
                
                if ($deleteResult) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . $conn->error]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
            }
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Fluffy Cozzy Kit</title>

    <link rel="stylesheet" href="navbar.css">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Dashboard specific styles */
        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .welcome-banner {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text h1 {
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .welcome-text p {
            color: #666;
            margin: 0;
        }

        .green-check {
            color: green;
            font-size: 24px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card h2 {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        
        .dashboard-card-content {
            display: flex;
            align-items: center;
        }
        
        .dashboard-card-icon {
            width: 40px;
            height: 40px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .dashboard-card-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .dashboard-card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #e9f5e9;
            color: #1e7e34;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background-color: #f9f9f9;
            color: #333;
            font-weight: 600;
        }
        
        .orders-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .action-button {
            padding: 5px 10px;
            border-radius: 4px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        
        .action-button.view {
            background-color: #007bff;
        }
        
        .action-button.track {
            background-color: #28a745;
        }
        
        .recommended-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .recommended-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .recommended-item:hover {
            transform: translateY(-5px);
        }
        
        .recommended-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .recommended-item-content {
            padding: 15px;
        }
        
        .recommended-item h3 {
            font-size: 16px;
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .recommended-item p {
            font-size: 14px;
            color: #666;
            margin: 0 0 10px 0;
        }
        
        .recommended-item .price {
            font-weight: bold;
            color: #28a745;
        }
        
        .profile-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .profile-field {
            margin-bottom: 20px;
        }
        
        .profile-field label {
            display: block;
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .profile-value {
            font-size: 16px;
            color: #333;
            padding: 8px 0;
        }
        
        .profile-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .profile-actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            margin-top: 20px;
        }
        
        .profile-button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .edit-button {
            background-color: #28a745;
            color: white;
        }
        
        .save-button {
            background-color: #007bff;
            color: white;
        }
        
        .cancel-button {
            background-color: #6c757d;
            color: white;
        }
        
        @media (max-width: 992px) {
            .dashboard-grid, .profile-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .recommended-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid, .profile-grid, .recommended-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
            }
            
            .green-check {
                margin-top: 10px;
            }
        }

        /* Cart panel styles copied from adult-cats.php */
        /* ... Keep the styles from adult-cats.php ... */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        .cart-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100%;
            background-color: white;
            z-index: 1000;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .cart-panel.open {
            right: 0;
        }

        .cart-panel-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-panel-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .cart-panel-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .cart-empty {
            text-align: center;
            color: #666;
            margin-top: 40px;
        }

        .cart-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .cart-item-details p {
            margin: 5px 0;
            color: #666;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .quantity-btn {
            width: 24px;
            height: 24px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 50%;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .quantity-controls span {
            margin: 0 10px;
            font-size: 14px;
        }

        .remove-btn {
            position: absolute;
            top: 0;
            right: 0;
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 12px;
        }

        .cart-panel-footer {
            padding: 20px;
            border-top: 1px solid #eee;
        }

        .cart-total {
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .cart-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cart-action-btn {
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .cart-action-btn.checkout {
            background-color: #28a745;
            color: white;
        }

        /* Account dropdown styles */
        .account-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 150px;
            display: none;
            z-index: 100;
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
        }

        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }

        .logout-btn {
            width: 100%;
            padding: 10px 15px;
            background: none;
            border: none;
            text-align: left;
            color: #dc3545;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #f5f5f5;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #28a745;
            color: white;
            font-size: 12px;
            font-weight: bold;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Footer Styles */
footer {
    background-color: #222;
    color: #fff;
    padding: 50px 0 20px;
    margin-top: 50px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    display: flex;
    flex-direction: column;
}

.footer-logo {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}

.footer-logo img {
    width: 50px;
    height: 50px;
    margin-right: 10px;
}

.footer-logo-text {
    font-size: 18px;
    font-weight: bold;
    line-height: 1.2;
}

.footer-logo-text .green {
    color: #28a745;
}

.footer-logo-text .white {
    color: #fff;
}

.footer-links {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

.footer-section h3 {
    color: #fff;
    font-size: 18px;
    margin-bottom: 15px;
    position: relative;
    padding-bottom: 10px;
}

.footer-section h3:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background-color: #28a745;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section ul li a:hover {
    color: #28a745;
}

.social-media {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.social-media a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: #333;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.social-media a:hover {
    background-color: #28a745;
}

.social-media img {
    width: 20px;
    height: 20px;
}

.newsletter h4 {
    color: #fff;
    font-size: 16px;
    margin-bottom: 15px;
}

.newsletter form {
    display: flex;
}

.newsletter input {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 4px 0 0 4px;
    background-color: #333;
    color: #fff;
}

.newsletter button {
    padding: 10px 15px;
    border: none;
    background-color: #28a745;
    color: #fff;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.3s;
}

.newsletter button:hover {
    background-color: #218838;
}

.footer-bottom {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 15px 0;
    border-top: 1px solid #444;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-bottom p {
    margin: 0;
    color: #888;
    font-size: 14px;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
}

.footer-bottom-links a {
    color: #888;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s;
}

.footer-bottom-links a:hover {
    color: #28a745;
}

/* Responsive Styles for Footer */
@media (max-width: 992px) {
    .footer-links {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
    }
    
    .footer-logo {
        margin-bottom: 20px;
    }
    
    .footer-links {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .footer-bottom-links {
        justify-content: center;
    }
    
    .newsletter form {
        flex-direction: column;
    }
    
    .newsletter input {
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .newsletter button {
        border-radius: 4px;
    }
}
    </style>
</head>
<body>
    <!-- Overlay for background dimming -->
    <div class="overlay" id="cartOverlay"></div>

    <!-- Cart Side Panel -->
    <div class="cart-panel" id="cartPanel">
        <div class="cart-panel-header">
            <h2>My Cart</h2>
            <button class="cart-panel-close" id="closeCart">×</button>
        </div>
        <div class="cart-items" id="cartItems">
            <?php if (count($cartItems) === 0): ?>
                <p class="cart-empty">Your cart is empty.</p>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Price: <?= htmlspecialchars($item['price']) ?> Rs</p>
                            <div class="quantity-controls">
                                <button class="quantity-btn minus-btn" data-product-id="<?= $item['product_id'] ?>" data-quantity="<?= $item['quantity'] - 1 ?>">–</button>
                                <span>Qty: <?= htmlspecialchars($item['quantity']) ?></span>
                                <button class="quantity-btn plus-btn" data-product-id="<?= $item['product_id'] ?>" data-quantity="<?= $item['quantity'] + 1 ?>">+</button>
                            </div>
                            <p>Total: <?= $item['price'] * $item['quantity'] ?> Rs</p>
                        </div>
                        <button class="remove-btn" data-product-id="<?= $item['product_id'] ?>">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-panel-footer">
            <div class="cart-total" id="cartTotal">
                <?php if (count($cartItems) > 0): ?>
                    <?php
                    $totalPrice = 0;
                    foreach ($cartItems as $item) {
                        $totalPrice += $item['price'] * $item['quantity'];
                    }
                    ?>
                    Subtotal: <?= $totalPrice ?> Rs
                <?php endif; ?>
            </div>
            <div class="cart-actions">
                <button class="cart-action-btn" onclick="location.href='cart.php'">View and Edit Cart</button>
                <button class="cart-action-btn checkout" onclick="location.href='<?= $isLoggedIn ? 'checkout.php' : 'account.php' ?>'">Go to Checkout</button>
            </div>
        </div>
    </div>

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
                <li><a href="customer_dashboard.php" class="active">Home</a></li>
                <li><a href="adult-cats.php">Adult Cats</a></li>
                <li><a href="kittens.php">Kittens</a></li>
                <li><a href="cat-food.php">Cat Food</a></li>
                <li><a href="accessories.php">Accessories</a></li>
                <?php if ($isAdmin): ?>
                <li><a href="admin.php" id="adminLink">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <li class="search-bar">
                    <form action="adult-cats.php" method="GET" id="searchForm">
                        <input type="text" id="searchInput" name="search" placeholder="Search products...">
                        <button type="submit" style="background: none; border: none; cursor: pointer;">
                            <img src="search.png" alt="Search Icon">
                        </button>
                    </form>
                </li>
                <li>
                    <a href="#" id="openCart" style="position: relative;">
                        <img src="cart.png" alt="Cart Logo">
                        <span id="cartCount" class="cart-count"><?= $cartCount ?></span>
                    </a>
                </li>
                <li class="account-dropdown">
                    <a href="#" id="accountLink">
                        <img src="account.png" alt="Account Logo">
                    </a>
                    <div class="dropdown-menu" id="accountDropdown">
                        <?php if ($isLoggedIn): ?>
                            <a href="customer_dashboard.php" id="myProfileLink">My Profile</a>
                            <a href="customer_dashboard.php?action=edit" id="editProfileLink">Edit Profile</a>
                            <button class="logout-btn" id="logoutBtn" onclick="window.location.href='logout.php'">Logout</button>
                        <?php else: ?>
                            <a href="login.php" id="loginLink">Login</a>
                            <a href="register.php" id="registerLink">Register</a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome back, <?= htmlspecialchars($userInfo['first_name']) ?>!</h1>
                <p>View your order information and manage your Fluffy Cozzy Kit account.</p>
            </div>
            <div class="green-check">✓</div>
        </div>

        <!-- Dashboard Summary Cards -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Your Orders</h2>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">📦</div>
                    <div class="dashboard-card-value"><?= $orderCount ?></div>
                </div>
                <span class="dashboard-card-badge">Total Orders</span>
            </div>
            
            <div class="dashboard-card">
                <h2>Wishlist</h2>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">❤️</div>
                    <div class="dashboard-card-value">0</div>
                </div>
                <span class="dashboard-card-badge">Saved Items</span>
            </div>
            
            <div class="dashboard-card">
                <h2>Loyalty Points</h2>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">🌟</div>
                    <div class="dashboard-card-value"><?= $loyaltyPoints ?></div>
                </div>
                <span class="dashboard-card-badge">Available Points</span>
            </div>
            
            <div class="dashboard-card">
                <h2>Cat Food Subscriptions</h2>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-icon">🐱</div>
                    <div class="dashboard-card-value"><?= $subscriptionCount ?></div>
                </div>
                <span class="dashboard-card-badge">Active Subscriptions</span>
            </div>
        </div>

        <?php if ($editProfile): ?>
        <!-- Edit Profile Section -->
        <div class="profile-section">
            <h2>Edit Profile</h2>
            <form method="POST" action="customer_dashboard.php">
                <div class="profile-grid">
                    <div class="profile-field">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="profile-input" value="<?= htmlspecialchars($userInfo['first_name']) ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="profile-input" value="<?= htmlspecialchars($userInfo['last_name']) ?>" required>
                    </div>
                    
                    <div class="profile-field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="profile-input" value="<?= htmlspecialchars($userInfo['email']) ?>" required>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button type="submit" name="update_profile" class="profile-button save-button">Save Changes</button>
                    <a href="customer_dashboard.php" class="profile-button cancel-button">Cancel</a>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Profile Information Section -->
        <div class="profile-section">
            <h2>Account Information</h2>
            <div class="profile-grid">
                <div class="profile-field">
                    <label>First Name</label>
                    <div class="profile-value"><?= htmlspecialchars($userInfo['first_name']) ?></div>
                </div>
                
                <div class="profile-field">
                    <label>Last Name</label>
                    <div class="profile-value"><?= htmlspecialchars($userInfo['last_name']) ?></div>
                </div>
                
                <div class="profile-field">
                    <label>Email Address</label>
                    <div class="profile-value"><?= htmlspecialchars($userInfo['email']) ?></div>
                </div>
            </div>
            
            <div class="profile-actions">
                <a href="customer_dashboard.php?action=edit" class="profile-button edit-button">Edit Profile</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Orders Section -->
        <h2>Recent Orders</h2>
        <?php if ($recentOrdersResult && $recentOrdersResult->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td><?= htmlspecialchars($order['total_amount']) ?> Rs</td>
                            <td>
                                <span class="status-badge <?= 'status-' . strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order-details.php?id=<?= $order['order_id'] ?>" class="action-button view">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't placed any orders yet.</p>
        <?php endif; ?>

        <!-- Recommended Products -->
        <h2>Recommended For You</h2>
        <?php if ($recommendedResult && $recommendedResult->num_rows > 0): ?>
            <div class="recommended-grid">
                <?php while ($product = $recommendedResult->fetch_assoc()): ?>
                    <div class="recommended-item">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="recommended-item-content">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= htmlspecialchars($product['breed']) ?></p>
                            <p class="price"><?= htmlspecialchars($product['price']) ?> Rs</p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No recommended products available at this time.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="cat.png" alt="Logo">
                <div class="footer-logo-text">
                    <span class="green">Fluffy</span> 
                    <span class="green">cozzy</span><br>
                    <span class="white">kit</span>
                </div>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h3>Shop</h3>
                    <ul>
                        <li><a href="adult-cats.php">Adult Cats</a></li>
                        <li><a href="kittens.php">Kittens</a></li>
                        <li><a href="cat-food.php">Cat Food</a></li>
                        <li><a href="accessories.php">Accessories</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                        <li><a href="shipping.php">Shipping Information</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="careers.php">Careers</a></li>
                        <li><a href="press.php">Press</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <div class="newsletter">
                        <h4>Subscribe to our newsletter</h4>
                        <form>
                            <input type="email" placeholder="Enter your email">
                            <button type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Fluffy Cozzy Kit. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
                <a href="sitemap.php">Sitemap</a>
            </div>
        </div>
    </footer>

    <script>
        // Toggle account dropdown
        const accountLink = document.getElementById('accountLink');
        const accountDropdown = document.getElementById('accountDropdown');
        
        accountLink.addEventListener('click', function(e) {
            e.preventDefault();
            accountDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!accountLink.contains(e.target) && !accountDropdown.contains(e.target)) {
                accountDropdown.classList.remove('active');
            }
        });
        
        // Cart panel functionality
        const openCartBtn = document.getElementById('openCart');
        const closeCartBtn = document.getElementById('closeCart');
        const cartPanel = document.getElementById('cartPanel');
        const cartOverlay = document.getElementById('cartOverlay');
        
        openCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cartPanel.classList.add('open');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        function closeCart() {
            cartPanel.classList.remove('open');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        closeCartBtn.addEventListener('click', closeCart);
        cartOverlay.addEventListener('click', closeCart);
        
        // Handle cart quantity updates
        const minusBtns = document.querySelectorAll('.minus-btn');
        const plusBtns = document.querySelectorAll('.plus-btn');
        const removeBtns = document.querySelectorAll('.remove-btn');
        
        minusBtns.forEach(btn => {
            btn.addEventListener('click', updateQuantity);
        });
        
        plusBtns.forEach(btn => {
            btn.addEventListener('click', updateQuantity);
        });
        
        removeBtns.forEach(btn => {
            btn.addEventListener('click', removeItem);
        });
        
        function updateQuantity(e) {
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(this.getAttribute('data-quantity'));
            
            $.ajax({
                url: 'customer_dashboard.php',
                type: 'POST',
                data: {
                    action: 'update',
                    productId: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the cart.');
                }
            });
        }
        
        function removeItem(e) {
            const productId = this.getAttribute('data-product-id');
            
            $.ajax({
                url: 'customer_dashboard.php',
                type: 'POST',
                data: {
                    action: 'remove',
                    productId: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while removing the item from cart.');
                }
            });
        }
        
        // Show success message if profile was updated
        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
        alert('Your profile has been updated successfully!');
        <?php endif; ?>
    </script>
</body>
</html>