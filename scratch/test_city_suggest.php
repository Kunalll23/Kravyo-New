<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/MenuItem.php';

$m = new MenuItem();

echo "=== TEST 1: Query 'Paneer' for SURAT ONLY ===\n";
$resSurat = $m->getDishSuggestions('Paneer', 'Surat', 10);
echo "Count in Surat: " . count($resSurat) . "\n";
foreach ($resSurat as $d) {
    echo "  - {$d['item_name']} | ₹{$d['price']} | {$d['kitchen_name']} | City: {$d['city']}\n";
    if ($d['city'] !== 'Surat') {
        echo "  [FAIL] Unexpected city: {$d['city']}\n";
    }
}

echo "\n=== TEST 2: Query 'Paneer' for BANGALORE ONLY ===\n";
$resBangalore = $m->getDishSuggestions('Paneer', 'Bangalore', 10);
echo "Count in Bangalore: " . count($resBangalore) . "\n";
foreach ($resBangalore as $d) {
    echo "  - {$d['item_name']} | ₹{$d['price']} | {$d['kitchen_name']} | City: {$d['city']}\n";
    if ($d['city'] !== 'Bangalore') {
        echo "  [FAIL] Unexpected city: {$d['city']}\n";
    }
}

echo "\n=== TEST 3: Query 'Chicken' for SURAT ONLY ===\n";
$resChickenSurat = $m->getDishSuggestions('Chicken', 'Surat', 10);
echo "Count in Surat: " . count($resChickenSurat) . "\n";
foreach ($resChickenSurat as $d) {
    echo "  - {$d['item_name']} | ₹{$d['price']} | {$d['kitchen_name']} | City: {$d['city']}\n";
    if ($d['city'] !== 'Surat') {
        echo "  [FAIL] Unexpected city: {$d['city']}\n";
    }
}

echo "\n=== TEST 4: Query 'Chicken' for HYDERABAD ONLY ===\n";
$resChickenHyd = $m->getDishSuggestions('Chicken', 'Hyderabad', 10);
echo "Count in Hyderabad: " . count($resChickenHyd) . "\n";
foreach ($resChickenHyd as $d) {
    echo "  - {$d['item_name']} | ₹{$d['price']} | {$d['kitchen_name']} | City: {$d['city']}\n";
    if ($d['city'] !== 'Hyderabad') {
        echo "  [FAIL] Unexpected city: {$d['city']}\n";
    }
}
