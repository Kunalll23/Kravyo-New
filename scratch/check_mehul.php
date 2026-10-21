<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();

echo "--- ADDRESSES SCHEMA ---\n";
$stmt = $db->query("DESCRIBE addresses");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- RECENT ADDRESSES ---\n";
$stmt = $db->query("SELECT * FROM addresses LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- ACTIVE CUSTOMERS ---\n";
$stmt = $db->query("SELECT id, full_name, phone, email, status FROM customers LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
