<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
$host = "localhost";
$username = "root";
$password = "";
$database = "cats_db";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;


// Get filter values
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'All';
$priceFilter = isset($_GET['price']) ? $_GET['price'] : 'All';
$searchInput = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT * FROM products WHERE category_id = 4"; // 4 is for accessories as per your db schema

// Apply search filter
if (!empty($searchInput)) {
    $query .= " AND (name LIKE '%" . mysqli_real_escape_string($conn, $searchInput) . "%' 
               OR description LIKE '%" . mysqli_real_escape_string($conn, $searchInput) . "%')";
}

// Apply type filter (using product_details table)
if ($typeFilter != 'All') {
    $query .= " AND product_id IN (
                SELECT product_id FROM product_details 
                WHERE detail_key = 'type' 
                AND detail_value = '" . mysqli_real_escape_string($conn, $typeFilter) . "')";
}

// Apply price filter
if ($priceFilter != 'All') {
    switch ($priceFilter) {
        case '100-1000':
            $query .= " AND price BETWEEN 100 AND 1000";
            break;
        case '1000-3000':
            $query .= " AND price BETWEEN 1000 AND 3000";
            break;
        case '3000-5000':
            $query .= " AND price > 3000";
            break;
    }
}

// Execute query
$result = mysqli_query($conn, $query);
$accessories = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get product details (type)
        $typeQuery = "SELECT detail_value FROM product_details WHERE product_id = {$row['product_id']} AND detail_key = 'type'";
        $typeResult = mysqli_query($conn, $typeQuery);
        $type = '';
        
        if ($typeResult && mysqli_num_rows($typeResult) > 0) {
            $typeRow = mysqli_fetch_assoc($typeResult);
            $type = $typeRow['detail_value'];
        }
        
        $row['type'] = $type;
        $accessories[] = $row;
    }
}

// Get cart count
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $cartQuery = "SELECT SUM(quantity) as total FROM temp_cart WHERE user_id = $userId";
    $cartResult = mysqli_query($conn, $cartQuery);
    
    if ($cartResult && mysqli_num_rows($cartResult) > 0) {
        $cartRow = mysqli_fetch_assoc($cartResult);
        $cartCount = $cartRow['total'] ? $cartRow['total'] : 0;
    }
}

// Get all accessory types for filter dropdown
$typeQuery = "SELECT DISTINCT detail_value FROM product_details 
              WHERE detail_key = 'type' 
              AND product_id IN (SELECT product_id FROM products WHERE category_id = 4)";
$typeResult = mysqli_query($conn, $typeQuery);
$types = [];

if ($typeResult && mysqli_num_rows($typeResult) > 0) {
    while ($typeRow = mysqli_fetch_assoc($typeResult)) {
        $types[] = $typeRow['detail_value'];
    }
}

