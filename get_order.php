<?php
// Include database connection
require_once '../db_connection.php';

// Set headers
header('Content-Type: application/json');

// Check if order_id parameter is provided
if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Order ID parameter is required']);
    exit;
}

// Get order_id from request
$orderId = (int)$_GET['order_id'];

// Get order details
$getOrderQuery = "SELECT * FROM orders WHERE order_id = $orderId";
$orderResult = $conn->query($getOrderQuery);

if (!$orderResult || $orderResult->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$order = $orderResult->fetch_assoc();

// Get user information
$userId = $order['user_id'];
$getUserQuery = "SELECT * FROM users WHERE user_id = $userId";
$userResult = $conn->query($getUserQuery);
$user = $userResult->fetch_assoc();

// Remove sensitive data
unset($user['password']);

// Get order items
$items = [];
$getItemsQuery = "SELECT * FROM order_items WHERE order_id = $orderId";
$itemsResult = $conn->query($getItemsQuery);

if ($itemsResult && $itemsResult->num_rows > 0) {
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
}

$order['items'] = $items;
$order['user'] = $user;

echo json_encode(['success' => true, 'order' => $order]);

$conn->close();
?>