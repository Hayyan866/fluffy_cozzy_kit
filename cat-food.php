<?php
// Start session for user authentication and cart functionality
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "cats_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? true : false;

// Handle cart update operations via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['productId'])) {
    $productId = (int)$_POST['productId'];
    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];
    
    // Ensure user is logged in
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Login required']);
        exit;
    }
    
    switch ($action) {
        case 'add':
            // Check if product already in cart
            $checkSql = "SELECT * FROM temp_cart WHERE user_id = ? AND product_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ii", $userId, $productId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult && $checkResult->num_rows > 0) {
                // Update quantity
                $updateSql = "UPDATE temp_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $userId, $productId);
                $updateResult = $updateStmt->execute();
                
                if ($updateResult) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
                }
            } else {
                // Get product details for new cart item
                $productSql = "SELECT * FROM products WHERE product_id = ?";
                $productStmt = $conn->prepare($productSql);
                $productStmt->bind_param("i", $productId);
                $productStmt->execute();
                $productResult = $productStmt->get_result();
                
                if ($productResult && $productResult->num_rows > 0) {
                    $product = $productResult->fetch_assoc();
                    
                    // Insert new cart item
                    $insertSql = "INSERT INTO temp_cart (user_id, product_id, quantity, price, name, image) VALUES (?, ?, 1, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bind_param("iidss", $userId, $productId, $product['price'], $product['name'], $product['image_path']);
                    $insertResult = $insertStmt->execute();
                    
                    if ($insertResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add to cart: ' . $conn->error]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                }
            }
            break;
            
        case 'increase':
            // Increase quantity by 1
            $updateSql = "UPDATE temp_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $userId, $productId);
            $updateResult = $updateStmt->execute();
            
            if ($updateResult) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
            }
            break;
            
        case 'decrease':
            // Get current quantity
            $checkSql = "SELECT quantity FROM temp_cart WHERE user_id = ? AND product_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ii", $userId, $productId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult && $checkResult->num_rows > 0) {
                $currentQty = $checkResult->fetch_assoc()['quantity'];
                
                if ($currentQty > 1) {
                    // Decrease quantity by 1
                    $updateSql = "UPDATE temp_cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("ii", $userId, $productId);
                    $updateResult = $updateStmt->execute();
                    
                    if ($updateResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
                    }
                } else {
                    // Remove item if quantity would be 0
                    $deleteSql = "DELETE FROM temp_cart WHERE user_id = ? AND product_id = ?";
                    $deleteStmt = $conn->prepare($deleteSql);
                    $deleteStmt->bind_param("ii", $userId, $productId);
                    $deleteResult = $deleteStmt->execute();
                    
                    if ($deleteResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . $conn->error]);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
            }
            break;
            
        case 'remove':
            // Remove item from cart
            $deleteSql = "DELETE FROM temp_cart WHERE user_id = ? AND product_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ii", $userId, $productId);
            $deleteResult = $deleteStmt->execute();
            
            if ($deleteResult) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . $conn->error]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}

// Get all cat food products
$sql = "SELECT p.*, pd.detail_key, pd.detail_value 
        FROM products p 
        LEFT JOIN product_details pd ON p.product_id = pd.product_id
        WHERE p.category_id = 3"; // Category ID 3 is for Cat Food as per your database

$result = $conn->query($sql);

// Store products in an array
$products = array();
$product_details = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        
        // Add product to products array if not already added
        if (!isset($products[$product_id])) {
            $products[$product_id] = $row;
        }
        
        // Add product details
        if (!empty($row['detail_key']) && !empty($row['detail_value'])) {
            if (!isset($product_details[$product_id])) {
                $product_details[$product_id] = array();
            }
            $product_details[$product_id][$row['detail_key']] = $row['detail_value'];
        }
    }
}

// Handle direct add to cart functionality (non-AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    // Get product details
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // If user is logged in, add to database cart
        if ($isLoggedIn) {
            $user_id = $_SESSION['user_id'];
            
            // Check if product already exists in cart
            $checkSql = "SELECT * FROM temp_cart WHERE user_id = ? AND product_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ii", $user_id, $product_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update quantity
                $updateSql = "UPDATE temp_cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("iii", $quantity, $user_id, $product_id);
                $updateStmt->execute();
            } else {
                // Insert new cart item
                $insertSql = "INSERT INTO temp_cart (user_id, product_id, quantity, price, name, image) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("iiidss", $user_id, $product_id, $quantity, $product['price'], $product['name'], $product['image_path']);
                $insertStmt->execute();
            }
        } else {
            // Add to session cart for non-logged in users
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = array(
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'image' => $product['image_path']
                );
            }
        }
        
        // Redirect to prevent form resubmission
        header("Location: cat-food.php?success=1");
        exit();
    }
}

