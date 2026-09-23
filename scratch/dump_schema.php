<?php
define('CONFIG_PATH', 'c:/xampp/htdocs/Kravyo/config');
define('APP_ENV', 'development');
require 'c:/xampp/htdocs/Kravyo/core/Database.php';

$db = Database::getInstance();

echo "--- USERS ---\n";
print_r($db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- KITCHENS ---\n";
print_r($db->query("DESCRIBE kitchens")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- MENU_ITEMS ---\n";
print_r($db->query("DESCRIBE menu_items")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- CATEGORIES ---\n";
print_r($db->query("DESCRIBE categories")->fetchAll(PDO::FETCH_ASSOC));
