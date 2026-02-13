<?php
// Start session
session_start();

// Database connection parameters
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

// Set header to JSON
header('Content-Type: application/json');

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if action is set
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'signup':
            handleSignup();
            break;
        case 'login':
            handleLogin();
            break;
        case 'forgotPassword':
            handleForgotPassword();
            break;
        case 'updateProfile':
            handleUpdateProfile();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'checkSession':
            checkSession();
            break;
        case 'getUserOrders':
            getUserOrders();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
    }
} else {
    // No action specified, load the HTML page
    displayPage();
}

// Function to handle user registration
function handleSignup() {
    global $conn;
    
    // Get form data
    $firstName = sanitize($_POST['first-name']);
    $lastName = sanitize($_POST['last-name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password']; // Will be hashed
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists. Please use a different email or login.'
        ]);
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Sign-up successful! Please login with your new account.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error during registration: ' . $conn->error
        ]);
    }
    
    $stmt->close();
}

// Function to handle user login
function handleLogin() {
    global $conn;
    
    // Get form data
    $email = sanitize($_POST['login-email']);
    $password = $_POST['login-password'];
    
    // Check user credentials
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            // Don't send password back to the client
            unset($user['password']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email not found.'
        ]);
    }
    
    $stmt->close();
}

// Function to handle forgot password
function handleForgotPassword() {
    global $conn;
    
    // Get email
    $email = sanitize($_POST['forgot-email']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // In a real application, you would:
        // 1. Generate a unique reset token
        // 2. Store it in the database with expiration time
        // 3. Send an email with reset link
        
        // For demo purposes, we'll just return success
        echo json_encode([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive password reset instructions.'
        ]);
    } else {
        // To prevent email enumeration, return the same message
        echo json_encode([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive password reset instructions.'
        ]);
    }
    
    $stmt->close();
}

// Function to update user profile
function handleUpdateProfile() {
    global $conn;
    
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to update your profile.'
        ]);
        return;
    }
    
    // Get form data
    $userId = $_SESSION['user_id']; // Use session user ID for security
    $firstName = sanitize($_POST['edit-first-name']);
    $lastName = sanitize($_POST['edit-last-name']);
    $email = sanitize($_POST['edit-email']);
    
    // Check if email already exists for another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is already in use by another account. Please use a different email.'
        ]);
        return;
    }
    
    // Update user data
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $firstName, $lastName, $email, $userId);
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $email;
        
        // Return updated user data
        $user = [
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating profile: ' . $conn->error
        ]);
    }
    
    $stmt->close();
}

// Function to handle user logout
function handleLogout() {
    // Destroy session
    session_unset();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful!'
    ]);
}

// Function to check session status
function checkSession() {
    if (isset($_SESSION['user_id'])) {
        $user = [
            'user_id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'email' => $_SESSION['email']
        ];
        
        echo json_encode([
            'success' => true,
            'isLoggedIn' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'isLoggedIn' => false
        ]);
    }
}

// Function to get user orders
function getUserOrders() {
    global $conn;
    
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to view your orders.'
        ]);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user orders
    $stmt = $conn->prepare("SELECT order_id, total_amount, status, created_at, updated_at FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    
    $stmt->close();
}

