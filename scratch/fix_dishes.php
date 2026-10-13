<?php
require_once __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);

// 1. Fix Chicken & Fish items to Non-Veg
$pdo->exec("UPDATE menu_items SET is_veg = 0, is_jain_available = 0 WHERE item_name LIKE '%Chicken%' OR item_name LIKE '%Fish%'");

// 2. Fix Vegetarian items to Veg
$pdo->exec("UPDATE menu_items SET is_veg = 1 WHERE item_name LIKE '%Paneer%' OR item_name LIKE '%Dosa%' OR item_name LIKE '%Idli%' OR item_name LIKE '%Salad%' OR item_name LIKE '%Gulab Jamun%' OR item_name LIKE '%Samosa%' OR item_name LIKE '%Lassi%' OR item_name LIKE '%Chaas%' OR item_name LIKE '%Dal%' OR item_name LIKE '%Biryani%' OR item_name LIKE '%Roti%' OR item_name LIKE '%Dhokla%' OR item_name LIKE '%Rajma%' OR item_name LIKE '%Chole%' OR item_name LIKE '%Aloo%' OR item_name LIKE '%Kheer%'");

// 3. Assign specific high-quality images
$pdo->exec("UPDATE menu_items SET image = 'chicken_curry.jpg', category_id = 2 WHERE item_name LIKE '%Chicken Curry%'");
$pdo->exec("UPDATE menu_items SET image = 'dhokla.jpg' WHERE item_name LIKE '%Dhokla%'");
$pdo->exec("UPDATE menu_items SET image = 'rajma_chawal.jpg' WHERE item_name LIKE '%Rajma Chawal%'");
$pdo->exec("UPDATE menu_items SET image = 'chole_bhature.jpg' WHERE item_name LIKE '%Chole Bhature%'");

echo "Dishes updated successfully!\n";
