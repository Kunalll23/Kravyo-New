<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();
$stmt = $db->query('SELECT id, kitchen_name, latitude, longitude FROM kitchens');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
