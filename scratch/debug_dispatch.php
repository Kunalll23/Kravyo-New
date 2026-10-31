<?php
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$conn = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

echo "=== Deliveries for Run 6 ===\n";
$stmt = $conn->query("SELECT id, status, run_id, meal_type FROM subscription_deliveries WHERE run_id = 6");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Delivery #{$r['id']}: {$r['status']} | Meal: {$r['meal_type']}\n";
}

echo "\n=== Deliveries for Run 7 ===\n";
$stmt = $conn->query("SELECT id, status, run_id, meal_type FROM subscription_deliveries WHERE run_id = 7");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Delivery #{$r['id']}: {$r['status']} | Meal: {$r['meal_type']}\n";
}

echo "\n=== Deliveries for Run 4 ===\n";
$stmt = $conn->query("SELECT id, status, run_id, meal_type FROM subscription_deliveries WHERE run_id = 4");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Delivery #{$r['id']}: {$r['status']} | Meal: {$r['meal_type']}\n";
}
