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

// Process signup form submission
$signup_error = "";
$signup_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $signup_error = "All fields are required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_error = "Invalid email format";
    } else if (strlen($password) < 8) {
        $signup_error = "Password must be at least 8 characters long";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $signup_error = "Email already exists. Please use a different email or login.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Start session and log user in
                session_regenerate_id();
                
                // Store user data in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;
                $_SESSION['logged_in'] = true;
                
                // Redirect to home page or display success message
                $signup_success = "Registration successful! Redirecting to home page...";
                header("Refresh: 2; URL=customer_dashboard.php");
            } else {
                $signup_error = "Registration failed. Please try again later.";
            }
            
            $insert_stmt->close();
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
    <title>Fluffy Cozzy Kit - Sign Up</title>
    <link rel="stylesheet" href="account.css">
    <style>
        .error-message {
            color: #e74c3c;
            background-color: #f9e7e7;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: #27ae60;
            background-color: #e7f9ed;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
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

    <!-- Signup Section -->
    <section class="account">
        <h2>Create an Account</h2>
        <div class="account-container">
            <!-- Sign Up Form -->
            <div class="form-container" id="signupContainer">
                <h3>Sign Up</h3>
                <?php if (!empty($signup_error)): ?>
                    <div class="error-message"><?php echo $signup_error; ?></div>
                <?php endif; ?>
                <?php if (!empty($signup_success)): ?>
                    <div class="success-message"><?php echo $signup_success; ?></div>
                <?php endif; ?>
                <form id="signup-form" method="POST" action="signup.php">
                    <label for="first-name">First Name:</label>
                    <input type="text" id="first-name" name="first-name" required value="<?php echo isset($_POST['first-name']) ? htmlspecialchars($_POST['first-name']) : ''; ?>">
                    <label for="last-name">Last Name:</label>
                    <input type="text" id="last-name" name="last-name" required value="<?php echo isset($_POST['last-name']) ? htmlspecialchars($_POST['last-name']) : ''; ?>">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <p class="password-hint">Password must be at least 8 characters long</p>
                    <button type="submit">Sign Up</button>
                    <a href="login.php" class="toggle-link" id="showLogin">Already have an account? Login</a>
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
        
        // Password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const hint = document.querySelector('.password-hint');
            
            if (password.length < 8) {
                hint.style.color = '#e74c3c';
            } else {
                hint.style.color = '#27ae60';
            }
        });
    </script>
</body>
</html>