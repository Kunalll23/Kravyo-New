<?php
require_once __DIR__ . '/../config/app.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();
$stmt = $db->query('DESCRIBE kitchens');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
