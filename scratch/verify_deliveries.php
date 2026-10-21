<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once APP_PATH . '/models/SubscriptionDelivery.php';

$sd = new SubscriptionDelivery();
$deliveries = $sd->getTodaysDeliveries(43);

echo "Total Deliveries: " . count($deliveries) . "\n";
echo "--------------------------------------------------------------------------------------------------\n";
printf("%-4s | %-16s | %-32s | %-8s | %-8s\n", "ID", "Customer", "Plan", "Meal", "Status");
echo "--------------------------------------------------------------------------------------------------\n";
foreach ($deliveries as $d) {
    printf("%-4d | %-16s | %-32s | %-8s | %-8s\n", $d['id'], substr($d['customer_name'], 0, 16), substr($d['plan_name'], 0, 32), $d['meal_type'], $d['status']);
}
echo "--------------------------------------------------------------------------------------------------\n";

$b = count(array_filter($deliveries, fn($d) => strtolower($d['meal_type']) === 'breakfast'));
$l = count(array_filter($deliveries, fn($d) => strtolower($d['meal_type']) === 'lunch'));
$dn = count(array_filter($deliveries, fn($d) => strtolower($d['meal_type']) === 'dinner'));

echo "Filter Counts: All (" . count($deliveries) . ") | Breakfast ({$b}) | Lunch ({$l}) | Dinner ({$dn})\n";
