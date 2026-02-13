<?php
// Start session if not already started
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
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

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Check if user is admin
$isAdmin = false;
if ($isLoggedIn) {
    $userEmail = $_SESSION['email'];
    $adminQuery = "SELECT * FROM users WHERE email = '$userEmail' AND role = 'admin'";
    $adminResult = mysqli_query($conn, $adminQuery);
    if ($adminResult && mysqli_num_rows($adminResult) > 0) {
        $isAdmin = true;
    }
}

// Fetch all kitten products from database
$query = "SELECT p.*, pd.detail_key, pd.detail_value 
          FROM products p 
          LEFT JOIN product_details pd ON p.product_id = pd.product_id
          WHERE p.category_id = 2"; // Category ID 2 is 'Kittens' as per your database

$result = mysqli_query($conn, $query);

// Create an array to store products with their details
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $productId = $row['product_id'];
    
    if (!isset($products[$productId])) {
        $products[$productId] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'image_path' => $row['image_path'],
            'details' => []
        ];
    }
    
    // Add details if available
    if (!empty($row['detail_key'])) {
        $products[$productId]['details'][$row['detail_key']] = $row['detail_value'];
    }
}

// Convert to indexed array for easier access in JavaScript
$products = array_values($products);

// Get all distinct breeds for filter dropdown
$breedQuery = "SELECT DISTINCT detail_value FROM product_details 
              WHERE detail_key = 'breed' AND product_id IN 
              (SELECT product_id FROM products WHERE category_id = 2)";
$breedResult = mysqli_query($conn, $breedQuery);
$breeds = [];
if ($breedResult && mysqli_num_rows($breedResult) > 0) {
    while ($row = mysqli_fetch_assoc($breedResult)) {
        $breeds[] = $row['detail_value'];
    }
}

