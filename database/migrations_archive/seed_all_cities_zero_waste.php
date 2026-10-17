<?php
/**
 * Kravyo — Seed Active Zero Waste Deals Across All Major Cities
 * Surat, Delhi, Ahmedabad, Mumbai, Bangalore
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

echo "=== SEEDING ZERO WASTE DEALS ACROSS ALL CITIES ===\n";

// 1. First ensure Delhi has menu items
$delhiCheck = $db->query("SELECT COUNT(*) FROM menu_items WHERE kitchen_id = 11")->fetchColumn();
if ((int)$delhiCheck === 0) {
    $db->query("INSERT INTO menu_items (kitchen_id, category_id, item_name, description, price, is_veg, is_available, image) VALUES 
        (11, 1, 'Chole Bhature Special', 'Authentic Delhi style spicy chickpeas with fluffy deep-fried bhature', 150.00, 1, 1, 'chole_bhature.jpg'),
        (11, 1, 'Dal Makhani Shahi', 'Slow-cooked black lentils with fresh cream and butter', 180.00, 1, 1, 'pexels-gaurav-budhiraja-98488099-32797056.jpg'),
        (11, 1, 'Paneer Butter Masala', 'Cottage cheese cubes in rich tomato cashew gravy', 220.00, 1, 1, 'pexels-chanwalrus-958545.jpg'),
        (11, 1, 'Rajma Chawal Desi', 'Classic homestyle red kidney bean curry with steamed basmati rice', 160.00, 1, 1, 'rajma_chawal.jpg')
    ");
    echo "🍛 Added Delhi menu items for Rohit's Delhi Rasoi (Kitchen 11).\n";
}

// Ensure Simran's Punjabi Rasoi (Kitchen 12) image is updated
$db->query("UPDATE menu_items SET image = 'pexels-julia-m-cameron-6995300.jpg' WHERE id = 11");

// Ensure Komal's Homemade Food (Kitchen 32) in Ahmedabad has images
$db->query("UPDATE menu_items SET image = 'dhokla.jpg' WHERE id = 41");
$db->query("UPDATE menu_items SET image = 'pexels-saveurssecretes-5410418.jpg' WHERE id = 36");

// Ensure Pooja's Thali House (Kitchen 8) in Mumbai has images
$db->query("UPDATE menu_items SET image = 'pexels-chanwalrus-958545.jpg' WHERE id = 31");
$db->query("UPDATE menu_items SET image = 'pexels-julia-m-cameron-6995300.jpg' WHERE id = 40");

// Ensure Divya's Telugu Meals (Kitchen 16) in Bangalore has images
$db->query("UPDATE menu_items SET image = 'chicken_curry.jpg' WHERE id = 9");

// Clear existing zero waste items
$db->query("DELETE FROM zero_waste_items");
echo "🧹 Cleared existing zero waste items.\n";

$dealsToInsert = [
    // ── SURAT (Mehul Chinchangare's Kitchen - 43) ──
    ['item_id' => 59, 'kitchen_id' => 43, 'orig' => 350.00, 'disc' => 175.00, 'qty' => 5, 'mins' => 345],
    ['item_id' => 43, 'kitchen_id' => 43, 'orig' => 220.00, 'disc' => 120.00, 'qty' => 4, 'mins' => 300],
    ['item_id' => 46, 'kitchen_id' => 43, 'orig' => 160.00, 'disc' => 89.00,  'qty' => 6, 'mins' => 210],
    ['item_id' => 47, 'kitchen_id' => 43, 'orig' => 150.00, 'disc' => 75.00,  'qty' => 5, 'mins' => 255],
    ['item_id' => 56, 'kitchen_id' => 43, 'orig' => 80.00,  'disc' => 40.00,  'qty' => 8, 'mins' => 150],
    ['item_id' => 44, 'kitchen_id' => 43, 'orig' => 180.00, 'disc' => 99.00,  'qty' => 4, 'mins' => 390],
    ['item_id' => 53, 'kitchen_id' => 43, 'orig' => 90.00,  'disc' => 49.00,  'qty' => 10,'mins' => 435],
    ['item_id' => 54, 'kitchen_id' => 43, 'orig' => 110.00, 'disc' => 59.00,  'qty' => 4, 'mins' => 240],

    // ── DELHI ──
    // Get newly inserted item IDs for Kitchen 11
];

// Fetch Delhi item IDs
$delhiItems = $db->query("SELECT id, item_name, price FROM menu_items WHERE kitchen_id = 11")->fetchAll(PDO::FETCH_ASSOC);
foreach ($delhiItems as $di) {
    $disc = round($di['price'] * 0.5, 2);
    $dealsToInsert[] = [
        'item_id'    => (int)$di['id'],
        'kitchen_id' => 11,
        'orig'       => (float)$di['price'],
        'disc'       => $disc,
        'qty'        => 5,
        'mins'       => mt_rand(180, 400),
    ];
}
// Add Simran's Gulab Jamun Shahi (item 11, kitchen 12)
$dealsToInsert[] = ['item_id' => 11, 'kitchen_id' => 12, 'orig' => 111.00, 'disc' => 55.00, 'qty' => 8, 'mins' => 280];

// ── AHMEDABAD ──
// Kitchen 31 (Varun) & Kitchen 32 (Komal)
$dealsToInsert[] = ['item_id' => 34, 'kitchen_id' => 31, 'orig' => 187.00, 'disc' => 99.00, 'qty' => 4, 'mins' => 310];
$dealsToInsert[] = ['item_id' => 23, 'kitchen_id' => 32, 'orig' => 105.00, 'disc' => 55.00, 'qty' => 6, 'mins' => 220];
$dealsToInsert[] = ['item_id' => 36, 'kitchen_id' => 32, 'orig' => 136.00, 'disc' => 68.00, 'qty' => 5, 'mins' => 260];
$dealsToInsert[] = ['item_id' => 41, 'kitchen_id' => 32, 'orig' => 308.00, 'disc' => 155.00, 'qty' => 3, 'mins' => 190];

// ── MUMBAI ──
// Kitchen 8 (Pooja) & Kitchen 9 (Arjun)
$dealsToInsert[] = ['item_id' => 10, 'kitchen_id' => 8, 'orig' => 173.00, 'disc' => 89.00, 'qty' => 5, 'mins' => 320];
$dealsToInsert[] = ['item_id' => 31, 'kitchen_id' => 8, 'orig' => 267.00, 'disc' => 135.00, 'qty' => 4, 'mins' => 250];
$dealsToInsert[] = ['item_id' => 40, 'kitchen_id' => 8, 'orig' => 250.00, 'disc' => 125.00, 'qty' => 6, 'mins' => 360];
$dealsToInsert[] = ['item_id' => 28, 'kitchen_id' => 9, 'orig' => 69.00,  'disc' => 35.00,  'qty' => 12,'mins' => 180];

// ── BANGALORE ──
// Kitchen 16 (Divya) & Kitchen 17 (Naveen)
$dealsToInsert[] = ['item_id' => 9,  'kitchen_id' => 16, 'orig' => 176.00, 'disc' => 95.00, 'qty' => 6, 'mins' => 290];
$dealsToInsert[] = ['item_id' => 33, 'kitchen_id' => 16, 'orig' => 55.00,  'disc' => 28.00, 'qty' => 8, 'mins' => 210];
$dealsToInsert[] = ['item_id' => 18, 'kitchen_id' => 17, 'orig' => 248.00, 'disc' => 125.00, 'qty' => 4, 'mins' => 340];

$stmt = $db->prepare("INSERT INTO zero_waste_items 
    (menu_item_id, kitchen_id, original_price, discounted_price, quantity_available, expiry_time, status, created_at)
    VALUES (:menu_item_id, :kitchen_id, :original_price, :discounted_price, :quantity_available, DATE_ADD(NOW(), INTERVAL :mins MINUTE), 'active', NOW())");

$count = 0;
foreach ($dealsToInsert as $d) {
    $stmt->execute([
        'menu_item_id'       => $d['item_id'],
        'kitchen_id'         => $d['kitchen_id'],
        'original_price'     => $d['orig'],
        'discounted_price'   => $d['disc'],
        'quantity_available' => $d['qty'],
        'mins'               => $d['mins'],
    ]);
    $count++;
}

echo "✅ Successfully seeded $count active Zero Waste deals across all cities!\n";
