<?php
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$conn = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

echo "=== All Orders created today (2026-10-30) ===\n";
$stmt = $conn->query("SELECT id, order_number, order_type, kitchen_id, order_status, dispatch_status, delivery_partner_id, created_at FROM orders WHERE DATE(created_at) = '2026-10-30' ORDER BY id DESC");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Order #{$r['id']}: {$r['order_number']} | type: {$r['order_type']} | status: {$r['order_status']} | dispatch: {$r['dispatch_status']} | dp: {$r['delivery_partner_id']} | time: {$r['created_at']}\n";
}

echo "\n=== All Delivery Offers created today ===\n";
$stmt = $conn->query("SELECT * FROM delivery_offers WHERE DATE(sent_at) = '2026-10-30' ORDER BY id DESC LIMIT 10");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Offer #{$r['id']}: order {$r['order_id']} | dp: {$r['delivery_partner_id']} | status: {$r['status']} | round: {$r['round']} | time: {$r['sent_at']}\n";
}

echo "\n=== Delivery Runs today ===\n";
$stmt = $conn->query("SELECT id, run_code, meal_type, status, delivery_partner_id FROM delivery_runs WHERE delivery_date = '2026-10-30'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Run #{$r['id']}: {$r['run_code']} | status: {$r['status']} | dp: {$r['delivery_partner_id']}\n";
}

