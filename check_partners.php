<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT dp.id, dp.user_id, u.email, dp.city, dp.is_online, dp.is_busy FROM delivery_partners dp JOIN delivery_users u ON dp.user_id = u.id');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
