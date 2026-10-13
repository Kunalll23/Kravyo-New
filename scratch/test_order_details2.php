<?php
$config = require __DIR__ . '/../config/database.php';
$dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

$sql = "SELECT o.*,
               k.kitchen_name, k.id AS kitchen_id_ref, k.latitude AS kitchen_lat, k.longitude AS kitchen_lng
        FROM orders o
        JOIN kitchens k ON o.kitchen_id = k.id
        ORDER BY o.id DESC LIMIT 1";

$stmt = $db->query($sql);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Order ID: {$order['id']}\n";
echo "Kitchen Lat: " . var_export($order['kitchen_lat'], true) . "\n";
echo "Kitchen Lng: " . var_export($order['kitchen_lng'], true) . "\n";