// Get cart items if user is logged in
$cartItems = [];
$cartCount = 0;
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
    
    switch ($action) {
        case 'add':
            if (isset($_POST['productId'])) {
                $productId = (int)$_POST['productId'];
                
                // Get product info for cart
                $productQuery = "SELECT name, price, image_path FROM products WHERE product_id = $productId";
                $productResult = mysqli_query($conn, $productQuery);
                $productInfo = mysqli_fetch_assoc($productResult);
                
                if (!$productInfo) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    exit;
                }
                
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
                    // Insert new item
                    $insertSql = "INSERT INTO temp_cart (user_id, product_id, quantity, price, name, image) 
                                 VALUES ($userId, $productId, 1, {$productInfo['price']}, '{$productInfo['name']}', '{$productInfo['image_path']}')";
                    $insertResult = mysqli_query($conn, $insertSql);
                    
                    if ($insertResult) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add to cart: ' . mysqli_error($conn)]);
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
    <title>Fluffy Cozzy Kit - Kittens</title>
    <link rel="stylesheet" href="kitten.css">
    <<link rel="stylesheet" href="navbar.css">
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
                <button class="cart-action-btn checkout" onclick="location.href='checkout.php'">Go to Checkout</button>
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
                <li><a href="kittens.php" class="active">Kittens</a></li>
                <li><a href="cat-food.php">Cat Food</a></li>
                <li><a href="accessories.php">Accessories</a></li>
                <?php if($isAdmin): ?>
                <li><a href="admin.php">Admin</a></li>
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
        <div>
            <label for="breedFilter">Filter by Breed:</label>
            <select id="breedFilter">
                <option value="All">All</option>
                <?php foreach ($breeds as $breed): ?>
                    <option value="<?= htmlspecialchars($breed) ?>"><?= htmlspecialchars($breed) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="ageFilter">Age:</label>
            <select id="ageFilter">
                <option value="All">All</option>
                <option value="0-0.5">0-6 months</option>
                <option value="0.5-1">6-12 months</option>
            </select>
        </div>
        <div>
            <label for="priceFilter">Price:</label>
            <select id="priceFilter">
                <option value="All">All</option>
                <option value="2000-5000">2000-5000 Rs</option>
                <option value="5000-10000">5000-10000 Rs</option>
                <option value="10000-15000">10000-15000 Rs</option>
            </select>
        </div>
        <div>
            <label for="genderFilter">Gender:</label>
            <select id="genderFilter">
                <option value="All">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
    </section>

    <!-- Success Message -->
    <?php if(isset($_GET['added']) && $_GET['added'] == 'true'): ?>
    <div class="success-message" id="successMessage">
        Product added to cart successfully!
    </div>
    <?php endif; ?>

    <!-- Kittens Grid -->
    <section class="kittens">
        <h2>Available Kittens</h2>
        <div class="kittens-grid" id="kittensGrid">
            <?php if(empty($products)): ?>
                <p class="no-products">No kittens available at the moment. Please check back later!</p>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <div class="kitten-card" data-breed="<?php echo isset($product['details']['breed']) ? $product['details']['breed'] : 'Unknown'; ?>" 
                         data-age="<?php echo isset($product['details']['age']) ? $product['details']['age'] : 'Unknown'; ?>" 
                         data-price="<?php echo $product['price']; ?>" 
                         data-gender="<?php echo isset($product['details']['gender']) ? $product['details']['gender'] : 'Unknown'; ?>">
                        <div class="kitten-image">
                            <img src="<?php echo $product['image_path'] ? $product['image_path'] : 'images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="kitten-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="breed">
                                Breed: <?php echo isset($product['details']['breed']) ? htmlspecialchars($product['details']['breed']) : 'Unknown'; ?>
                            </p>
                            <p class="age">
                                Age: <?php echo isset($product['details']['age']) ? htmlspecialchars($product['details']['age']) : 'Unknown'; ?> months
                            </p>
                            <p class="gender">
                                Gender: <?php echo isset($product['details']['gender']) ? htmlspecialchars($product['details']['gender']) : 'Unknown'; ?>
                            </p>
                            <p class="price">Rs. <?php echo number_format($product['price'], 2); ?></p>
                            <div class="kitten-actions">
                                <button class="add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Kitten Care Tips Section -->
    <section class="kitten-care">
        <img src="petcare-remove.png" alt="Kitten Care">
        <div class="kitten-care-text">
            <h2>Kitten Care Tips</h2>
            <p>Learn how to care for your new kitten with our expert tips!</p>
            <a href="kitten-care.html" class="learn-more-btn">Learn More</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <h2>Why Choose Us?</h2>
        <div class="reasons-grid">
            <div class="reason">
                <img src="https://images.unsplash.com/photo-1680691406746-de04c6bea9a1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8aGFwcHklMjBjYXR8ZW58MHx8MHx8fDA%3D" alt="Healthy Cat">
                <p>Healthy and well-socialized kittens from trusted breeders.</p>
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
            $('body').css('overflow', 'hidden');
        });

        $('#closeCart, #cartOverlay').click(function() {
            $('#cartPanel').removeClass('open');
            $('#cartOverlay').removeClass('active');
            $('body').css('overflow', '');
        });

        // Filter functionality
        const filterElements = document.querySelectorAll('#breedFilter, #ageFilter, #priceFilter, #genderFilter');
        filterElements.forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });
        
        function applyFilters() {
            const breedFilter = document.getElementById('breedFilter').value;
            const ageFilter = document.getElementById('ageFilter').value;
            const priceFilter = document.getElementById('priceFilter').value;
            const genderFilter = document.getElementById('genderFilter').value;
            
            const kittenCards = document.querySelectorAll('.kitten-card');
            
            kittenCards.forEach(card => {
                let showCard = true;
                
                // Apply breed filter
                if (breedFilter !== 'All') {
                    const cardBreed = card.getAttribute('data-breed');
                    if (cardBreed !== breedFilter) {
                        showCard = false;
                    }
                }
                
                // Apply age filter
                if (ageFilter !== 'All' && showCard) {
                    const cardAge = parseFloat(card.getAttribute('data-age'));
                    const [minAge, maxAge] = ageFilter.split('-').map(parseFloat);
                    
                    if (cardAge < minAge || cardAge > maxAge) {
                        showCard = false;
                    }
                }
                
                // Apply price filter
                if (priceFilter !== 'All' && showCard) {
                    const cardPrice = parseFloat(card.getAttribute('data-price'));
                    const [minPrice, maxPrice] = priceFilter.split('-').map(val => parseFloat(val.replace(/[^\d.]/g, '')));
                    
                    if (cardPrice < minPrice || cardPrice > maxPrice) {
                        showCard = false;
                    }
                }
                
                // Apply gender filter
                if (genderFilter !== 'All' && showCard) {
                    const cardGender = card.getAttribute('data-gender');
                    if (cardGender !== genderFilter) {
                        showCard = false;
                    }
                }
                
                // Show or hide the card
                card.style.display = showCard ? 'block' : 'none';
            });
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const kittenCards = document.querySelectorAll('.kitten-card');
            
            kittenCards.forEach(card => {
                const kittenName = card.querySelector('h3').textContent.toLowerCase();
                const kittenBreed = card.querySelector('.breed').textContent.toLowerCase();
                
                if (kittenName.includes(searchTerm) || kittenBreed.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Add to cart functionality with AJAX
        $('.add-to-cart-btn').click(function() {
            const productId = $(this).data('product-id');
            
            <?php if ($isLoggedIn): ?>
            // Show loading indicator or disable button
            $(this).prop('disabled', true).text('Adding...');
            const $button = $(this);
            
            $.ajax({
                url: 'kittens.php',
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
                url: 'kittens.php',
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
                url: 'kittens.php',
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
        
        // Hide success message after 3 seconds
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 3000);
        }
    });
    </script>
</body>
</html>