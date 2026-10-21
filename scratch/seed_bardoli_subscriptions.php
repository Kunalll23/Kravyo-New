<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();
$kitchenId = 43;

echo "1. Ensuring clean Bardoli addresses for customers...\n";

// Update Kunalll's address (user_id 55) or create a fresh Bardoli address
$stmt = $db->prepare("SELECT id FROM addresses WHERE user_id = 55 AND city = 'Bardoli' LIMIT 1");
$stmt->execute();
$kunalllAddr = $stmt->fetch();

if ($kunalllAddr) {
    $kunalllAddrId = (int)$kunalllAddr['id'];
    $db->query("UPDATE addresses SET street_address = 'B-204, Shyam Residency, Station Road', landmark = 'Opp. Circuit House', city = 'Bardoli', pincode = '394601', latitude = 21.11950000, longitude = 73.11420000, is_deleted = 0 WHERE id = {$kunalllAddrId}");
} else {
    $db->query("INSERT INTO addresses (user_id, address_type, street_address, landmark, city, pincode, latitude, longitude, is_deleted)
                VALUES (55, 'Home', 'B-204, Shyam Residency, Station Road', 'Opp. Circuit House', 'Bardoli', '394601', 21.11950000, 73.11420000, 0)");
    $kunalllAddrId = (int)$db->lastInsertId();
}

// Update Priya Patel (user_id 107, address_id 77)
$db->query("UPDATE addresses SET street_address = 'House 11, Sardar Patel Society, Dhulia Road', landmark = 'Near Jalaram Temple', city = 'Bardoli', pincode = '394601', latitude = 21.11480000, longitude = 73.11850000, is_deleted = 0 WHERE id = 77");

// Update Ritu Agarwal (user_id 105, address_id 73)
$db->query("UPDATE addresses SET street_address = 'Flat 102, Shradha Residency, Baben Road', landmark = 'Near Baben Lake', city = 'Bardoli', pincode = '394601', latitude = 21.12520000, longitude = 73.12310000, is_deleted = 0 WHERE id = 73");

// Update Vikram Desai (user_id 106, address_id 75)
$db->query("UPDATE addresses SET street_address = 'Apt 5, Somnath Heritage, Gandhi Road', landmark = 'Near Gandhi Ashram', city = 'Bardoli', pincode = '394601', latitude = 21.11720000, longitude = 73.10980000, is_deleted = 0 WHERE id = 75");

// aashsis address (address_id 113) is already at Maliba Campus, Tarsadi, Bardoli
$db->query("UPDATE addresses SET street_address = 'Hostel Block B, Maliba Campus, Mahuva-Bardoli Rd, Tarsadi', landmark = 'UKA Tarsadia University', city = 'Bardoli', pincode = '394350', latitude = 21.06928800, longitude = 73.13398950, is_deleted = 0 WHERE id = 113");

echo "Addresses updated to authentic Bardoli locations.\n\n";

echo "2. Resetting old Surat customer subscriptions for kitchen 43 to completed...\n";
$db->query("UPDATE customer_subscriptions SET status = 'completed' WHERE kitchen_id = 43 AND customer_id NOT IN (55, 105, 106, 107, 110)");

echo "3. Configuring 5 Bardoli Active Subscriptions for Kitchen 43...\n";

// Helper to upsert customer subscription
function upsertSub($db, $kitchenId, $customerId, $planId, $addressId, $mealsDesc, $instructions) {
    $stmt = $db->prepare("SELECT id FROM customer_subscriptions WHERE kitchen_id = ? AND customer_id = ? LIMIT 1");
    $stmt->execute([$kitchenId, $customerId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $subId = (int)$existing['id'];
        $stmt = $db->prepare("UPDATE customer_subscriptions 
                              SET subscription_plan_id = ?, address_id = ?, start_date = '2026-10-15', end_date = '2026-11-15',
                                  status = 'active', payment_status = 'completed', special_instructions = ?, updated_at = NOW() 
                              WHERE id = ?");
        $stmt->execute([$planId, $addressId, $instructions, $subId]);
        return $subId;
    } else {
        $stmt = $db->prepare("INSERT INTO customer_subscriptions 
                              (customer_id, subscription_plan_id, kitchen_id, address_id, start_date, end_date, status, payment_method, payment_status, total_paid, special_instructions, created_at, updated_at)
                              VALUES (?, ?, ?, ?, '2026-10-15', '2026-11-15', 'active', 'upi', 'completed', 1249.00, ?, NOW(), NOW())");
        $stmt->execute([$customerId, $planId, $kitchenId, $addressId, $instructions]);
        return (int)$db->lastInsertId();
    }
}

// 1. Kunalll (55) - Shahi Paneer Deluxe Tiffin (Plan 15)
$sub1 = upsertSub($db, $kitchenId, 55, 15, $kunalllAddrId, '1 meal', 'Deliver before 1 PM');

// 2. aashsis (110) - Kisan Rasoi Mini Lunch Box (Plan 14)
$sub2 = upsertSub($db, $kitchenId, 110, 14, 113, '1 meal', 'Call upon arrival at campus gate');

// 3. Priya Patel (107) - Daily Swasthya Thali (Plan 11)
$sub3 = upsertSub($db, $kitchenId, 107, 11, 77, '1 meal', 'Less oil, no onion');

// 4. Ritu Agarwal (105) - Maa Ke Haath Ka Swaad Special (Plan 16 - 2 meals)
$sub4 = upsertSub($db, $kitchenId, 105, 16, 73, '2 meals', 'Extra green salad please');

// 5. Vikram Desai (106) - Ghar Ka Swad Executive (Plan 12 - 2 meals)
$sub5 = upsertSub($db, $kitchenId, 106, 12, 75, '2 meals', 'Ring bell, ground floor');

echo "5 Bardoli subscriptions configured.\n\n";

echo "4. Removing old delivery records for today and generating fresh Bardoli deliveries...\n";
$today = date('Y-m-d');
$db->query("DELETE FROM subscription_deliveries WHERE kitchen_id = {$kitchenId} AND delivery_date = '{$today}'");

require_once ROOT_PATH . '/core/Model.php';
require_once APP_PATH . '/models/SubscriptionDelivery.php';

$subDeliveryModel = new SubscriptionDelivery();
$deliveries = $subDeliveryModel->getTodaysDeliveries($kitchenId);

echo "Today's Bardoli Deliveries Count: " . count($deliveries) . "\n";
echo "------------------------------------------------------------------------------------------------------------------------\n";
printf("%-4s | %-16s | %-30s | %-7s | %-40s | %-8s\n", "ID", "Customer", "Plan", "Meal", "Address", "Status");
echo "------------------------------------------------------------------------------------------------------------------------\n";
foreach ($deliveries as $d) {
    printf("%-4d | %-16s | %-30s | %-7s | %-40s | %-8s\n", 
        $d['id'], 
        substr($d['customer_name'], 0, 16), 
        substr($d['plan_name'], 0, 30), 
        $d['meal_type'], 
        substr($d['street_address'] . ', ' . $d['delivery_city'], 0, 40), 
        $d['status']
    );
}
echo "------------------------------------------------------------------------------------------------------------------------\n";
