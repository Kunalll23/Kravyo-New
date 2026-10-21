<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT id, status FROM subscription_deliveries WHERE kitchen_id = 43 AND delivery_date = '2026-10-21'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
