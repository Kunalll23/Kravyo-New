<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$sql = "SELECT dp.id
        FROM delivery_partners dp
        JOIN delivery_users u ON dp.user_id = u.id
        WHERE dp.city = :city
          AND dp.is_online = 1
          AND dp.is_busy = 0
          AND dp.verification_status = 'approved'
          AND u.status = 'active'
          AND dp.last_seen_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)";
$stmt = $db->prepare($sql);
$stmt->execute(['city' => 'Surat']);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Eligible count: " . count($results) . "\n";
print_r($results);
