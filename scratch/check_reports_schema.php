<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
$cols = $db->query('DESCRIBE customer_reports')->fetchAll(PDO::FETCH_COLUMN);
echo "customer_reports columns: " . implode(', ', $cols) . "\n";
