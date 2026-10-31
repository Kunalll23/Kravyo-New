<?php
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$conn = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

echo "=== Kitchen 43 ===\n";
$stmt = $conn->query("SELECT id, kitchen_name, city, latitude, longitude FROM kitchens WHERE id = 43");
$k = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($k);

echo "\n=== Dinesh Gupta (67) ===\n";
$stmt = $conn->query("SELECT id, city, is_online, is_busy, current_latitude, current_longitude FROM delivery_partners WHERE id = 67");
$dp = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($dp);

// Calculate distance
$lat1 = $k['latitude']; $lon1 = $k['longitude'];
$lat2 = $dp['current_latitude']; $lon2 = $dp['current_longitude'];
$theta = $lon1 - $lon2;
$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
$dist = acos($dist);
$dist = rad2deg($dist);
$miles = $dist * 60 * 1.1515;
$km = $miles * 1.609344;
echo "\nDistance: $km km\n";

