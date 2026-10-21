<?php
define('ROOT_PATH', __DIR__ . '/..');
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/constants.php';
require_once ROOT_PATH . '/core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT DISTINCT c.id, c.full_name, c.phone, (SELECT a.id FROM addresses a WHERE a.user_id = c.id AND a.is_deleted = 0 LIMIT 1) as address_id, (SELECT a.street_address FROM addresses a WHERE a.user_id = c.id AND a.is_deleted = 0 LIMIT 1) as address, (SELECT a.city FROM addresses a WHERE a.user_id = c.id AND a.is_deleted = 0 LIMIT 1) as city
                    FROM customers c
                    WHERE (SELECT a.id FROM addresses a WHERE a.user_id = c.id AND a.is_deleted = 0 LIMIT 1) IS NOT NULL
                    ORDER BY c.id ASC
                    LIMIT 15");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
