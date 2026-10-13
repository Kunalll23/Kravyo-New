<?php
require_once __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
$stmt = $pdo->query('SELECT id, item_name, is_veg, is_jain_available, is_diabetic_friendly, category_id, image FROM menu_items ORDER BY id ASC');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    echo "ID: {$item['id']} | Name: {$item['item_name']} | is_veg: {$item['is_veg']} | Jain: {$item['is_jain_available']} | Diabetic: {$item['is_diabetic_friendly']} | Img: {$item['image']}\n";
}
