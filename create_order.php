<?php
// Include database connection
require_once '../db_connection.php';

// Set headers
header('Content-Type: application/json');

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['shipping_address'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit;
}

// Sanitize and validate input data
$email = sanitize($conn, $input['email']);
$shippingAddress = sanitize($conn, $input['shipping_address']);
$billingAddress = isset($input['billing_address']) ? sanitize($conn, $input['billing_address']) : $shippingAddress;
$paymentMethod = sanitize($conn, $input['payment_method'] ?? 'credit_card');
$notes = sanitize($conn, $input['notes'] ?? '');
$phone = sanitize($conn, $input['phone'] ?? '');

// Check if user exists
$user = getUserByEmail($conn, $email);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$userId = $user['user_id'];

// Get cart items for this user
$cartItems = [];
$totalAmount = 0;

$getCartQuery = "SELECT * FROM temp_cart WHERE user_id = $userId";
$cartResult = $conn->query($getCartQuery);

if ($cartResult && $cartResult->num_rows > 0) {
    while ($item = $cartResult->fetch_assoc()) {
        $cartItems[] = $item;
        $totalAmount += $item['price'] * $item['quantity'];
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No items in cart']);
    exit;
}

// Create new order
$orderDate = date('Y-m-d H:i:s');
$orderStatus = 'pending';
$orderNumber = 'ORD-' . time() . '-' . $userId;

$createOrderQuery = "INSERT INTO orders (
    user_id, 
    order_number, 
    total_amount, 
    shipping_address, 
    billing_address, 
    payment_method, 
    order_status, 
    order_date,
    notes,
    phone
) VALUES (
    $userId, 
    '$orderNumber', 
    $totalAmount, 
    '$shippingAddress', 
    '$billingAddress', 
    '$paymentMethod', 
    '$orderStatus', 
    '$orderDate',
    '$notes',
    '$phone'
)";

if (!$conn->query($createOrderQuery)) {
    echo json_encode(['success' => false, 'error' => 'Failed to create order']);
    exit;
}

$orderId = $conn->insert_id;

// Create order items
$success = true;

foreach ($cartItems as $item) {
    $productId = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $name = $item['name'];
    
    $createOrderItemQuery = "INSERT INTO order_items (
        order_id, 
        product_id, 
        quantity, 
        price,
        product_name
    ) VALUES (
        $orderId, 
        '$productId', 
        $quantity, 
        $price,
        '$name'
    )";
    
    if (!$conn->query($createOrderItemQuery)) {
        $success = false;
        break;
    }
}

// Clear cart after successful order creation
if ($success) {
    $clearCartQuery = "DELETE FROM temp_cart WHERE user_id = $userId";
    $conn->query($clearCartQuery);
    
    echo json_encode([
        'success' => true, 
        'order_id' => $orderId,
        'order_number' => $orderNumber
    ]);
} else {
    // Rollback the order if items insertion failed
    $conn->query("DELETE FROM orders WHERE order_id = $orderId");
    echo json_encode(['success' => false, 'error' => 'Failed to create order items']);
}

$conn->close();
?>