<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

echo "--- delivery_partners ---\n";
$stmt = $db->query('DESCRIBE delivery_partners');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- orders ---\n";
$stmt = $db->query('DESCRIBE orders');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
