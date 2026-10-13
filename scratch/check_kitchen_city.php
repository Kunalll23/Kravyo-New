<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT DISTINCT city FROM kitchens WHERE city IS NOT NULL AND city != ''");
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Available kitchen cities: " . implode(', ', $cities) . "\n";
