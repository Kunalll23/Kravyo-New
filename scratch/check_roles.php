<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
echo "--- CHEFS ---\n";
$stmt = $db->query('SELECT id, full_name, email, phone, status FROM chefs LIMIT 3');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- DELIVERY ---\n";
$stmt = $db->query('SELECT id, full_name, phone, status FROM delivery_partners LIMIT 3');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- ADMINS ---\n";
$stmt = $db->query('SELECT id, username, email FROM admins LIMIT 3');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
