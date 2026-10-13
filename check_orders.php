<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT id, order_number, order_status, dispatch_status FROM orders ORDER BY id DESC LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
