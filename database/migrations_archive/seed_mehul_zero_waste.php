<?php
/**
 * Kravyo — Seed Active Zero Waste Deals for Mehul Chinchangare's Kitchen (Surat)
 * Kitchen ID: 43
 * Chef User ID: 56
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

echo "=== SEEDING ZERO WASTE DEALS FOR MEHUL'S KITCHEN (SURAT) ===\n";

// 1. Find Mehul's kitchen
$kitchen = $db->query("SELECT * FROM kitchens WHERE id = 43 OR (kitchen_name LIKE '%Mehul%' AND city = 'Surat') LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$kitchen) {
    die("❌ Mehul's kitchen not found!\n");
}

$kitchenId = (int)$kitchen['id'];
echo "🍳 Found Kitchen ID: $kitchenId ({$kitchen['kitchen_name']}, City: {$kitchen['city']})\n";

// Ensure kitchen is approved and open
$db->query("UPDATE kitchens SET approval_status = 'approved', is_open = 1 WHERE id = $kitchenId");

// 2. Update dish images for high visual quality
$imageUpdates = [
    44 => 'pexels-gaurav-budhiraja-98488099-32797056.jpg', // Dal Makhani
    53 => 'pexels-julia-m-cameron-6995300.jpg',            // Gulab Jamun
    54 => 'pexels-pranjall-kumar-150768-7364662.jpg',       // Kheer
    57 => 'pexels-saveurssecretes-5410418.jpg'             // Mango Lassi
];

foreach ($imageUpdates as $dishId => $imgName) {
    $db->query("UPDATE menu_items SET image = '$imgName' WHERE id = $dishId AND kitchen_id = $kitchenId");
}
echo "📸 Updated menu item images for visual presentation.\n";

// 3. Delete any stale zero_waste_items for Mehul's kitchen
$db->query("DELETE FROM zero_waste_items WHERE kitchen_id = $kitchenId");
echo "🧹 Cleared existing zero waste items for kitchen $kitchenId.\n";

// 4. Deals to seed:
// [menu_item_id, original_price, discounted_price, quantity, hours_remaining]
$deals = [
    [
        'item_id' => 59, // Gujarati Thali
        'orig'    => 350.00,
        'disc'    => 175.00, // 50% off
        'qty'     => 5,
        'mins'    => 345, // ~5h 45m left
    ],
    [
        'item_id' => 43, // Paneer Butter Masala
        'orig'    => 220.00,
        'disc'    => 120.00, // 45% off
        'qty'     => 4,
        'mins'    => 300, // ~5h left
    ],
    [
        'item_id' => 46, // Rajma Chawal
        'orig'    => 160.00,
        'disc'    => 89.00, // 44% off
        'qty'     => 6,
        'mins'    => 210, // ~3h 30m left
    ],
    [
        'item_id' => 47, // Chole Bhature
        'orig'    => 150.00,
        'disc'    => 75.00, // 50% off
        'qty'     => 5,
        'mins'    => 255, // ~4h 15m left
    ],
    [
        'item_id' => 56, // Dhokla
        'orig'    => 80.00,
        'disc'    => 40.00, // 50% off
        'qty'     => 8,
        'mins'    => 150, // ~2h 30m left
    ],
    [
        'item_id' => 44, // Dal Makhani
        'orig'    => 180.00,
        'disc'    => 99.00, // 45% off
        'qty'     => 4,
        'mins'    => 390, // ~6h 30m left
    ],
    [
        'item_id' => 53, // Gulab Jamun
        'orig'    => 90.00,
        'disc'    => 49.00, // 46% off
        'qty'     => 10,
        'mins'    => 435, // ~7h 15m left
    ],
    [
        'item_id' => 54, // Kheer
        'orig'    => 110.00,
        'disc'    => 59.00, // 46% off
        'qty'     => 4,
        'mins'    => 240, // ~4h left
    ],
];

$stmt = $db->prepare("INSERT INTO zero_waste_items 
    (menu_item_id, kitchen_id, original_price, discounted_price, quantity_available, expiry_time, status, created_at)
    VALUES (:menu_item_id, :kitchen_id, :original_price, :discounted_price, :quantity_available, DATE_ADD(NOW(), INTERVAL :mins MINUTE), 'active', NOW())");

$count = 0;
foreach ($deals as $d) {
    $stmt->execute([
        'menu_item_id'       => $d['item_id'],
        'kitchen_id'         => $kitchenId,
        'original_price'     => $d['orig'],
        'discounted_price'   => $d['disc'],
        'quantity_available' => $d['qty'],
        'mins'               => $d['mins'],
    ]);
    $count++;
}

echo "✅ Successfully inserted $count active Zero Waste deals for Mehul's Kitchen (Surat)!\n";
