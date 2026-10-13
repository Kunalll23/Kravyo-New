<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Model.php';
require_once APP_PATH . '/models/Kitchen.php';

$kitchenModel = new Kitchen();

echo "=== TEST 1: Pune location (18.5204, 73.8567) radius 10km ===\n";
$results = $kitchenModel->findNearby(18.5204, 73.8567, 10.0);
echo "Found " . count($results) . " kitchens:\n";
foreach ($results as $k) {
    echo "- {$k['kitchen_name']} | {$k['distance']} km away | {$k['address']} | Open: {$k['is_open']} | Rating: {$k['avg_rating']}\n";
}

echo "\n=== TEST 2: Surat location (21.1702, 72.8311) radius 10km ===\n";
$results2 = $kitchenModel->findNearby(21.1702, 72.8311, 10.0);
echo "Found " . count($results2) . " kitchens:\n";
foreach ($results2 as $k) {
    echo "- {$k['kitchen_name']} | {$k['distance']} km away | {$k['address']} | Open: {$k['is_open']} | Rating: {$k['avg_rating']}\n";
}

echo "\n=== TEST 3: All with coordinates ===\n";
$all = $kitchenModel->findAllWithCoordinates([], 10);
echo "Total with coords sample (limit 10): " . count($all) . "\n";
