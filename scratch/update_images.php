<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Database.php';

$db = Database::getInstance();
$db->query("UPDATE menu_items SET image = 'default_dish.jpg'");
echo "All menu items updated with default dish image.\n";
