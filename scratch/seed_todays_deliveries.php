<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$kitchenId = 43;
$today = date('Y-m-d');

echo "Setting up active subscriptions for Kitchen ID: {$kitchenId}\n";

// 1. Subscription 29 (Kunalll) - keep active
$stmt = $db->prepare("UPDATE customer_subscriptions SET status = 'active', start_date = '2026-10-15', end_date = '2026-11-15' WHERE id = 29 AND kitchen_id = ?");
$stmt->execute([$kitchenId]);

// 2. Subscription 16 (Neha Singh) - reactivate
$stmt = $db->prepare("UPDATE customer_subscriptions SET status = 'active', start_date = '2026-10-15', end_date = '2026-11-15', subscription_plan_id = 11, address_id = 1, special_instructions = 'Less spicy dal please' WHERE id = 16 AND kitchen_id = ?");
$stmt->execute([$kitchenId]);

// 3. Subscription 17 (Amit Kumar) - reactivate
$stmt = $db->prepare("UPDATE customer_subscriptions SET status = 'active', start_date = '2026-10-15', end_date = '2026-11-15', subscription_plan_id = 14, address_id = 4, special_instructions = 'Deliver at reception 2nd floor' WHERE id = 17 AND kitchen_id = ?");
$stmt->execute([$kitchenId]);

// 4. Subscription 18 (Vikram Desai) - reactivate with 2 meals
$stmt = $db->prepare("UPDATE customer_subscriptions SET status = 'active', start_date = '2026-10-15', end_date = '2026-11-15', subscription_plan_id = 12, address_id = 2, special_instructions = 'Ring doorbell twice' WHERE id = 18 AND kitchen_id = ?");
$stmt->execute([$kitchenId]);

// 5. Check if Karan Joshi (customer_id 57) has a subscription, or create one
$stmt = $db->prepare("SELECT id FROM customer_subscriptions WHERE customer_id = 57 AND kitchen_id = ?");
$stmt->execute([$kitchenId]);
$existingKaran = $stmt->fetch();

if ($existingKaran) {
    $stmt = $db->prepare("UPDATE customer_subscriptions SET status = 'active', start_date = '2026-10-15', end_date = '2026-11-15', subscription_plan_id = 16, address_id = 23, special_instructions = 'Extra salad please' WHERE id = ?");
    $stmt->execute([$existingKaran['id']]);
} else {
    $stmt = $db->prepare("INSERT INTO customer_subscriptions (customer_id, subscription_plan_id, kitchen_id, address_id, start_date, end_date, status, payment_method, payment_status, total_paid, special_instructions, created_at, updated_at)
                          VALUES (57, 16, ?, 23, '2026-10-15', '2026-11-15', 'active', 'upi', 'completed', 1249.00, 'Extra salad please', NOW(), NOW())");
    $stmt->execute([$kitchenId]);
}

// 6. Check if Pooja Reddy or another customer can be added as 6th active subscriber (for extra richness)
$stmt = $db->prepare("SELECT id FROM customer_subscriptions WHERE customer_id = 52 AND kitchen_id = ? AND id != 18");
$stmt->execute([$kitchenId]);

echo "Active subscriptions configured. Now triggering SubscriptionDelivery::getTodaysDeliveries...\n";

require_once ROOT_PATH . '/core/Model.php';
require_once APP_PATH . '/models/SubscriptionDelivery.php';

$subDeliveryModel = new SubscriptionDelivery();
$deliveries = $subDeliveryModel->getTodaysDeliveries($kitchenId);

echo "Today's Deliveries Count: " . count($deliveries) . "\n";
foreach ($deliveries as $idx => $d) {
    echo ($idx + 1) . ". [ID: {$d['id']}] {$d['customer_name']} - {$d['plan_name']} ({$d['meal_type']}) - Status: {$d['status']}\n";
}
