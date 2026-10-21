<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$today = date('Y-m-d');
$kitchenId = 43;

echo "1. Reverting subscription_deliveries status for Kitchen 43 on {$today} back to 'pending'...\n";
$stmt = $db->prepare("UPDATE subscription_deliveries 
                      SET status = 'pending', updated_at = NOW() 
                      WHERE kitchen_id = ? AND delivery_date = ?");
$stmt->execute([$kitchenId, $today]);
echo "Updated " . $stmt->rowCount() . " delivery records to 'pending'.\n\n";

echo "2. Cleaning up test orders 216-223 created during dispatch...\n";
$orderIdsStmt = $db->query("SELECT id FROM orders WHERE id >= 216 AND kitchen_id = {$kitchenId} AND subscription_delivery_id IS NOT NULL");
$orderIds = $orderIdsStmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($orderIds)) {
    $placeholders = implode(',', array_map('intval', $orderIds));
    $db->query("DELETE FROM delivery_offers WHERE order_id IN ({$placeholders})");
    $db->query("DELETE FROM orders WHERE id IN ({$placeholders})");
    echo "Deleted orders (" . implode(', ', $orderIds) . ") and their delivery offers.\n\n";
} else {
    echo "No matching orders found to delete.\n\n";
}

echo "3. Current Status of Today's Deliveries for Kitchen 43:\n";
$stmt = $db->prepare("SELECT id, customer_subscription_id, meal_type, delivery_time, status, updated_at 
                      FROM subscription_deliveries 
                      WHERE kitchen_id = ? AND delivery_date = ?");
$stmt->execute([$kitchenId, $today]);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($deliveries);
