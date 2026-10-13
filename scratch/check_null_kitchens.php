<?php
$config = require __DIR__ . '/../config/database.php';
$dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
$stmt = $db->query("SELECT COUNT(*) FROM kitchens WHERE latitude IS NULL OR longitude IS NULL");
echo 'Null kitchens: ' . $stmt->fetchColumn() . "\n";
