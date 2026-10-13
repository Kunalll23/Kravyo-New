<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$stmt = $db->query("UPDATE customers SET city = 'Pune' WHERE city IS NULL OR city = ''");
echo "Updated " . $stmt->rowCount() . " customers to Pune.\n";
