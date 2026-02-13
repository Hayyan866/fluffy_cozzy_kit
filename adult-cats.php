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

// Check if user is admin
if ($isLoggedIn) {
    $userEmail = $_SESSION['email'];
    $adminQuery = "SELECT * FROM users WHERE email = '$userEmail' AND role = 'admin'";
    $adminResult = $conn->query($adminQuery);
    if ($adminResult && $adminResult->num_rows > 0) {
        $isAdmin = true;
    }
}

// Get filters from URL parameters
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$breedFilter = isset($_GET['breed']) ? $conn->real_escape_string($_GET['breed']) : 'All';
$ageFilter = isset($_GET['age']) ? $conn->real_escape_string($_GET['age']) : 'All';
$priceFilter = isset($_GET['price']) ? $conn->real_escape_string($_GET['price']) : 'All';

// Build the query
$sql = "SELECT p.*, pd1.detail_value as breed, pd2.detail_value as age 
        FROM products p 
        JOIN product_details pd1 ON p.product_id = pd1.product_id AND pd1.detail_key = 'breed'
        JOIN product_details pd2 ON p.product_id = pd2.product_id AND pd2.detail_key = 'age'
        WHERE p.category_id = 1"; // Adult Cats category

// Apply search filter
if (!empty($searchTerm)) {
    $sql .= " AND (p.name LIKE '%$searchTerm%' OR pd1.detail_value LIKE '%$searchTerm%')";
}

// Apply breed filter
if ($breedFilter != 'All') {
    $sql .= " AND pd1.detail_value = '$breedFilter'";
}

// Apply age filter
if ($ageFilter != 'All') {
    list($minAge, $maxAge) = explode('-', $ageFilter);
    $sql .= " AND CAST(pd2.detail_value AS DECIMAL) BETWEEN $minAge AND $maxAge";
}

// Apply price filter
if ($priceFilter != 'All') {
    list($minPrice, $maxPrice) = explode('-', $priceFilter);
    $sql .= " AND p.price BETWEEN $minPrice AND $maxPrice";
}

$result = $conn->query($sql);

// Get all distinct breeds for filter dropdown
$breedQuery = "SELECT DISTINCT detail_value FROM product_details 
              WHERE detail_key = 'breed' AND product_id IN 
              (SELECT product_id FROM products WHERE category_id = 1)";
$breedResult = $conn->query($breedQuery);
$breeds = [];
if ($breedResult && $breedResult->num_rows > 0) {
    while ($row = $breedResult->fetch_assoc()) {
        $breeds[] = $row['detail_value'];
    }
}

