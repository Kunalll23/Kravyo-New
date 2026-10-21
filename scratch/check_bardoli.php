<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();

echo "--- KITCHEN 43 DETAILS ---\n";
$stmt = $db->query("SELECT * FROM kitchens WHERE id = 43");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- BARDOLI ADDRESSES IN DB ---\n";
$stmt = $db->query("SELECT * FROM addresses WHERE city LIKE '%Bardoli%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- BARDOLI DELIVERY PARTNERS ---\n";
$stmt = $db->query("SELECT dp.*, u.full_name, u.phone FROM delivery_partners dp JOIN delivery_users u ON dp.user_id = u.id WHERE dp.city LIKE '%Bardoli%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
