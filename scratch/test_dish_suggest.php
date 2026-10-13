<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/MenuItem.php';

$m = new MenuItem();

echo "--- TEST 1: Query 'Paneer' ---\n";
$res1 = $m->getDishSuggestions('Paneer', 'Surat', 5);
foreach ($res1 as $d) {
    echo "Dish: {$d['item_name']} | Price: ₹{$d['price']} | Veg: {$d['is_veg']} | Kitchen: {$d['kitchen_name']} ({$d['city']})\n";
}

echo "\n--- TEST 2: Query 'Chicken' (Non-veg) ---\n";
$res2 = $m->getDishSuggestions('Chicken', 'Surat', 5);
foreach ($res2 as $d) {
    echo "Dish: {$d['item_name']} | Price: ₹{$d['price']} | Veg: {$d['is_veg']} | Kitchen: {$d['kitchen_name']} ({$d['city']})\n";
}

echo "\n--- TEST 3: Query 'Thali' ---\n";
$res3 = $m->getDishSuggestions('Thali', 'Surat', 5);
foreach ($res3 as $d) {
    echo "Dish: {$d['item_name']} | Price: ₹{$d['price']} | Veg: {$d['is_veg']} | Kitchen: {$d['kitchen_name']} ({$d['city']})\n";
}

echo "\n--- TEST 4: Query with 1 character 'd' ---\n";
$res4 = $m->getDishSuggestions('d', 'Surat', 5);
foreach ($res4 as $d) {
    echo "Dish: {$d['item_name']} | Price: ₹{$d['price']} | Veg: {$d['is_veg']} | Kitchen: {$d['kitchen_name']} ({$d['city']})\n";
}