// Get cart items if user is logged in
$cartItems = [];
$cartCount = 0;
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // FIXED: Use a consistent table name (temp_cart)
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
    
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'];
    
    // FIXED: Added error handling and consistent table name (temp_cart)
    switch ($action) {
        case 'add':
            if (isset($_POST['productId'])) {
                $productId = (int)$_POST['productId'];
                
                // Check if product already in cart
                $checkSql = "SELECT * FROM temp_cart WHERE user_id = $userId AND product_id = $productId";
                $checkResult = $conn->query($checkSql);
                
                if ($checkResult && $checkResult->num_rows > 0) {
                    // Update quantity
                    $updateSql = "UPDATE temp_cart SET quantity = quantity + 1 WHERE user_id = $userId AND product_id = $productId";
                    $updateResult = $conn->query($updateSql);
                    
                    if ($updateResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
                    }
                } else {
                    // Insert new item
                    $insertSql = "INSERT INTO temp_cart (user_id, product_id, quantity) VALUES ($userId, $productId, 1)";
                    $insertResult = $conn->query($insertSql);
                    
                    if ($insertResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add to cart: ' . $conn->error]);
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
    <title>Fluffy Cozzy Kit - Adult Cats</title>
    <link rel="stylesheet" href="adult-cats.css">
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
                <!-- <button class="cart-action-btn" onclick="location.href='cart.php'">View and Edit Cart</button> -->
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
                <li><a href="customer_dashboard.php">Home</a></li>
                <li><a href="adult-cats.php" class="active">Adult Cats</a></li>
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
                        <input type="text" id="searchInput" name="search" placeholder="Search products..." value="<?= htmlspecialchars($searchTerm) ?>">
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

    <!-- Filter Section -->
    <section class="filters">
        <form action="adult-cats.php" method="GET" id="filterForm">
            <div>
                <label for="breedFilter">Filter by Breed:</label>
                <select id="breedFilter" name="breed" onchange="this.form.submit()">
                    <option value="All" <?= $breedFilter === 'All' ? 'selected' : '' ?>>All</option>
                    <?php foreach ($breeds as $breed): ?>
                        <option value="<?= htmlspecialchars($breed) ?>" <?= $breedFilter === $breed ? 'selected' : '' ?>>
                            <?= htmlspecialchars($breed) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="ageFilter">Age:</label>
                <select id="ageFilter" name="age" onchange="this.form.submit()">
                    <option value="All" <?= $ageFilter === 'All' ? 'selected' : '' ?>>All</option>
                    <option value="1-3" <?= $ageFilter === '1-3' ? 'selected' : '' ?>>1-3 years</option>
                    <option value="3-5" <?= $ageFilter === '3-5' ? 'selected' : '' ?>>3-5 years</option>
                </select>
            </div>
            <div>
                <label for="priceFilter">Price:</label>
                <select id="priceFilter" name="price" onchange="this.form.submit()">
                    <option value="All" <?= $priceFilter === 'All' ? 'selected' : '' ?>>All</option>
                    <option value="5000-10000" <?= $priceFilter === '5000-10000' ? 'selected' : '' ?>>5000-10000 Rs</option>
                    <option value="10000-20000" <?= $priceFilter === '10000-20000' ? 'selected' : '' ?>>10000-20000 Rs</option>
                </select>
            </div>
            <?php if (!empty($searchTerm)): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
            <?php endif; ?>
        </form>
    </section>

    <!-- Adult Cats Grid -->
    <section class="cats">
        <h2>Available Adult Cats</h2>
        <div class="cats-grid" id="catsGrid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($cat = $result->fetch_assoc()) {
                    $productId = $cat['product_id'];
                    $catName = htmlspecialchars($cat['name']);
                    $catBreed = htmlspecialchars($cat['breed']);
                    $catAge = htmlspecialchars($cat['age']);
                    $catPrice = htmlspecialchars($cat['price']);
                    $catImage = htmlspecialchars($cat['image_path']);
                    
                    // Get description if available
                    $descriptionQuery = "SELECT detail_value FROM product_details 
                                       WHERE product_id = $productId AND detail_key = 'description'";
                    $descResult = $conn->query($descriptionQuery);
                    $description = ($descResult && $descResult->num_rows > 0) ? 
                                  $descResult->fetch_assoc()['detail_value'] : '';
                    ?>
                    <div class="cat">
                       
                            <img src="<?= $catImage ?>" alt="<?= $catName ?>">
                        </a>
                        <h3><?= $catName ?></h3>
                        <p>Age: <?= $catAge ?> years | Adoption Fee: <?= $catPrice ?> Rs</p>
                        <button class="shop-btn add-to-cart-btn" data-product-id="<?= $productId ?>">Add to Cart</button>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-results">No cats found matching your criteria.</p>';
            }
            ?>
        </div>
    </section>

    <!-- Cat Care Tips Section -->
    <section class="cat-care">
        <img src="ad_care.png" alt="Cat Care">
        <div class="cat-care-text">
            <h2>Cat Care Tips</h2>
            <p>Learn how to care for your new adult cat with our expert tips!</p>
            <a href="cat-care.html" class="learn-more-btn">Learn More</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <h2>Why Choose Us?</h2>
        <div class="reasons-grid">
            <div class="reason">
                <img src="https://images.unsplash.com/photo-1680691406746-de04c6bea9a1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aGFwcHklMjBjYXR8ZW58MHx8MHx8fDA%3D" alt="Healthy Cat">
                <p>Healthy and well-socialized cats from trusted breeders.</p>
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
        // Toggle account dropdown
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

        // Add to cart functionality - IMPROVED ERROR HANDLING
        $('.add-to-cart-btn').click(function() {
            const productId = $(this).data('product-id');
            
            <?php if ($isLoggedIn): ?>
            // Show loading indicator or disable button
            $(this).prop('disabled', true).text('Adding...');
            const $button = $(this);
            
            $.ajax({
                url: 'adult-cats.php',
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

        // Update quantity with improved error handling
        $('.quantity-btn').click(function() {
            const productId = $(this).data('product-id');
            const quantity = $(this).data('quantity');
            const $button = $(this);
            
            // Disable button to prevent multiple clicks
            $button.prop('disabled', true);
            
            $.ajax({
                url: 'adult-cats.php',
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