// Get cart count and items
$cartCount = 0;
$cartItems = [];
$totalPrice = 0;

if ($isLoggedIn) {
    $user_id = $_SESSION['user_id'];
    
    // Get cart items with product details
    $cartSql = "SELECT * FROM temp_cart WHERE user_id = ?";
    $cartStmt = $conn->prepare($cartSql);
    $cartStmt->bind_param("i", $user_id);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    
    if ($cartResult && $cartResult->num_rows > 0) {
        while ($item = $cartResult->fetch_assoc()) {
            $cartItems[] = $item;
            $cartCount += $item['quantity'];
            $totalPrice += ($item['price'] * $item['quantity']);
        }
    }
} else if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartItems[] = $item;
        $cartCount += $item['quantity'];
        $totalPrice += ($item['price'] * $item['quantity']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fluffy Cozzy Kit - Cat Food</title>
    <link rel="stylesheet" href="cat-food.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">Your cart is empty</div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Rs. <?= number_format($item['price'], 2) ?></p>
                            <div class="quantity-controls">
                                <button class="quantity-btn decrease-btn" data-product-id="<?= $item['product_id'] ?>">-</button>
                                <span class="quantity-value"><?= $item['quantity'] ?></span>
                                <button class="quantity-btn increase-btn" data-product-id="<?= $item['product_id'] ?>">+</button>
                            </div>
                            <button class="remove-btn" data-product-id="<?= $item['product_id'] ?>">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-total" id="cartTotal">Total: Rs. <?= number_format($totalPrice, 2) ?></div>
        <div class="cart-panel-footer">
            <div class="cart-actions">
                <!-- <button class="cart-action-btn" onclick="window.location.href='cart.php'">View and Edit Cart</button> -->
                <button class="cart-action-btn checkout" onclick="window.location.href='checkout.php'">Go to Checkout</button>
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
                <li><a href="customer_dashboard.php">Home</a></li>
                <li><a href="adult-cats.php">Adult Cats</a></li>
                <li><a href="kittens.php">Kittens</a></li>
                <li><a href="cat-food.php" class="active">Cat Food</a></li>
                <li><a href="accessories.php">Accessories</a></li>
                <?php if ($isAdmin): ?>
                <li><a href="admin.php" id="adminLink">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <li class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search products...">
                    <img src="search.png" alt="Search Icon">
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

    <!-- Filter Section -->
    <section class="filters">
        <div>
            <label for="typeFilter">Filter by Type:</label>
            <select id="typeFilter">
                <option value="All">All</option>
                <option value="Dry">Dry</option>
                <option value="Wet">Wet</option>
                <option value="Treats">Treats</option>
            </select>
        </div>
        <div>
            <label for="priceFilter">Price:</label>
            <select id="priceFilter">
                <option value="All">All</option>
                <option value="1000-1500">1000-1500 Rs</option>
                <option value="1500-2000">1500-2000 Rs</option>
                <option value="2000-2500">2000+ Rs</option>
            </select>
        </div>
        <div>
            <label for="brandFilter">Brand:</label>
            <select id="brandFilter">
                <option value="All">All</option>
                <option value="Reflex">Reflex</option>
                <option value="Moggy">Moggy</option>
                <option value="Pawfect">Pawfect</option>
                <option value="Fluffy Food">Fluffy Food</option>
                <option value="Royal Canin">Royal Canin</option>
            </select>
        </div>
        <div>
            <label for="ageGroupFilter">Age Group:</label>
            <select id="ageGroupFilter">
                <option value="All">All</option>
                <option value="Kitten">Kitten</option>
                <option value="Adult">Adult</option>
                <option value="Senior">Senior</option>
            </select>
        </div>
    </section>

    <!-- Food Grid -->
    <section class="food">
        <h2>Premium Cat Food</h2>
        <?php if(isset($_GET['success'])): ?>
        <!-- <div class="notification active" id="cartNotification">Product added to cart successfully!</div> -->
        <?php endif; ?>
        <div class="food-grid" id="foodGrid">
            <?php
            // Display products
            if (!empty($products)) {
                foreach ($products as $product) {
                    // Get product details
                    $type = isset($product_details[$product['product_id']]['type']) ? $product_details[$product['product_id']]['type'] : 'N/A';
                    $brand = isset($product_details[$product['product_id']]['brand']) ? $product_details[$product['product_id']]['brand'] : 'N/A';
                    $age_group = isset($product_details[$product['product_id']]['age_group']) ? $product_details[$product['product_id']]['age_group'] : 'N/A';
                    ?>
                    <div class="food-item" 
                        data-type="<?= htmlspecialchars($type) ?>" 
                        data-price="<?= htmlspecialchars($product['price']) ?>" 
                        data-brand="<?= htmlspecialchars($brand) ?>" 
                        data-age="<?= htmlspecialchars($age_group) ?>">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        <p><strong>Rs. <?= number_format($product['price'], 2) ?></strong></p>
                        <p><strong>Type:</strong> <?= htmlspecialchars($type) ?> | <strong>Brand:</strong> <?= htmlspecialchars($brand) ?> | <strong>Age:</strong> <?= htmlspecialchars($age_group) ?></p>
                        <button class="shop-btn add-to-cart-btn" data-product-id="<?= $product['product_id'] ?>">Add to Cart</button>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-products">No products found</p>';
            }
            ?>
        </div>
    </section>

    <!-- Feeding Tips Section -->
    <section class="feeding-tips">
        <img src="cat-fd.png" alt="Feeding Tips">
        <div class="feeding-tips-text">
            <h2>Feeding Tips</h2>
            <p>Discover the best ways to feed your cat for optimal health and happiness!</p>
            <a href="cat-care.html" class="learn-more-btn">Learn More</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <h2>Why Choose Us?</h2>
        <div class="reasons-grid">
            <div class="reason">
                <img src="https://images.unsplash.com/photo-1680691406746-de04c6bea9a1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aGFwcHklMjBjYXR8ZW58MHx8MHx8fDA%3D" alt="Healthy Cat">
                <p>High-quality cat food from trusted brands.</p>
                <h4>Quality Assurance</h4>
            </div>
            <div class="reason">
                <img src="https://media.istockphoto.com/id/1854401602/photo/cute-bengal-cat-sitting-in-a-cardboard-box.jpg?s=612x612&w=0&k=20&c=Ow_bJr2mYOt6Uh6R1QoPknpuGgVijPCvh_pLJbBne98=" alt="Delivery Service">
                <p>Fast delivery and excellent customer support.</p>
                <h4>Great Service</h4>
            </div>
            <div class="reason">
                <img src="https://media.istockphoto.com/id/182515843/photo/cat-shopping.jpg?s=612x612&w=0&k=20&c=0rW_WxBILxofHyFNXcIEiLYqgflpYUpMxCXTq7GayTw=" alt="Rewards Badge">
                <p>Affordable prices with reward points on every purchase.</p>
                <h4>Best Value</h4>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 Fluffy Cozzy Kit. All rights reserved.</p>
    </footer>

    <script>
    $(document).ready(function() {
        // Cart Panel Toggle
        $('#openCart').click(function(e) {
            e.preventDefault();
            $('#cartPanel').addClass('open');
            $('#cartOverlay').addClass('active');
        });
        
        $('#closeCart, #cartOverlay').click(function() {
            $('#cartPanel').removeClass('open');
            $('#cartOverlay').removeClass('active');
        });
        
        // Account Dropdown Toggle
        const accountLink = document.getElementById('accountLink');
        const accountDropdown = document.getElementById('accountDropdown');
        
        accountLink.addEventListener('click', function(e) {
            e.preventDefault();
            accountDropdown.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!accountLink.contains(e.target) && !accountDropdown.contains(e.target)) {
                accountDropdown.classList.remove('active');
            }
        });
        
        // Notification auto-hide
        const notification = document.getElementById('cartNotification');
        if (notification && notification.classList.contains('active')) {
            setTimeout(function() {
                notification.classList.remove('active');
            }, 3000);
        }
        
        // Filter functionality
        const typeFilter = document.getElementById('typeFilter');
        const priceFilter = document.getElementById('priceFilter');
        const brandFilter = document.getElementById('brandFilter');
        const ageGroupFilter = document.getElementById('ageGroupFilter');
        const foodItems = document.querySelectorAll('.food-item');
        
        function filterProducts() {
            const typeValue = typeFilter.value;
            const priceValue = priceFilter.value;
            const brandValue = brandFilter.value;
            const ageValue = ageGroupFilter.value;
            
            foodItems.forEach(item => {
                const type = item.getAttribute('data-type');
                const price = parseFloat(item.getAttribute('data-price'));
                const brand = item.getAttribute('data-brand');
                const age = item.getAttribute('data-age');
                
                let typeMatch = typeValue === 'All' || type === typeValue;
                let priceMatch = priceValue === 'All';
                let brandMatch = brandValue === 'All' || brand === brandValue;
                let ageMatch = ageValue === 'All' || age === ageValue;
                
                // Price range filtering
                if (priceValue === '1000-1500') {
                    priceMatch = price >= 1000 && price <= 1500;
                } else if (priceValue === '1500-2000') {
                    priceMatch = price > 1500 && price <= 2000;
                } else if (priceValue === '2000-2500') {
                    priceMatch = price > 2000;
                }
                
                if (typeMatch && priceMatch && brandMatch && ageMatch) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        typeFilter.addEventListener('change', filterProducts);
        priceFilter.addEventListener('change', filterProducts);
        brandFilter.addEventListener('change', filterProducts);
        ageGroupFilter.addEventListener('change', filterProducts);
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            
            foodItems.forEach(item => {
                const title = item.querySelector('h3').innerText.toLowerCase();
                const description = item.querySelector('p').innerText.toLowerCase();
                
                if (title.includes(searchValue) || description.includes(searchValue)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Add to cart functionality with AJAX
        $('.add-to-cart-btn').click(function() {
            const productId = $(this).data('product-id');
            const $button = $(this);
            
            // Disable button and show loading state
            $button.prop('disabled', true).text('Adding...');
            
            $.ajax({
                url: 'cat-food.php',
                type: 'POST',
                data: {
                    action: 'add',
                    productId: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // // Show notification
                        // $('<div class="notification active">Product added to cart!</div>')
                        //     .appendTo('.food')
                        //     .delay(3000)
                        //     .fadeOut(300, function() { $(this).remove(); });
                            
                        // Reload the page to update cart
                        location.reload();
                    } else {
                        // Re-enable button and show error
                        $button.prop('disabled', false).text('Add to Cart');
                        alert(response.message || 'Error adding to cart');
                    }
                },
                error: function() {
                    // Re-enable button and show error
                    $button.prop('disabled', false).text('Add to Cart');
                    alert('Error adding to cart. Please try again.');
                }
            });
        });
        
        // Increase quantity in cart
        $('.increase-btn').click(function() {
            const productId = $(this).data('product-id');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            
            $.ajax({
                url: 'cat-food.php',
                type: 'POST',
                data: {
                    action: 'increase',
                    productId: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        // Re-enable button and show error
                        $button.prop('disabled', false);
                        alert(response.message || 'Error updating cart');
                    }
                },
                error: function() {
                    // Re-enable button and show error
                    $button.prop('disabled', false);
                    alert('Error updating cart. Please try again.');
                }
            });
        });
        
        // Decrease quantity in cart
        $('.decrease-btn').click(function() {
            const productId = $(this).data('product-id');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            
            $.ajax({
                url: 'cat-food.php',
                type: 'POST',
                data: {
                    action: 'decrease',
                    productId: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        // Re-enable button and show error
                        $button.prop('disabled', false);
                        alert(response.message || 'Error updating cart');
                    }
                },
                error: function() {
                    // Re-enable button and show error
                    $button.prop('disabled', false);
                    alert('Error updating cart. Please try again.');
                }
            });
        });
        
        // Remove from cart with improved error handling
        $('.remove-btn').click(function() {
            const productId = $(this).data('product-id');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true).text('Removing...');
            
            $.ajax({
                url: 'adult-cats.php',
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
                        // Re-enable button and show error
                        $button.prop('disabled', false).text('Remove');
                        alert(response.message || 'Error removing item');
                        console.error('Remove from cart error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Re-enable button and show error
                    $button.prop('disabled', false).text('Remove');
                    alert('Error removing item. Please try again.');
                    console.error('AJAX error:', status, error);
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>
