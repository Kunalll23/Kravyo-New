<?php
require_once 'config/app.php';
require_once 'core/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
