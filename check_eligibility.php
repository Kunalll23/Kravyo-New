<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();
$stmt = $db->query('SELECT dp.id, dp.is_online, dp.is_busy, dp.verification_status, u.status, dp.last_seen_at FROM delivery_partners dp JOIN delivery_users u ON dp.user_id = u.id WHERE dp.city="Surat" LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
