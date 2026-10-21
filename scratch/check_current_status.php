<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$today = date('Y-m-d');

echo "--- TODAY'S DELIVERIES IN subscription_deliveries FOR KITCHEN 43 ---\n";
$stmt = $db->query("SELECT * FROM subscription_deliveries WHERE kitchen_id = 43 AND delivery_date = '{$today}'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- RECENT ORDERS TODAY ---\n";
$stmt = $db->query("SELECT id, order_number, order_type, kitchen_id, order_status, delivery_status, subscription_delivery_id, created_at, updated_at FROM orders WHERE kitchen_id = 43 ORDER BY id DESC LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- CUSTOMER SUBSCRIPTIONS FOR KITCHEN 43 ---\n";
$stmt = $db->query("SELECT id, customer_id, subscription_plan_id, status, start_date, end_date, updated_at FROM customer_subscriptions WHERE kitchen_id = 43");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
