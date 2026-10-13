<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../core/Database.php';
require __DIR__ . '/../core/Model.php';
require __DIR__ . '/../app/models/Order.php';

$orderModel = new Order();
$order = $orderModel->findWithDetails(156);

echo "kitchen_lat from db: ";
var_dump($order['kitchen_lat']);

$out1 = $order['kitchen_lat'] ?? 20.0;
echo "kitchenLat JS output: const kitchenLat = {$out1};\n";
