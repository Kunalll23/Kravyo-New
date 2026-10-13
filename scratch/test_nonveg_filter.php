<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/MenuItem.php';

$m = new MenuItem();
$nonVeg = $m->searchWithFilters(['is_veg' => 0]);
echo "Non-Veg Dishes Count: " . count($nonVeg) . "\n";
foreach ($nonVeg as $d) {
    echo "  - " . $d['item_name'] . " | " . $d['kitchen_name'] . " | " . $d['city'] . " | ₹" . $d['price'] . "\n";
}

$veg = $m->searchWithFilters(['is_veg' => 1]);
echo "Veg Dishes Count: " . count($veg) . "\n";
