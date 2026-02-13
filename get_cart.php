<?php
// Start session if not already started
session_start();

// Set content type to JSON
header('Content-Type: application/json');

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

$items = [];
$total = 0;

if ($isLoggedIn) {
    // Get cart items from database
    $query = "SELECT tc.*, p.image_path 
              FROM temp_cart tc 
              LEFT JOIN products p ON tc.product_id = p.product_id 
              WHERE tc.user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'image' => !empty($row['image']) ? $row['image'] : (!empty($row['image_path']) ? $row['image_path'] : 'images/placeholder.jpg')
        ];
        $total += ($row['price'] * $row['quantity']);
    }
} else if (isset($_SESSION['cart'])) {
    // Get cart items from session
    foreach ($_SESSION['cart'] as $item) {
        $items[] = [
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => !empty($item['image']) ? $item['image'] : 'images/placeholder.jpg'
        ];
        $total += ($item['price'] * $item['quantity']);
    }
}

// Return cart data as JSON
echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total,
    'cart_count' => count($items)
]);