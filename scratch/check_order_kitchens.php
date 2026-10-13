<?php
$config = require __DIR__ . '/../config/database.php';
$dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
$stmt = $db->query("SELECT o.id, o.kitchen_id, k.latitude, k.kitchen_name FROM orders o JOIN kitchens k ON o.kitchen_id = k.id ORDER BY o.id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
