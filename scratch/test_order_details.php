<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Model.php';
require __DIR__ . '/../app/models/Order.php';

$orderModel = new Order();
$order = $orderModel->findWithDetails(1);
echo "Order 1:\n";
print_r([
    'kitchen_id' => $order['kitchen_id'] ?? null,
    'kitchen_lat' => $order['kitchen_lat'] ?? null,
    'kitchen_lng' => $order['kitchen_lng'] ?? null
]);

// Find any order that exists
$db = Database::getInstance();
$stmt = $db->query("SELECT id FROM orders LIMIT 1");
$firstOrderId = $stmt->fetchColumn();
echo "Order {$firstOrderId}:\n";
$order = $orderModel->findWithDetails($firstOrderId);
print_r([
    'kitchen_id' => $order['kitchen_id'] ?? null,
    'kitchen_lat' => $order['kitchen_lat'] ?? null,
    'kitchen_lng' => $order['kitchen_lng'] ?? null
]);
