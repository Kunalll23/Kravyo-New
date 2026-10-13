<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT id, kitchen_name, city FROM kitchens LIMIT 10');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
