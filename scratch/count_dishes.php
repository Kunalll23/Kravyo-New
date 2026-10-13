<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$total = $db->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
$available = $db->query('SELECT COUNT(*) FROM menu_items WHERE is_available = 1')->fetchColumn();
$veg = $db->query('SELECT COUNT(*) FROM menu_items WHERE is_veg = 1')->fetchColumn();
$nonVeg = $db->query('SELECT COUNT(*) FROM menu_items WHERE is_veg = 0')->fetchColumn();

// By Category
$byCategory = $db->query('SELECT c.category_name, COUNT(m.id) as cnt FROM menu_items m JOIN categories c ON m.category_id = c.id GROUP BY c.id, c.category_name ORDER BY cnt DESC')->fetchAll(PDO::FETCH_ASSOC);

// By Kitchen
$byKitchen = $db->query('SELECT k.kitchen_name, k.city, COUNT(m.id) as cnt FROM menu_items m JOIN kitchens k ON m.kitchen_id = k.id GROUP BY k.id, k.kitchen_name, k.city ORDER BY cnt DESC')->fetchAll(PDO::FETCH_ASSOC);

// Zero waste items
$zeroWasteCount = $db->query('SELECT COUNT(*) FROM zero_waste_items WHERE status = "active"')->fetchColumn();

echo json_encode([
    'total' => (int)$total,
    'available' => (int)$available,
    'veg' => (int)$veg,
    'non_veg' => (int)$nonVeg,
    'active_zero_waste_deals' => (int)$zeroWasteCount,
    'by_category' => $byCategory,
    'by_kitchen' => $byKitchen
], JSON_PRETTY_PRINT);