// Get cart items if user is logged in
$cartItems = [];
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    $cartQuery = "SELECT p.product_id, p.name, p.price, p.image_path, c.quantity 
                 FROM temp_cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = $userId";
    $cartResult = mysqli_query($conn, $cartQuery);
    
    if ($cartResult && mysqli_num_rows($cartResult) > 0) {
        while ($row = mysqli_fetch_assoc($cartResult)) {
            $cartItems[] = $row;
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
    
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add':
            if (isset($_POST['productId'])) {
                $productId = (int)$_POST['productId'];
                
                // Check if product already in cart
                $checkSql = "SELECT * FROM temp_cart WHERE user_id = $userId AND product_id = $productId";
                $checkResult = mysqli_query($conn, $checkSql);
                
                if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                    // Update quantity
                    $updateSql = "UPDATE temp_cart SET quantity = quantity + 1 WHERE user_id = $userId AND product_id = $productId";
                    $updateResult = mysqli_query($conn, $updateSql);
                    
                    if ($updateResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . mysqli_error($conn)]);
                    }
                } else {
                    // Get product details for the cart
                    $prodSql = "SELECT name, price, image_path FROM products WHERE product_id = $productId";
                    $prodResult = mysqli_query($conn, $prodSql);
                    
                    if ($prodResult && mysqli_num_rows($prodResult) > 0) {
                        $prodDetails = mysqli_fetch_assoc($prodResult);
                        
                        // Insert new item
                        $insertSql = "INSERT INTO temp_cart (user_id, product_id, quantity, name, price, image) 
                                      VALUES ($userId, $productId, 1, '{$prodDetails['name']}', {$prodDetails['price']}, '{$prodDetails['image_path']}')";
                        $insertResult = mysqli_query($conn, $insertSql);
                        
                        if ($insertResult) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Failed to add to cart: ' . mysqli_error($conn)]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Product not found']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
            }
            exit;
            
        case 'update':
            if (isset($_POST['productId']) && isset($_POST['quantity'])) {
                $productId = (int)$_POST['productId'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    // Remove item
                    $deleteSql = "DELETE FROM temp_cart WHERE user_id = $userId AND product_id = $productId";
                    $deleteResult = mysqli_query($conn, $deleteSql);
                    
                    if ($deleteResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . mysqli_error($conn)]);
                    }
                } else {
                    // Update quantity
                    $updateSql = "UPDATE temp_cart SET quantity = $quantity WHERE user_id = $userId AND product_id = $productId";
                    $updateResult = mysqli_query($conn, $updateSql);
                    
                    if ($updateResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . mysqli_error($conn)]);
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
                $deleteResult = mysqli_query($conn, $deleteSql);
                
                if ($deleteResult) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . mysqli_error($conn)]);
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
    <title>Fluffy Cozzy Kit - Accessories</title>
    <link rel="stylesheet" href="acessories.css">
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
            <?php if (count($cartItems) === 0): ?>
                <p class="cart-empty">Your cart is empty.</p>
            <?php else: ?>
                <?php 
                $totalPrice = 0;
                foreach ($cartItems as $item): 
                    $itemTotal = $item['price'] * $item['quantity'];
                    $totalPrice += $itemTotal;
                ?>
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
                            <p>Total: <?= $itemTotal ?> Rs</p>
                        </div>
                        <button class="remove-btn" data-product-id="<?= $item['product_id'] ?>">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-panel-footer">
            <div class="cart-total" id="cartTotal">
                <?php if (count($cartItems) > 0): ?>
                    Subtotal: <?= $totalPrice ?> Rs
                <?php endif; ?>
            </div>
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
                <li><a href="cat-food.php">Cat Food</a></li>
                <li><a href="accessories.php" class="active">Accessories</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <li class="search-bar">
                    <form action="accessories.php" method="GET">
                        <input type="text" id="searchInput" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchInput); ?>">
                        <button type="submit" style="background: none; border: none; cursor: pointer;">
                            <img src="search.png" alt="Search Icon">
                        </button>
                    </form>
                </li>
                <li>
                    <a href="#" id="openCart" style="position: relative;">
                        <img src="cart.png" alt="Cart Logo">
                        <span id="cartCount" class="cart-count"><?php echo $cartCount; ?></span>
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
        <form action="accessories.php" method="GET">
            <?php if(!empty($searchInput)): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchInput); ?>">
            <?php endif; ?>
            
            <div>
                <label for="typeFilter">Filter by Type:</label>
                <select id="typeFilter" name="type" onchange="this.form.submit()">
                    <option value="All" <?php echo ($typeFilter == 'All') ? 'selected' : ''; ?>>All</option>
                    <?php foreach($types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($typeFilter == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="priceFilter">Price:</label>
                <select id="priceFilter" name="price" onchange="this.form.submit()">
                    <option value="All" <?php echo ($priceFilter == 'All') ? 'selected' : ''; ?>>All</option>
                    <option value="100-1000" <?php echo ($priceFilter == '100-1000') ? 'selected' : ''; ?>>100-1000 Rs</option>
                    <option value="1000-3000" <?php echo ($priceFilter == '1000-3000') ? 'selected' : ''; ?>>1000-3000 Rs</option>
                    <option value="3000-5000" <?php echo ($priceFilter == '3000-5000') ? 'selected' : ''; ?>>3000+ Rs</option>
                </select>
            </div>
        </form>
    </section>

    <!-- Accessories Grid -->
    <section class="accessories">
        <h2>Available Accessories</h2>
        <div class="accessories-grid" id="accessoriesGrid">
            <?php if(count($accessories) > 0): ?>
                <?php foreach($accessories as $item): ?>
                    <div class="accessory">
                            <img src="<?php echo $item['image_path'] ? $item['image_path'] : 'placeholder.png'; ?>" alt="<?php echo $item['name']; ?>">
                        
                        <h3><?php echo $item['name']; ?></h3>
                        <p>Price: <?php echo $item['price']; ?> Rs</p>
                        <button class="shop-btn add-to-cart-btn" data-product-id="<?php echo $item['product_id']; ?>">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-products">No accessories found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Accessory Usage Tips Section -->
    <section class="accessory-tips">
        <img src="ac-usage.png" alt="Accessory Usage">
        <div class="accessory-tips-text">
            <h2>Accessory Usage Tips</h2>
            <p>Learn how to use our cat accessories to keep your pet happy and comfortable!</p>
            <a href="acessories-tips.html" class="learn-more-btn">Learn More</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <h2>Why Choose Us?</h2>
        <div class="reasons-grid">
            <div class="reason">
                <img src="https://images.unsplash.com/photo-1680691406746-de04c6bea9a1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aGFwcHklMjBjYXR8ZW58MHx8MHx8fDA%3D" alt="Healthy Cat">
                <p>High-quality accessories from trusted suppliers.</p>
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
        
        // Toggle cart panel
        $('#openCart').click(function(e) {
            e.preventDefault();
            $('#cartPanel').addClass('open');
            $('#cartOverlay').addClass('active');
        });

        $('#closeCart, #cartOverlay').click(function() {
            $('#cartPanel').removeClass('open');
            $('#cartOverlay').removeClass('active');
        });

        // Add to cart functionality with improved error handling
        $('.add-to-cart-btn').click(function() {
            const productId = $(this).data('product-id');
            
            <?php if ($isLoggedIn): ?>
            // Show loading indicator or disable button
            $(this).prop('disabled', true).text('Adding...');
            const $button = $(this);
            
            $.ajax({
                url: 'accessories.php',
                type: 'POST',
                data: {
                    action: 'add',
                    productId: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Success notification
                        alert('Item added to cart!');
                        // Reload the page to update cart count
                        location.reload();
                    } else {
                        // Re-enable button and show error
                        $button.prop('disabled', false).text('Add to Cart');
                        alert(response.message || 'Error adding to cart');
                        console.error('Add to cart error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Re-enable button and show error
                    $button.prop('disabled', false).text('Add to Cart');
                    alert('Error adding to cart. Please try again.');
                    console.error('AJAX error:', status, error);
                }
            });
            <?php else: ?>
            // Redirect to login if not logged in
            window.location.href = 'login.php';
            <?php endif; ?>
        });

        // Update quantity buttons with improved error handling
        $('.quantity-btn').click(function() {
            const productId = $(this).data('product-id');
            const quantity = $(this).data('quantity');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            
            $.ajax({
                url: 'accessories.php',
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
                        // Re-enable button and show error
                        $button.prop('disabled', false);
                        alert(response.message || 'Error updating cart');
                        console.error('Update cart error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Re-enable button and show error
                    $button.prop('disabled', false);
                    alert('Error updating cart. Please try again.');
                    console.error('AJAX error:', status, error);
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
                url: 'accessories.php',
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