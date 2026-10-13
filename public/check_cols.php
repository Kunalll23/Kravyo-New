<?php
require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();
$kitchen = $db->query("SELECT * FROM kitchens LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$menuItem = $db->query("SELECT * FROM menu_items LIMIT 1")->fetch(PDO::FETCH_ASSOC);

print_r(['kitchen' => $kitchen, 'menu_item' => $menuItem]);