// Function to display HTML page
function displayPage() {
    // Output HTML content
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fluffy Cozzy Kit - Account</title>
    <link rel="stylesheet" href="account.css">
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
        <div class="cart-items" id="cartItems"></div>
        <div class="cart-panel-footer">
            <div class="cart-total" id="cartTotal"></div>
            <div class="cart-actions">
                <button class="cart-action-btn" onclick="viewCart()">View and Edit Cart</button>
                <button class="cart-action-btn checkout" onclick="checkout()">Go to Checkout</button>
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
                <li><a href="index.html">Home</a></li>
                <li><a href="adult-cats.html">Adult Cats</a></li>
                <li><a href="kittens.html">Kittens</a></li>
                <li><a href="cat-food.html">Cat Food</a></li>
                <li><a href="accessories.html">Accessories</a></li>
                <li><a href="admin.html">Admin</a></li>
            </ul>
            <ul class="nav-right">
                <li class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search products...">
                    <img src="search.png" alt="Search Icon">
                </li>
                <li><button id="cartBtn" class="cart-btn">
                    <img src="cart.png" alt="Cart Icon">
                    <span id="cartCount" class="cart-count">0</span>
                </button></li>
                <li id="accountBtn" class="account-btn">
                    <img src="account.png" alt="Account Icon">
                </li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main class="account-container">
        <!-- Account Navigation Tabs -->
        <div class="account-tabs" id="accountTabs">
            <div class="tab active" data-tab="login-signup">Login/Sign Up</div>
            <div class="tab" data-tab="profile" style="display: none;">My Profile</div>
            <div class="tab" data-tab="orders" style="display: none;">My Orders</div>
        </div>

        <!-- Account Content Sections -->
        <div class="account-content">
            <!-- Login/Sign Up Section -->
            <div class="account-section active" id="login-signup-section">
                <div class="auth-container">
                    <div class="auth-tabs">
                        <div class="auth-tab active" data-auth="login">Login</div>
                        <div class="auth-tab" data-auth="signup">Sign Up</div>
                    </div>
                    
                    <!-- Login Form -->
                    <div class="auth-form active" id="loginForm">
                        <div class="form-group">
                            <label for="login-email">Email Address</label>
                            <input type="email" id="login-email" name="login-email" required>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="login-password" required>
                        </div>
                        <div class="form-group forgot-password">
                            <a href="#" id="forgotPasswordLink">Forgot Password?</a>
                        </div>
                        <button class="btn primary-btn" id="loginBtn">Login</button>
                    </div>
                    
                    <!-- Sign Up Form -->
                    <div class="auth-form" id="signupForm">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first-name" required>
                        </div>
                        <div class="form-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last-name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <button class="btn primary-btn" id="signupBtn">Sign Up</button>
                    </div>
                    
                    <!-- Forgot Password Form -->
                    <div class="auth-form" id="forgotPasswordForm">
                        <h3>Reset Your Password</h3>
                        <p>Enter your email address and we'll send you instructions to reset your password.</p>
                        <div class="form-group">
                            <label for="forgot-email">Email Address</label>
                            <input type="email" id="forgot-email" name="forgot-email" required>
                        </div>
                        <div class="form-actions">
                            <button class="btn secondary-btn" id="backToLoginBtn">Back to Login</button>
                            <button class="btn primary-btn" id="resetPasswordBtn">Reset Password</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div class="account-section" id="profile-section">
                <h2>My Profile</h2>
                <form id="profileForm">
                    <div class="form-group">
                        <label for="edit-first-name">First Name</label>
                        <input type="text" id="edit-first-name" name="edit-first-name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-last-name">Last Name</label>
                        <input type="text" id="edit-last-name" name="edit-last-name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email Address</label>
                        <input type="email" id="edit-email" name="edit-email" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn danger-btn" id="logoutBtn">Logout</button>
                        <button type="submit" class="btn primary-btn" id="saveProfileBtn">Save Changes</button>
                    </div>
                </form>
            </div>
            
            <!-- Orders Section -->
            <div class="account-section" id="orders-section">
                <h2>My Orders</h2>
                <div class="orders-list" id="ordersList">
                    <!-- Orders will be loaded here dynamically -->
                    <div class="no-orders-message" id="noOrdersMessage">You don't have any orders yet.</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification Toast -->
    <div class="toast" id="notificationToast">
        <div class="toast-content" id="toastContent"></div>
        <button class="toast-close" id="closeToast">×</button>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>About Us</h3>
                <p>Fluffy Cozzy Kit is your one-stop shop for all cat-related products and services. We're dedicated to keeping your feline friends happy and healthy.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="adult-cats.html">Adult Cats</a></li>
                    <li><a href="kittens.html">Kittens</a></li>
                    <li><a href="cat-food.html">Cat Food</a></li>
                    <li><a href="accessories.html">Accessories</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <p>Email: support@fluffycozzykit.com</p>
                <p>Phone: (123) 456-7890</p>
                <p>Address: 123 Cat Street, Purrington, CA 90210</p>
            </div>
            <div class="footer-column">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for updates and special offers!</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Enter your email">
                    <button>Subscribe</button>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Fluffy Cozzy Kit. All rights reserved.</p>
        </div>
    </footer>
    <script src="account.js"></script>

</body>
</html>
<?php
}
?>