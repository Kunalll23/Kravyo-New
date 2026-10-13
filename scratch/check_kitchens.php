<?php
$config = require __DIR__ . '/../config/database.php';
$dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

$stmt = $db->query("SELECT id, kitchen_name, latitude, longitude FROM kitchens LIMIT 10");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Kitchens Data:\n";
foreach ($results as $row) {
    echo "ID: {$row['id']} | Name: {$row['kitchen_name']} | Lat: " . ($row['latitude'] ?? 'NULL') . " | Lng: " . ($row['longitude'] ?? 'NULL') . "\n";
}
