<?php
// Start session
session_start();

// Database connection
$db_host = "localhost";
$db_user = "root"; // Change as needed
$db_pass = ""; // Change as needed
$db_name = "cats_db";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form submission
$login_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login-email'])) {
    $email = $_POST['login-email'];
    $password = $_POST['login-password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $login_error = "Please enter both email and password";
    } else {
        // Prepare SQL statement to prevent SQL injection
        // Added role to the SELECT statement to check if user is admin
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_regenerate_id();
                
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $email;
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = $user['role']; // Store the role in session
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    // Redirect admin to admin dashboard
                    header("Location: admin_dashboard.php");
                } else {
                    // Redirect regular users to account page
                    header("Location: customer_dashboard.php");
                }
                exit();
            } else {
                $login_error = "Invalid email or password";
            }
        } else {
            $login_error = "Invalid email or password";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fluffy Cozzy Kit - Login</title>
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
                <!-- <li><a href="index.html">Home</a></li>
                <li><a href="adult-cats.html">Adult Cats</a></li>
                <li><a href="kittens.html">Kittens</a></li>
                <li><a href="cat-food.html">Cat Food</a></li>
                <li><a href="accessories.html">Accessories</a></li>
                <li><a href="admin.html">Admin</a></li> -->
            </ul>
            <!-- <ul class="nav-right">
                <li class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search products...">
                    <img src="search.png" alt="Search Icon">
                </li>
                <li>
                    <a href="#" id="openCart" style="position: relative;">
                        <img src="cart.png" alt="Cart Logo">
                        <span id="cartCount" class="cart-count">0</span>
                    </a>
                </li> -->
                <!-- <li class="account-dropdown">
                    <a href="#" id="accountLink">
                        <img src="account.png" alt="Account Logo">
                    </a>
                    <div class="dropdown-menu" id="accountDropdown">
                        <a href="#" id="myProfileLink">My Profile</a>
                        <a href="#" id="editProfileLink">Edit Profile</a>
                        <button class="logout-btn" id="logoutBtn">Logout</button>
                    </div>
                </li> -->
            </ul>
        </div>
    </header>

    <!-- Login Section -->
    <section class="account">
        <h2>Account Login</h2>
        <div class="account-container">
            <!-- Login Form -->
            <div class="form-container" id="loginContainer">
                <h3>Login</h3>
                <?php if (!empty($login_error)): ?>
                    <div class="error-message"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form id="login-form" method="POST" action="login.php"> 
                    <label for="login-email">Email:</label>
                    <input type="email" id="login-email" name="login-email" required>
                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" name="login-password" required>
                    <button type="submit">Login</button>
                    <!-- <a href="forgot-password.php" class="forgot-password" id="forgotPasswordLink">Forgot Password?</a> -->
                    <a href="signup.php" class="toggle-link" id="showSignup">Don't have an account? Sign Up</a>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 Fluffy Cozzy Kit. All rights reserved.</p>
    </footer>

    <script src="cart.js"></script>
    <script>
        // Show/hide account dropdown
        document.getElementById('accountLink').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('accountDropdown').classList.toggle('show');
        });

        // Close the dropdown if clicked outside
        window.addEventListener('click', function(e) {
            if (!e.target.matches('#accountLink') && !e.target.matches('#accountDropdown') && !e.target.closest('#accountDropdown')) {
                var dropdown = document.getElementById('accountDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            // Send to logout script
            window.location.href = 'logout.php';
        });
    </script>
</body>
</html>