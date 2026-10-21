<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT c.id as customer_id, c.full_name, c.phone, a.id as address_id, a.street_address, a.city, a.latitude, a.longitude 
                    FROM customers c 
                    JOIN addresses a ON a.user_id = c.id 
                    WHERE c.status = 'active' AND a.is_deleted = 0
                    ORDER BY c.id ASC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Customers with addresses count: " . count($results) . "\n";
print_r(array_slice($results, 0, 10));
