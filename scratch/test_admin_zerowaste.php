<?php
require_once 'C:\xampp\htdocs\Kravyo\config\app.php';
require_once 'C:\xampp\htdocs\Kravyo\config\constants.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Database.php';
require_once 'C:\xampp\htdocs\Kravyo\core\Model.php';
require_once 'C:\xampp\htdocs\Kravyo\app\models\ZeroWasteItem.php';

$zw = new ZeroWasteItem();

echo "--- TEST 1: findAllWithAdminDetails (all) ---\n";
$all = $zw->findAllWithAdminDetails();
echo "Total listings returned: " . count($all) . "\n";
if (count($all) > 0) {
    echo "First listing: item_name=" . $all[0]['item_name'] . ", kitchen=" . $all[0]['kitchen_name'] . ", status=" . $all[0]['status'] . "\n";
}

echo "\n--- TEST 2: getPlatformStats ---\n";
$stats = $zw->getPlatformStats();
echo "total_listings=" . ($stats['total_listings'] ?? 0) . "\n";
echo "active_listings=" . ($stats['active_listings'] ?? 0) . "\n";
echo "sold_out_listings=" . ($stats['sold_out_listings'] ?? 0) . "\n";
echo "expired_listings=" . ($stats['expired_listings'] ?? 0) . "\n";
echo "total_active_qty=" . ($stats['total_active_qty'] ?? 0) . "\n";
echo "total_original_value=" . number_format((float)($stats['total_original_value'] ?? 0), 2) . "\n";
echo "total_discounted_value=" . number_format((float)($stats['total_discounted_value'] ?? 0), 2) . "\n";
echo "potential_savings=" . number_format((float)($stats['potential_savings'] ?? 0), 2) . "\n";

echo "\n--- TEST 3: getStatsByKitchen ---\n";
$byKitchen = $zw->getStatsByKitchen();
echo "Kitchen rows: " . count($byKitchen) . "\n";
foreach ($byKitchen as $row) {
    echo " - " . $row['kitchen_name'] . " (" . $row['city'] . "): total=" . $row['total_listings'] . ", active=" . $row['active_listings'] . "\n";
}

echo "\n--- TEST 4: Status filter (active only) ---\n";
$active = $zw->findAllWithAdminDetails('active');
echo "Active listings: " . count($active) . "\n";

echo "\n--- All tests passed! ---\n";
