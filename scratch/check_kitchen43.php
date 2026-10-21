<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT id, kitchen_name, address, city, latitude, longitude FROM kitchens WHERE id = 43");
print_r($stmt->fetch(PDO::FETCH_ASSOC));

echo "--- CUSTOMERS WITH BARDOLI ADDRESSES ---\n";
$stmt = $db->query("SELECT c.id as customer_id, c.full_name, c.phone, a.id as address_id, a.street_address, a.landmark, a.city, a.pincode, a.latitude, a.longitude 
                    FROM customers c 
                    JOIN addresses a ON a.user_id = c.id 
                    WHERE a.city LIKE '%Bardoli%' AND a.is_deleted = 0");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
