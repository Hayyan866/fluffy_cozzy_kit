<?php
// Include database connection
require_once '../db_connection.php';

// Set headers
header('Content-Type: application/json');

// Check if email parameter is provided
if (!isset($_GET['email'])) {
    echo json_encode(['success' => false, 'error' => 'Email parameter is required']);
    exit;
}

// Get email from request
$email = sanitize($conn, $_GET['email']);

// Check if user exists
$user = getUserByEmail($conn, $email);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$userId = $user['user_id'];

// Get orders for this user
$orders = [];

$getOrdersQuery = "SELECT * FROM orders WHERE user_id = $userId ORDER BY order_date DESC";
$ordersResult = $conn->query($getOrdersQuery);

if ($ordersResult && $ordersResult->num_rows > 0) {
    while ($order = $ordersResult->fetch_assoc()) {
        // Get order items
        $orderId = $order['order_id'];
        $items = [];
        
        $getItemsQuery = "SELECT * FROM order_items WHERE order_id = $orderId";
        $itemsResult = $conn->query($getItemsQuery);
        
        if ($itemsResult && $itemsResult->num_rows > 0) {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        }
        
        $order['items'] = $items;
        $orders[] = $order;
    }
}

echo json_encode(['success' => true, 'orders' => $orders]);

$conn->close();
?>