<?php
// Start session if not already started
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Check if product ID is provided
if (!isset($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit();
}

$productId = $_POST['product_id'];
$remove = isset($_POST['remove']) && $_POST['remove'] == 1;
$quantityChange = isset($_POST['quantity_change']) ? intval($_POST['quantity_change']) : 0;

// Include database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cats_db';

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

$newQuantity = 0;
$cartCount = 0;
$total = 0;

if ($isLoggedIn) {
    // Update database cart
    if ($remove) {
        // Remove item
        $query = "DELETE FROM temp_cart WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $userId, $productId);
        mysqli_stmt_execute($stmt);
    } else {
        // Get current quantity
        $query = "SELECT quantity FROM temp_cart WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $userId, $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $currentQuantity = $row['quantity'];
            $newQuantity = max(0, $currentQuantity + $quantityChange);
            
            if ($newQuantity <= 0) {
                // Remove item if quantity is 0 or less
                $query = "DELETE FROM temp_cart WHERE user_id = ? AND product_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "is", $userId, $productId);
                mysqli_stmt_execute($stmt);
            } else {
                // Update quantity
                $query = "UPDATE temp_cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "iis", $newQuantity, $userId, $productId);
                mysqli_stmt_execute($stmt);
            }
        }
    }
    
    // Get updated cart count and total
    $query = "SELECT SUM(quantity) as cart_count, SUM(price * quantity) as total FROM temp_cart WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $cartCount = $row['cart_count'] ? intval($row['cart_count']) : 0;
        $total = $row['total'] ? floatval($row['total']) : 0;
    }
} else if (isset($_SESSION['cart'])) {
    // Update session cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $productId) {
            if ($remove) {
                // Remove item
                unset($_SESSION['cart'][$key]);
            } else {
                // Update quantity
                $currentQuantity = $item['quantity'];
                $newQuantity = max(0, $currentQuantity + $quantityChange);
                
                if ($newQuantity <= 0) {
                    // Remove item if quantity is 0 or less
                    unset($_SESSION['cart'][$key]);
                } else {
                    // Update quantity
                    $_SESSION['cart'][$key]['quantity'] = $newQuantity;
                }
            }
            break;
        }
    }
    
    // Re-index array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    // Calculate cart count and total
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
        $total += ($item['price'] * $item['quantity']);
    }
}

// Return updated cart data
echo json_encode([
    'success' => true,
    'new_quantity' => $newQuantity,
    'cart_count' => $cartCount,
    'total' => $total
]);